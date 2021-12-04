INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Monthly Bulk Voucher Posting', 'admission_setup/monthly_bulk_voucher_posting', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"GET\":\"View\"}', '1', '1', '1', '0', '1', '2021-09-24 14:27:13', NULL, '1', NULL, NULL);


INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Fees Management', 'fees_management', NULL, 'mdi mdi-folder-multiple-outline', '0', '1', NULL, '1', '1', '0', '0', '1', '2021-09-06 12:00:54', NULL, NULL, NULL, NULL); 

UPDATE `auth_modules` SET `default_url` = 'fees_management/monthly_voucher_posting' WHERE `id` = '86'; 
UPDATE `auth_modules` SET `default_url` = 'fees_management/monthly_bulk_voucher_posting' WHERE `id` = '91'; 
UPDATE `auth_modules` SET `default_url` = 'fees_management/gen_customize_voucher' WHERE `id` = '81'; 


UPDATE `auth_modules` SET `parent_id` = '92' WHERE `id` = '81'; 
UPDATE `auth_modules` SET `parent_id` = '92' WHERE `id` = '86'; 
UPDATE `auth_modules` SET `parent_id` = '92' WHERE `id` = '91'; 


UPDATE `auth_modules` SET `parent_id` = '92' WHERE `id` = '76'; 
UPDATE `auth_modules` SET `default_url` = 'fees_management/gen_monthly_voucher' WHERE `id` = '76'; 

ALTER TABLE `monthly_fee_upload_temp` ADD COLUMN `error_log_file` VARCHAR(100) NULL AFTER `file_submited_by`, ADD COLUMN `std_verified_count` INT(11) NULL AFTER `error_log_file`;