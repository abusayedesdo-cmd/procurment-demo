-- Step 8: RFP/RFI/Hiring Vendor/Consultant (phpMyAdmin, no Artisan)
-- Equivalent of migration 2026_09_26_000004_add_rfp_to_rfqs_type_enum.php

ALTER TABLE `rfqs` MODIFY `type` ENUM('RFQ','OTM','RFP') NOT NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_26_000004_add_rfp_to_rfqs_type_enum', MAX(`batch`) FROM `migrations`;
