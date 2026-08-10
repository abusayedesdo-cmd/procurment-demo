-- ESDO Procurement Management System
-- Adds: Projects (super admin defines project names; every user can
-- optionally sit under a project alongside their role).
--
-- Run this once in phpMyAdmin > SQL tab, on the live database.
-- Safe to re-run individual statements if one already applied — but as
-- written, running the whole block twice will error on the second run
-- (table/column already exists), which is fine — just skip re-running.

CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_name_unique` (`name`),
  UNIQUE KEY `projects_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
  ADD COLUMN `project_id` bigint unsigned DEFAULT NULL AFTER `role_id`,
  ADD CONSTRAINT `users_project_id_foreign`
    FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
    ON DELETE SET NULL;

-- Optional starter row so the dropdown isn't empty on first load.
-- Feel free to edit/delete this via the Data Manager afterwards.
INSERT INTO `projects` (`name`, `code`, `is_active`, `created_at`, `updated_at`)
VALUES ('General / Unassigned', 'GEN', 1, NOW(), NOW());
