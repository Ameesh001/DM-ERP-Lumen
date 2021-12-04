
-- </usama>
ALTER TABLE `fee_structure_detail` CHANGE `fees_is_new_addmission` `fees_is_new_addmission` INT(11) DEFAULT 0 NOT NULL COMMENT '0: monthly fees, 1: admission fees';
ALTER TABLE `assign_discount_policy` CHANGE `student_adm_id` `admission_code` VARCHAR(20) NULL; 

ALTER TABLE `discount_policy` CHANGE `disc_is_new_addmission` `disc_is_new_addmission` INT(11) DEFAULT 0 NOT NULL COMMENT '0: monthly, 1:admission';
-- </usama>



--<usama>
ALTER TABLE `fee_slip_detail` ADD COLUMN `disc_type_id` INT(11) NULL AFTER `fee_amount`, ADD CONSTRAINT `fee_slip_detail_disc_type_id_foreign` FOREIGN KEY (`disc_type_id`) REFERENCES `discount_type`(`id`); 

ALTER TABLE `fee_slip_detail` CHANGE `discount_percentage` `discount_percentage` INT(11) NULL, CHANGE `discount_amount` `discount_amount` INT(11) NULL;

ALTER TABLE `fee_slip_detail` ADD COLUMN `is_discount_entry` INT(11) DEFAULT 0 NULL AFTER `fee_amount`;

ALTER TABLE `fee_slip_master` ADD COLUMN `payment_status` INT(11) DEFAULT 0 NULL COMMENT '0: Amount unpaid, 1:Amount Paid' AFTER `bank_id`;

ALTER TABLE `slip_type` ADD COLUMN `prefix` VARCHAR(20) NULL COMMENT 'mon, adv, ins, adm' AFTER `id`; 

ALTER TABLE `slip_type` CHANGE `prefix` `prefix` VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'mon' NULL COMMENT 'mon, adv, ins, adm'; 

insert  into `slip_type`(`id`,`prefix`,`slip_type`,`organization_id`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) values (1,'mon','Monthly Fee',1,NULL,NULL,NULL,NULL,NULL,1),(2,'adv','Advance Fee',1,NULL,NULL,NULL,NULL,NULL,1),(3,'ins','Installment Fee',1,NULL,NULL,NULL,NULL,NULL,1),(4,'adm','Admission Fee',1,NULL,NULL,NULL,NULL,NULL,1);

ALTER TABLE `fee_slip_master` ADD COLUMN `std_admission_id` INT(11) NOT NULL AFTER `id`; 

ALTER TABLE `fee_slip_master` ADD CONSTRAINT `fee_slip_master_std_admission_id_foreign` FOREIGN KEY (`std_admission_id`) REFERENCES `student_admission`(`id`) ON UPDATE RESTRICT ON DELETE RESTRICT;



ALTER TABLE `fee_slip_master` ADD COLUMN `fee_month` VARCHAR(20) NOT NULL AFTER `section_id`, ADD COLUMN `fee_month_code` VARCHAR(20) NOT NULL AFTER `fee_month`, ADD COLUMN `fee_date` DATE NOT NULL AFTER `fee_month_code`;
ALTER TABLE `fee_slip_detail` DROP COLUMN `fee_month`, DROP COLUMN `fee_month_code`, DROP COLUMN `fee_date`; 

insert  into `slip_setup`(`id`,`slip_type_id`,`month_close_date`,`issue_date`,`due_date`,`validity_date`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) values (1,1,'2021-09-30','2021-09-29','2021-10-01','2021-10-01',NULL,NULL,NULL,NULL,NULL,1),(2,2,'2021-09-30','2021-09-29','2021-10-01','2021-10-01',NULL,NULL,NULL,NULL,NULL,1);

--</usama>