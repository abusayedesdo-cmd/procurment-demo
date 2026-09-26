-- Tender Advertisement: medium -> BD Jobs / National Newspaper / Local Newspaper
-- (phpMyAdmin, no Artisan). Equivalent of migration
-- 2026_09_26_000001_update_tender_advertisements_medium_enum.php
--
-- Widen the column first so old values are never truncated mid-update:
ALTER TABLE `tender_advertisements` MODIFY `medium` VARCHAR(50) NOT NULL;

-- Map existing rows (re-check any 'Newspaper' rows by hand — this maps
-- them all to National Newspaper; move individual rows to Local Newspaper
-- if you know they were actually placed locally):
UPDATE `tender_advertisements` SET `medium` = 'BD Jobs' WHERE `medium` = 'bdjobs';
UPDATE `tender_advertisements` SET `medium` = 'National Newspaper' WHERE `medium` = 'Newspaper';

-- Lock the column down to the new enum:
ALTER TABLE `tender_advertisements`
  MODIFY `medium` ENUM('BD Jobs','National Newspaper','Local Newspaper') NOT NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_26_000001_update_tender_advertisements_medium_enum', MAX(`batch`) FROM `migrations`;
