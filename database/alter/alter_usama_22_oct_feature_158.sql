SET FOREIGN_KEY_CHECKS = 0; 
  
UPDATE `session_month` SET `is_enable` = '0'; 

UPDATE `session_month` SET `is_enable` = '1' WHERE `id` = '39'; 
  
UPDATE `fee_structure_detail` SET `fees_from_date` = '2021-11-01', `fees_end_date` = '2021-11-30'  WHERE `id` = '8'; 
UPDATE `fee_structure_detail` SET `fees_end_date` = '2021-11-30',  `fees_from_date` = '2021-11-01' WHERE `id` = '15';

ALTER TABLE `slip_setup` ADD COLUMN `session_month_id` INT(11) NOT NULL AFTER `slip_type_id`, ADD CONSTRAINT `slip_setupe_session_month_id_fk` FOREIGN KEY (`session_month_id`) REFERENCES `session_month`(`id`) ON UPDATE RESTRICT ON DELETE RESTRICT;
 
INSERT INTO `slip_setup` (`id`, `slip_type_id`, `session_month_id`, `month_close_date`, `issue_date`, `due_date`, `validity_date`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '39', '2021-11-22', '2021-11-01', '2021-11-22', '2021-11-30', NULL, NULL, NULL, NULL, NULL, '1');

UPDATE `slip_setup` SET `session_month_id` = '21' WHERE `id` = '1'; 
UPDATE `slip_setup` SET `session_month_id` = '22' WHERE `id` = '2'; 

ALTER TABLE `slip_setup` ADD COLUMN `month_index` INT(11) NOT NULL AFTER `session_month_id`; 

UPDATE `slip_setup` SET `month_index` = '202103' WHERE `id` = '1'; 
UPDATE `slip_setup` SET `month_index` = '202104' WHERE `id` = '2'; 
UPDATE `slip_setup` SET `month_index` = '202111' WHERE `id` = '3'; 

SET FOREIGN_KEY_CHECKS = 1;




INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Generate Customize Voucher', 'admission_setup/gen_customize_voucher', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"GET\":\"View\"}', '1', '1', '1', '1', '2021-09-24 14:27:13', NULL, '1', NULL, NULL);



ALTER TABLE `fee_slip_master` 
  ADD COLUMN `is_challan_customize` INT (11) DEFAULT 0 NULL COMMENT '0: normal challan, 1:challan customize refrence num on customize by cloumn' AFTER `transaction_no`,
  ADD COLUMN `customize_by_challan_no` VARCHAR (25) NULL COMMENT 'customize by challan num' AFTER `is_challan_customize` ;
