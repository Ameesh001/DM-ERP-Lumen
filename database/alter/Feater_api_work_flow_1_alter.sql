
-- </Naveed>

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Student Left Request', 'admission_setup/student_left_request', NULL, 'mdi mdi-folder-multiple-outline', '73', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Work Flow Setup', 'policy/work_flow_setup', NULL, 'mdi mdi-folder-multiple-outline', '57', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 

-- </Naveed>

-- <Naveed> 25-Oct-2021

ALTER TABLE `auth_modules` ADD COLUMN `is_workflow` TINYINT DEFAULT 0 NULL AFTER `detail`; 

ALTER TABLE `wf_master` CHANGE `wf_start_on` `wf_start_on` TINYINT NULL COMMENT '1= insert, 1=update, 3=delete'; 
ALTER TABLE `wf_master` DROP COLUMN `wf_hierarchy_level`; 
ALTER TABLE `wf_detail` CHANGE `amount` `amount` INT(11) DEFAULT 0 NULL; 
ALTER TABLE `wf_detail` ADD COLUMN `hours` INT NULL AFTER `amount`, ADD COLUMN `escalation` VARCHAR(100) NULL AFTER `hours`; 

insert  into `drop_down_list`(`id`,`type`,`type_name`,`drop_down_list_name`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) values (1,1,'Hours','24',NULL,NULL,NULL,NULL,NULL,1),(2,1,'Hours','48',NULL,NULL,NULL,NULL,NULL,1),(3,1,'Hours','72',NULL,NULL,NULL,NULL,NULL,1),(4,1,'Hours','96',NULL,NULL,NULL,NULL,NULL,1),(5,2,'Workflow Setup','Auto Next Level',NULL,NULL,NULL,NULL,NULL,1),(6,2,'Workflow Setup','Notify Reporting Manager',NULL,NULL,NULL,NULL,NULL,1),(7,2,'Workflow Setup','Both',NULL,NULL,NULL,NULL,NULL,1);

ALTER TABLE `wf_detail` CHANGE `hours` `breach_sla_time` INT(11) NULL, CHANGE `escalation` `escalation_type` VARCHAR(100) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, ADD COLUMN `before_sla_time` INT NULL AFTER `escalation_type`, ADD COLUMN `notification_type` INT NULL AFTER `before_sla_time`; 
ALTER TABLE `wf_detail` CHANGE `notification_type` `notification_type` VARCHAR(100) NULL; 

INSERT INTO `drop_down_list` (`id`, `type`, `type_name`, `drop_down_list_name`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '3', 'Notification Type', 'Email', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `drop_down_list` (`id`, `type`, `type_name`, `drop_down_list_name`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '3', 'Notification Type', 'Push Notification', NULL, NULL, NULL, NULL, NULL, '1'); 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Student Management', 'student_management', NULL, 'mdi mdi-folder-multiple-outline', '0', '1', NULL, '1', '1', '0', '0', '1', '2021-09-06 12:00:54', NULL, NULL, NULL, NULL); 
INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Student Profile', 'student_management/student_profile', NULL, 'mdi mdi-folder-multiple-outline', '83', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '0', '1', NULL, '2021-09-03 12:56:02', NULL, '1', NULL); 




