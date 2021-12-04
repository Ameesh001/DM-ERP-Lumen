

-- </Naveed>
ALTER TABLE `student_transfer_request` ADD COLUMN `to_session_id` INT(11) NOT NULL AFTER `to_campus_id`;
ALTER TABLE `student_transfer_request` ADD COLUMN `transfer_out_date` DATE NULL AFTER `to_class_id`, ADD COLUMN `expected_date_joining` DATE NULL AFTER `transfer_out_date`; 

-- </Naveed>

INSERT INTO `options` (`id`, `organization_id`, `option_type`, `option_title`, `option_name`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '3', 'Religion', 'Islam', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `options` (`id`, `organization_id`, `option_type`, `option_title`, `option_name`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '3', 'Religion', 'Christianity', NULL, NULL, NULL, NULL, NULL, '1'); 


-- left_certificate_form_image === Create New folder in Api  storege/app


ALTER TABLE `fee_slip_master` ADD COLUMN `transaction_no` INT DEFAULT 0 NULL COMMENT 'only use for kuickpay' AFTER `payment_status`; 

ALTER TABLE `reg_slip_detail` ADD COLUMN `discount_percentage` INT(11) NULL AFTER `disc_type_id`; 

ALTER TABLE `discount_policy` CHANGE `disc_percentage` `disc_percentage` INT(11) NOT NULL; 

ALTER TABLE `fee_slip_master` ADD COLUMN `payment_by_channel` INT(11) NULL COMMENT 'Payment by Bank ID' AFTER `bank_id`; 

ALTER TABLE `reg_slip_master` CHANGE `kuickpay_id` `kuickpay_id` BIGINT NULL, CHANGE `bank_id` `bank_id` BIGINT NULL; 
ALTER TABLE `fee_slip_master` CHANGE `kuickpay_id` `kuickpay_id` BIGINT NOT NULL, CHANGE `bank_id` `bank_id` BIGINT NOT NULL; 

ALTER TABLE `fee_slip_master` CHANGE `section_id` `section_id` INT(11) NULL; 