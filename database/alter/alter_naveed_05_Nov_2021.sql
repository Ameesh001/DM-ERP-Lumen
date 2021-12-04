ALTER TABLE `examination` ADD COLUMN `countries_id` INT NULL AFTER `organization_id`, ADD COLUMN `state_id` INT NULL AFTER `countries_id`, ADD COLUMN `region_id` INT NULL AFTER `state_id`, ADD COLUMN `city_id` INT NULL AFTER `region_id`, ADD COLUMN `campus_id` INT NULL AFTER `city_id`, ADD CONSTRAINT `examination_countries_id_foreign_key` FOREIGN KEY (`countries_id`) REFERENCES `countries`(`id`), ADD CONSTRAINT `examination_state_id_foreign_key` FOREIGN KEY (`state_id`) REFERENCES `state`(`id`), ADD CONSTRAINT `examination_region_id_foreign_key` FOREIGN KEY (`region_id`) REFERENCES `region`(`id`), ADD CONSTRAINT `examination_city_id_foreign_key` FOREIGN KEY (`city_id`) REFERENCES `city`(`id`), ADD CONSTRAINT `examination_campus_id_foreign_key` FOREIGN KEY (`campus_id`) REFERENCES `campus`(`id`); 
ALTER TABLE `assign_exam_campus` DROP COLUMN `assign_exam_hierarchy_id`, DROP INDEX `assign_exam_campus_assign_exam_hierarchy_id_foreign`, DROP FOREIGN KEY `assign_exam_campus_assign_exam_hierarchy_id_foreign`; 


ALTER TABLE `examination` CHANGE `countries_id` `countries_id` INT(11) NOT NULL, CHANGE `state_id` `state_id` INT(11) DEFAULT NULL NULL, CHANGE `region_id` `region_id` INT(11) DEFAULT NULL NULL, CHANGE `city_id` `city_id` INT(11) DEFAULT NULL NULL, CHANGE `campus_id` `campus_id` INT(11) DEFAULT NULL NULL; 

DROP TABLE assign_exam_hierarchy;

ALTER TABLE `examination` ADD COLUMN `hierarchy_level` INT NOT NULL AFTER `id`; 

ALTER TABLE `assign_exam_campus` CHANGE `campus_id` `campus_id` INT(11) NOT NULL, ADD COLUMN `is_exam_started` TINYINT DEFAULT 0 NULL AFTER `campus_id`; 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Exam Subject', 'exam_management/exam_subject', NULL, 'mdi mdi-folder-multiple-outline', '88', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '0', '1', NULL, '2021-09-03 12:56:02', NULL, '1', NULL); 
INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Register Exam Marks', 'exam_management/exam_register_marks', NULL, 'mdi mdi-folder-multiple-outline', '88', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '0', '1', NULL, '2021-09-03 12:56:02', NULL, '1', NULL); 

ALTER TABLE `exam_setup` ADD COLUMN `class_id` INT NOT NULL AFTER `campus_id`, ADD CONSTRAINT `exam_sertup_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `class`(`id`); 
ALTER TABLE `exam_setup` ADD COLUMN `session_id` INT NOT NULL AFTER `class_id`, ADD CONSTRAINT `exam_sertup_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `session`(`id`); 

ALTER TABLE `student_admission` CHANGE `admission_code` `admission_code` VARCHAR(40) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL; 

ALTER TABLE `exam_marks_register` CHANGE `grading_exam_id` `grading_exam_id` INT(11) NULL; 

ALTER TABLE `exam_marks_register` CHANGE `percentage` `percentage` DOUBLE NULL; 

ALTER TABLE `assign_exam_subject` ADD COLUMN `grading_type_id` INT NULL AFTER `passing_marks`, ADD CONSTRAINT `assign_exam_subject_gradding_type_id_foreign` FOREIGN KEY (`grading_type_id`) REFERENCES `grading_type`(`id`); 

ALTER TABLE `exam_marks_register` CHANGE `admission_code` `admission_code` VARCHAR(10) NOT NULL; 
