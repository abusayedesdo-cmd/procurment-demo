-- Meetings now remember their committee (Main / project Sub-Committee), and the
-- Main committee's members move into committee_members (phpMyAdmin, no Artisan).
-- Equivalent of migrations:
--   2026_10_12_000001_add_committee_id_to_meetings_table
--   2026_10_12_000002_copy_central_roster_into_main_committee_members
-- Run the blocks in order, once each.

-- ===== 1) meetings.committee_id =====
ALTER TABLE `meetings`
  ADD COLUMN `committee_id` BIGINT UNSIGNED NULL AFTER `procurement_case_id`,
  ADD CONSTRAINT `meetings_committee_id_foreign` FOREIGN KEY (`committee_id`)
    REFERENCES `purchase_committees` (`id`) ON DELETE SET NULL;

-- Old meetings: committee that holds the case now (latest transfer of its plan) ...
UPDATE `meetings` m
JOIN `procurement_cases` c ON c.id = m.procurement_case_id
JOIN `procurement_plans` p ON p.pr_id = c.purchase_requisition_id
SET m.committee_id = (
  SELECT t.to_committee_id FROM `sub_committee_transfers` t
  WHERE t.procurement_plan_id = p.id
  ORDER BY t.transfer_date DESC, t.id DESC LIMIT 1
)
WHERE m.committee_id IS NULL;

-- ... otherwise the Main committee.
UPDATE `meetings`
SET committee_id = (SELECT id FROM `purchase_committees` WHERE type = 'main' ORDER BY id LIMIT 1)
WHERE committee_id IS NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_12_000001_add_committee_id_to_meetings_table', MAX(`batch`) FROM `migrations`;

-- ===== 2) Main committee members (only if it has none yet) =====
-- user_id stays NULL on purpose (notifications only - no change to who can see/act on PRs).
INSERT INTO `committee_members`
  (`committee_id`, `user_id`, `procurement_committee_member_id`, `designation_in_committee`, `created_at`, `updated_at`)
SELECT
  (SELECT id FROM `purchase_committees` WHERE type = 'main' ORDER BY id LIMIT 1),
  NULL,
  r.id,
  CASE r.role WHEN 'convener' THEN 'Convener' WHEN 'member_secretary' THEN 'Member Secretary' ELSE 'Member' END,
  NOW(), NOW()
FROM `procurement_committee_members` r
WHERE r.active = 1
  AND r.committee_type = 'central_procurement'
  AND (SELECT id FROM `purchase_committees` WHERE type = 'main' ORDER BY id LIMIT 1) IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `committee_members` cm
    WHERE cm.committee_id = (SELECT id FROM `purchase_committees` WHERE type = 'main' ORDER BY id LIMIT 1)
  )
ORDER BY r.sort_order, r.id;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_12_000002_copy_central_roster_into_main_committee_members', MAX(`batch`) FROM `migrations`;
