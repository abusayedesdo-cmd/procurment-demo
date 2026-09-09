-- ESDO Procurement Management System
-- Run this in phpMyAdmin (no artisan/SSH on cPanel shared hosting).
-- Adds Project scoping to purchase_committees — sub-committees are formed
-- for a specific project (Policy §9); main/central committees stay
-- project-less (NULL).

ALTER TABLE `purchase_committees`
  ADD COLUMN `project_id` BIGINT UNSIGNED NULL AFTER `type`,
  ADD CONSTRAINT `purchase_committees_project_id_foreign`
    FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

-- Mark the migration as run (check current max batch first:
--    SELECT MAX(batch) FROM migrations;
-- then replace {NEXT_BATCH} with that number + 1):
INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_08_31_000010_add_project_id_to_purchase_committees_table', {NEXT_BATCH});
