-- RFQ: Terms & Conditions + Distribution Process (phpMyAdmin, no Artisan)
-- Equivalent of migration 2026_09_24_000002_add_terms_distribution_to_rfqs_table.
--
-- FIRST check whether the columns already exist on the live DB:
--   SHOW COLUMNS FROM `rfqs`;
-- Skip whichever ADD line below already exists (running it twice errors out).

ALTER TABLE `rfqs`
  ADD COLUMN `terms_conditions` TEXT NULL AFTER `closing_date`,
  ADD COLUMN `distribution_process` VARCHAR(255) NULL AFTER `terms_conditions`;

-- So a later `php artisan migrate` doesn't try to run it again:
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_24_000002_add_terms_distribution_to_rfqs_table', MAX(`batch`) FROM `migrations`;
