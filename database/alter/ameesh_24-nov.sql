INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES 
(NULL, 'Voucher Setup', 'fees_management/slip_setup', NULL, 'mdi mdi-folder-multiple-outline', '107', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '0', '1', NULL, NULL, NULL, NULL, NULL); 

ALTER TABLE `slip_setup` DROP FOREIGN KEY `slip_setupe_session_month_id_fk`; 


ALTER TABLE `slip_setup` ADD CONSTRAINT `slip_setupe_session_month_id_fk` FOREIGN KEY (`session_month_id`) REFERENCES `session_month`(`id`) ON UPDATE RESTRICT ON DELETE RESTRICT; 

ALTER TABLE `session_month` CHANGE `is_enable` `is_enable` INT(11) DEFAULT 0 NULL; 




ALTER TABLE `std_registration_interview_test` CHANGE `obtained_marks` `obtained_marks` 
VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL; 
