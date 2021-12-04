UPDATE `auth_modules` SET `default_url` = 'campus_setup' WHERE `id` = '59'; 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Section Assign', 'campus_setup/section_assign', NULL, 'mdi mdi-folder-multiple-outline', '59', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 

ALTER TABLE `auth_roles` ADD COLUMN `organization_id` INT(11) DEFAULT 1 NOT NULL AFTER `id`, ADD INDEX `roles_organization_id_idx` (`organization_id`), ADD CONSTRAINT `roles_organization_id_fk` FOREIGN KEY (`organization_id`) REFERENCES `organization_list`(`id`) ON UPDATE RESTRICT ON DELETE RESTRICT;

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Subject Assign', 'campus_setup/subject_assign', NULL, 'mdi mdi-folder-multiple-outline', '59', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL);

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Fee Structure', 'campus_setup/fee_assign', NULL, 'mdi mdi-folder-multiple-outline', '59', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 

