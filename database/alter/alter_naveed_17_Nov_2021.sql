INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Register Exam Marks Student', 'exam_management/exam_register_marks_student', NULL, 'mdi mdi-folder-multiple-outline', '88', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '0', '1', NULL, '2021-09-03 12:56:02', NULL, '1', NULL); 

ALTER TABLE `exam_marks_register` ADD COLUMN `subject_id` INT(11) NOT NULL AFTER `assign_exam_subject_id`, ADD CONSTRAINT `exam_marks_register_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subject`(`id`); 

ALTER TABLE `exam_marks_register` CHANGE `obtain_marks` `obtain_marks` INT(11) NULL; 

UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-server-security' WHERE `default_url` = 'permission'; 
UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-package-variant' WHERE `default_url` = 'policy'; 
UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-bank' WHERE `default_url` = 'campus_setup'; 
UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-library-books' WHERE `default_url` = 'admission_setup'; 
UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-account-location' WHERE `default_url` = 'student_management'; 
UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-cash-usd' WHERE `default_url` = 'fees_management'; 
UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-settings' WHERE `default_url` = 'setup'; 
UPDATE `auth_modules` SET `icon_class` = 'mdi mdi-school' WHERE `default_url` = 'exam_management'; 

