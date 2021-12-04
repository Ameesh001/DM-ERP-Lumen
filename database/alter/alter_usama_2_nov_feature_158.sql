INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_workflow`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Monthly Voucher Posting', 'admission_setup/monthly_voucher_posting', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"GET\":\"View\"}', '1', '1', '1', '0', '1', '2021-09-24 14:27:13', NULL, '1', NULL, NULL);


ALTER TABLE `fee_slip_master` ADD COLUMN `transaction_remarks` VARCHAR(100) NULL AFTER `payment_status`, ADD COLUMN `pay_date` DATE NULL AFTER `transaction_no`;

ALTER TABLE `fee_slip_master` ADD COLUMN `transaction_amount` INT(11) NULL AFTER `transaction_remarks`; 