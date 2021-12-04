-- <Naveed>
INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Admission Voucher Posting', 'admission_setup/admission_voucher_posting', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 

ALTER TABLE `student_admission` CHANGE `joinning_date` `joinning_date` DATE NULL; 
ALTER TABLE `student_admission` CHANGE `section_id` `section_id` INT(11) NULL; 
ALTER TABLE `student_admission` CHANGE `home_cell_no` `home_cell_no` VARCHAR(14) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL; 
ALTER TABLE `reg_rept_note` CHANGE `note_type` `note_type` INT(11) NULL COMMENT '1= Std Regration, 2 = admision voucher, 3=reg card view';
INSERT INTO `reg_rept_note` (`id`, `note_type`, `type`, `organization_id`, `state_id`, `region_id`, `city_id`, `sort_no`, `is_enable`, `note`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES (NULL, '3', '2', '1', '1', '1', '1', '1', '1', 'This admission confirmation card will be mandatory for school joining and valid till issuance of student ID card. In case student not appear within 3 days of given date.\r\nDarul Madinah will Consider other student for admission on.\r\n', NULL, NULL, NULL, NULL); 

insert  into `options`(`id`,`organization_id`,`option_type`,`option_title`,`option_name`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) values (1,1,1,'Progress','Good',NULL,NULL,NULL,NULL,NULL,1),(2,1,1,'Progress','Very Good',NULL,NULL,NULL,NULL,NULL,1),(3,1,1,'Progress','Excellent',NULL,NULL,NULL,NULL,NULL,1),(4,1,2,'Reason for Transfer','Due to Home shifting',NULL,NULL,NULL,NULL,NULL,1),(5,1,2,'Reason for Transfer','Due to School Distance',NULL,NULL,NULL,NULL,NULL,1),(6,1,2,'Reason for Transfer','Due Father Transfer',NULL,NULL,NULL,NULL,NULL,1);


INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Student Transfer Request', 'admission_setup/student_transfer_request', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 

-- </Naveed>


