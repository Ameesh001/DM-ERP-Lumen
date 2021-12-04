ALTER TABLE `fee_slip_master` ADD COLUMN `total_fees` INT(11) NULL AFTER `bank_id`, ADD COLUMN `total_discount` INT(11) NULL AFTER `total_fees`, ADD COLUMN `total_payable_amount` INT(11) NULL AFTER `total_discount`, ADD COLUMN `total_payable_amount_words` VARCHAR(50) NULL AFTER `total_payable_amount`;

ALTER TABLE `fee_slip_master` ADD COLUMN `fee_actual_date` DATE NULL AFTER `section_id`; 