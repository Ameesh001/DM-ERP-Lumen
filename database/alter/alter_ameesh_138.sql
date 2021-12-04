INSERT INTO `auth_modules` 
(`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES 
(NULL, 'Fee Structure', 'policy/fee_structure', NULL, 'mdi mdi-folder-multiple-outline', '57', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '4', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 


INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES 
(NULL, 'Assign Fee Structure', 'policy/assign_fee_structure', NULL, 'mdi mdi-folder-multiple-outline', '57', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '5', '1', '1', '0', '1', NULL, NULL, NULL, NULL, NULL); 


INSERT INTO `auth_modules` 
(`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
(NULL, 'Discount Policy', 'policy/discount_policy', NULL, 'mdi mdi-folder-multiple-outline', '57', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '3', '1', '1', '0', '1', NULL, NULL, NULL, NULL, NULL); 

ALTER TABLE `discount_policy` CHANGE `disc_amount` `disc_amount` INT(11) NULL;

ALTER TABLE `discount_type` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `deleted_at`; 


ALTER TABLE `discount_policy` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`; 



INSERT INTO `auth_modules` 
(`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
(NULL, 'Assign Discount Policy', 'policy/assign_discount_policy', NULL, 'mdi mdi-folder-multiple-outline', '57', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '4', '1', '1', '0', '1', NULL, NULL, NULL, NULL, NULL); 



INSERT INTO `user_hierarchy_levels` (`id`, `level_name`, `created_at`, `updated_at`) VALUES (NULL, 'Student Level', NULL, NULL); 

INSERT INTO `user_hierarchy_levels` (`id`, `level_name`, `created_at`, `updated_at`) VALUES (NULL, 'Class Level', NULL, NULL); 


ALTER TABLE `assign_discount_policy` ADD COLUMN `student_id` INT(11) NULL AFTER `class_id`; 

 ALTER TABLE `assign_discount_policy` ADD CONSTRAINT `assign_discount_policy_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_admission`(`id`); 


ALTER TABLE `assign_fee_structure` ADD COLUMN `student_id` INT(11) NULL AFTER `class_id`;
ALTER TABLE `fee_structure_detail` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`;

ALTER TABLE `discount_policy` CHANGE `condition` `condition` VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL; 

INSERT INTO `user_hierarchy_levels` (`id`, `level_name`, `created_at`, `updated_at`) VALUES (7, 'Class Level', NULL, NULL); 
INSERT INTO `user_hierarchy_levels` (`id`, `level_name`, `created_at`, `updated_at`) VALUES (8, 'Student Level', NULL, NULL); 

