-- ESDO Procurement Management System
-- Run this in phpMyAdmin (no artisan/SSH on cPanel shared hosting).
-- Adds Earnest Money tracking (per bidder, on quotations) and
-- Security Deposit tracking (winning bidder only, on contract_awards),
-- per ESDO Procurement Policy §23-24 (revised 1 July 2025).

-- 1) Earnest Money — quotations (per bidder)
ALTER TABLE `quotations`
  ADD COLUMN `earnest_money_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `opening_remarks`,
  ADD COLUMN `earnest_money_amount` DECIMAL(12,2) NULL AFTER `earnest_money_required`,
  ADD COLUMN `earnest_money_status` ENUM('not_required','held','refunded','forfeited') NOT NULL DEFAULT 'not_required' AFTER `earnest_money_amount`,
  ADD COLUMN `earnest_money_notes` TEXT NULL AFTER `earnest_money_status`;

-- 2) Security Deposit — contract_awards (winning bidder only)
ALTER TABLE `contract_awards`
  ADD COLUMN `security_deposit_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `file_path`,
  ADD COLUMN `security_deposit_amount` DECIMAL(12,2) NULL AFTER `security_deposit_required`,
  ADD COLUMN `security_deposit_percentage` DECIMAL(5,2) NULL AFTER `security_deposit_amount`,
  ADD COLUMN `security_deposit_status` ENUM('held','returned','waived') NOT NULL DEFAULT 'held' AFTER `security_deposit_percentage`,
  ADD COLUMN `warranty_period_ends_at` DATE NULL AFTER `security_deposit_status`,
  ADD COLUMN `security_deposit_returned_at` DATE NULL AFTER `warranty_period_ends_at`;

-- 3) Mark both migrations as run, so `php artisan migrate` (if ever run
--    later, e.g. from a local copy) doesn't try to re-apply them and fail
--    on "column already exists". Check the current max batch number first:
--       SELECT MAX(batch) FROM migrations;
--    then replace {NEXT_BATCH} below with that number + 1.
INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_08_31_000001_add_earnest_money_to_quotations_table', {NEXT_BATCH}),
  ('2026_08_31_000002_add_security_deposit_to_contract_awards_table', {NEXT_BATCH});
