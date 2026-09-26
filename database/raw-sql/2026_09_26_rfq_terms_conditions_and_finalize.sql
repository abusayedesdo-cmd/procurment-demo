-- Step 7: RFQ Terms & Conditions (editable master list) + Finalized RFQ
-- (phpMyAdmin, no Artisan). Equivalent of migrations
-- 2026_09_26_000002_create_rfq_terms_conditions_tables.php and
-- 2026_09_26_000003_add_finalized_status_to_rfqs_table.php

CREATE TABLE `rfq_terms_conditions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `text` TEXT NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rfq_terms_condition_rfq` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rfq_id` BIGINT UNSIGNED NOT NULL,
  `rfq_terms_condition_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfq_terms_condition_unique` (`rfq_id`, `rfq_terms_condition_id`),
  CONSTRAINT `rtcr_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rtcr_rfq_terms_condition_id_foreign` FOREIGN KEY (`rfq_terms_condition_id`) REFERENCES `rfq_terms_conditions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The 9 static lines from the old hardcoded list (the opening-date and
-- delivery-location lines stay auto-generated in the document, not here):
INSERT INTO `rfq_terms_conditions` (`text`, `sort_order`, `active`, `created_at`, `updated_at`) VALUES
('Legal Document PDF Copy must be Submitted with Quotation: (Trade License, VAT Registration, TIN Certificate, PSR)', 0, 1, NOW(), NOW()),
('Relevant Experience Certificate PDF Copy must be Submitted with Quotation.', 1, 1, NOW(), NOW()),
('General Experience Certificate PDF Copy must be Submitted with Quotation.', 2, 1, NOW(), NOW()),
('RFQ Receiving PDF Copy Need to Attach with the Quotation.', 3, 1, NOW(), NOW()),
('As per govt. rules and regulation vat & tax will be deducted at the time of payment.', 4, 1, NOW(), NOW()),
('The given price of the product must be valid for at least 15 days, and within this time frame the supplier is bound to supply products at the given price.', 5, 1, NOW(), NOW()),
('Mode of payment: Payment will be made through Account Payee cheque/Pay order/RTGS/BEFTN or DD in favour of the supplying vendor after successful delivery of goods.', 6, 1, NOW(), NOW()),
('ESDO reserves the authority to cancel — partially or fully — any quotation with or without explanation.', 7, 1, NOW(), NOW()),
('ESDO never allows any harassment to women and children, and never allows child labour. Any institution or organization associated with such practices is strongly discouraged from participating in the bid.', 8, 1, NOW(), NOW());

-- "Finalized RFQ"
ALTER TABLE `rfqs`
  ADD COLUMN `status` ENUM('draft','finalized') NOT NULL DEFAULT 'draft' AFTER `distribution_process`,
  ADD COLUMN `finalized_at` TIMESTAMP NULL AFTER `status`,
  ADD COLUMN `finalized_by` BIGINT UNSIGNED NULL AFTER `finalized_at`,
  ADD CONSTRAINT `rfqs_finalized_by_foreign` FOREIGN KEY (`finalized_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_26_000002_create_rfq_terms_conditions_tables', MAX(`batch`) FROM `migrations`;
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_26_000003_add_finalized_status_to_rfqs_table', MAX(`batch`) FROM `migrations`;
