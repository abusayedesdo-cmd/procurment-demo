-- Step 11: Forward for Evaluation / Reject Quotations
-- Run in phpMyAdmin (no Artisan on the server). Equivalent of migration
-- 2026_09_24_000001_add_opening_review_to_quotations_table.

ALTER TABLE `quotations`
  MODIFY `status` ENUM('received','opened','evaluated','disqualified','forwarded','rejected') NOT NULL DEFAULT 'received';

ALTER TABLE `quotations`
  ADD COLUMN `rejection_reason` TEXT NULL AFTER `opening_remarks`,
  ADD COLUMN `reviewed_by` BIGINT UNSIGNED NULL AFTER `rejection_reason`,
  ADD COLUMN `reviewed_at` TIMESTAMP NULL AFTER `reviewed_by`,
  ADD CONSTRAINT `quotations_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- So a later `php artisan migrate` doesn't try to run it again:
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_24_000001_add_opening_review_to_quotations_table', MAX(`batch`) FROM `migrations`;
