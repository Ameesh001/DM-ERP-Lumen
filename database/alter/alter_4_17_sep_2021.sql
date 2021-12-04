-- <Ameesh>
ALTER TABLE `session` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `subject` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `fee_type` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `admission_type` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `class` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `section` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `id`;

ALTER TABLE `campus_class` DROP COLUMN `organization_id`, DROP COLUMN `countries_id`, DROP COLUMN `state_id`, DROP COLUMN `region_id`, DROP COLUMN `city_id`, DROP INDEX `campus_class_organization_id_foreign`, DROP INDEX `campus_class_countries_id_foreign`, DROP INDEX `campus_class_state_id_foreign`, DROP INDEX `campus_class_region_id_foreign`, DROP INDEX `campus_class_city_id_foreign`, DROP FOREIGN KEY `campus_class_city_id_foreign`, DROP FOREIGN KEY `campus_class_countries_id_foreign`, DROP FOREIGN KEY `campus_class_organization_id_foreign`, DROP FOREIGN KEY `campus_class_region_id_foreign`, DROP FOREIGN KEY `campus_class_state_id_foreign`;
ALTER TABLE `campus_fee` DROP COLUMN `organization_id`, DROP COLUMN `countries_id`, DROP COLUMN `state_id`, DROP COLUMN `region_id`, DROP COLUMN `city_id`, DROP INDEX `campus_fee_organization_id_foreign`, DROP INDEX `campus_fee_countries_id_foreign`, DROP INDEX `campus_fee_state_id_foreign`, DROP INDEX `campus_fee_region_id_foreign`, DROP INDEX `campus_fee_city_id_foreign`, DROP FOREIGN KEY `campus_fee_city_id_foreign`, DROP FOREIGN KEY `campus_fee_countries_id_foreign`, DROP FOREIGN KEY `campus_fee_organization_id_foreign`, DROP FOREIGN KEY `campus_fee_region_id_foreign`, DROP FOREIGN KEY `campus_fee_state_id_foreign`; 

-- </Ameesh>



ALTER TABLE `discount_policy` ADD COLUMN `no_of_month` INT DEFAULT 1 NULL AFTER `id`; 

ALTER TABLE `reg_slip_master` ADD COLUMN `total_fees` INT DEFAULT 0 NULL AFTER `has_discount`, ADD COLUMN `total_discount` INT DEFAULT 0 NULL AFTER `total_fees`, ADD COLUMN `payable_amount` INT DEFAULT 0 NULL AFTER `total_discount`; 


ALTER TABLE `reg_slip_master` CHANGE `rec_date` `rec_date` DATE NULL; 

ALTER TABLE `reg_slip_master` CHANGE `kuickpay_id` `kuickpay_id` INT(11) NULL, CHANGE `bank_id` `bank_id` INT(11) NULL; 

ALTER TABLE `reg_slip_master` CHANGE `has_discount` `has_discount` INT(11) NULL; 

ALTER TABLE `assign_fee_structure` CHANGE `region_id` `region_id` INT(11) NULL, CHANGE `campus_id` `campus_id` INT(11) NULL, CHANGE `class_id` `class_id` INT(11) NULL, DROP FOREIGN KEY `assign_fee_structure_campus_id_foreign`, DROP FOREIGN KEY `assign_fee_structure_class_id_foreign`, DROP FOREIGN KEY `assign_fee_structure_region_id_foreign`; 

ALTER TABLE `reg_slip_master` ADD COLUMN `slip_month_name` VARCHAR(20) NULL AFTER `slip_month_code`; 

ALTER TABLE `assign_discount_policy` CHANGE `class_id` `class_id` INT(11) NULL; 
ALTER TABLE `assign_discount_policy` CHANGE `state_id` `state_id` INT(11) NULL, CHANGE `region_id` `region_id` INT(11) NULL, CHANGE `city_id` `city_id` INT(11) NULL, CHANGE `campus_id` `campus_id` INT(11) NULL, CHANGE `student_adm_id` `student_adm_id` INT(11) NULL; 


ALTER TABLE `reg_slip_detail` ADD COLUMN `disc_type_id` INT(11) DEFAULT 0 NULL AFTER `fees_type_id`; 


ALTER TABLE `student_registration` ADD COLUMN `full_name` VARCHAR(100) NULL AFTER `last_name`; 



DELIMITER $$

DROP TRIGGER  `admission_sequence_generator`$$

CREATE
    TRIGGER `admission_sequence_generator` BEFORE INSERT ON `student_admission` 
    FOR EACH ROW BEGIN
    
    SELECT MAX(CAST(SUBSTRING(`admission_code`, 6, LENGTH(`admission_code`)-5) AS UNSIGNED)) + 1  INTO @admissionTotalID  FROM student_admission; 
    
    SET NEW.admission_code = LPAD(IF(@admissionTotalID IS NULL , 1, @admissionTotalID ), 6, '0') ;
 END;
$$

DELIMITER ;



DELIMITER $$

DROP TRIGGER  `admission_sequence_generator_delete_restrict`$$

CREATE
    TRIGGER `admission_sequence_generator_delete_restrict` BEFORE DELETE ON `student_admission` 
    FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sorry! Delete Query are Blocked!';
END;
$$

DELIMITER ;
-- usama




DELIMITER $$

DROP TRIGGER  `fs_sequence_generator`$$

CREATE
    TRIGGER `fs_sequence_generator` BEFORE INSERT ON `fee_structure_master` 
    FOR EACH ROW BEGIN
    
    SELECT MAX(CAST(SUBSTRING(fees_code, 3, LENGTH(fees_code)-2) AS UNSIGNED)) + 1  INTO @feeMasterTotalID  FROM fee_structure_master; 
    
    SET NEW.fees_code = CONCAT('FE', LPAD(IF(@feeMasterTotalID IS NULL , 1, @feeMasterTotalID ), 3, '0')) ;
 END;
$$

DELIMITER ;

DELIMITER $$

DROP TRIGGER  `fs_sequence_generator_delete_restrict`$$

CREATE
    TRIGGER `fs_sequence_generator_delete_restrict` BEFORE DELETE ON `fee_structure_master` 
    FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sorry! Delete Query are Blocked!';
END;
$$

DELIMITER ;



DELIMITER $$


DROP TRIGGER /*!50032 IF EXISTS */ `disc_policy_sequence_generator`$$

CREATE
  
    TRIGGER `disc_policy_sequence_generator` BEFORE INSERT ON `discount_policy` 
    FOR EACH ROW BEGIN
    
    SELECT MAX(CAST(SUBSTRING(disc_code, 3, LENGTH(disc_code)-2) AS UNSIGNED)) + 1  INTO @dicsTotalID  FROM discount_policy; 
    
    SET NEW.disc_code = CONCAT('DP', LPAD(IF(@dicsTotalID IS NULL , 1, @dicsTotalID ), 3, '0')) ;
 END;
$$

DELIMITER ;


DELIMITER $$


DROP TRIGGER /*!50032 IF EXISTS */ `disc_policy_sequence_generator_delete_restrict`$$

CREATE
    
    TRIGGER `disc_policy_sequence_generator_delete_restrict` BEFORE DELETE ON `discount_policy` 
    FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sorry! Delete Query are Blocked!';
END;
$$

DELIMITER ;





INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '3', 'Mar', 'Mar-2021', '2021', '202103', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '4', 'Apr', 'Apr-2021', '2021', '202104', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '5', 'May', 'May-2021', '2021', '202105', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '6', 'Jun', 'Jun-2021', '2021', '202106', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '7', 'Jul', 'Jul-2021', '2021', '202107', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '8', 'Aug', 'Aug-2021', '2021', '202108', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '9', 'Sep', 'Sep-2021', '2021', '202109', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '10', 'Oct', 'Oct-2021', '2021', '202110', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '11', 'Nov', 'Nov-2021', '2021', '202111', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `session_month` (`id`, `session_id`, `month_no`, `month_name`, `month_full_name`, `year_no`, `month_index`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', '12', 'Dec', 'Dec-2021', '2021', '202112', NULL, NULL, NULL, NULL, NULL, '1'); 


insert  into `fee_type`(`id`,`fee_type`,`fee_desc`,`is_enable`,`created_at`,`updated_at`,`created_by`,`updated_by`,`deleted_at`) values (1,'Tuition Fee','ID Card Fee for description',1,NULL,'2021-08-12 16:28:45',NULL,1,'2021-08-12 16:00:14'),(2,'ID Card Fee','Admission Fee Description only first item payment',1,NULL,'2021-08-12 16:38:54',NULL,1,NULL),(3,'Jun-July Fee','Admission Fee Oct Description',1,NULL,'2021-08-23 11:35:14',NULL,1,NULL),(4,'Annual Fee','Tuition Fee Description here',1,NULL,'2021-08-12 16:37:19',NULL,1,NULL),(5,'Admission Fee','Tuition Fee Description here',1,NULL,'2021-08-12 16:37:19',NULL,1,NULL);




INSERT INTO `fee_structure_master` (`id`, `organization_id`, `fees_code`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES (NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '1');

INSERT INTO `fee_structure_detail` (`fees_code`, `id`, `fees_master_id`, `fees_type_id`, `fees_amount`, `fees_from_date`, `fees_end_date`, `fees_is_new_addmission`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES ('FE001', NULL, '1', '1', '3300', '2021-01-01', '2021-05-31', '0', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `fee_structure_detail` (`fees_code`, `id`, `fees_master_id`, `fees_type_id`, `fees_amount`, `fees_from_date`, `fees_end_date`, `fees_is_new_addmission`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES ('FE001', NULL, '1', '2', '200', '2021-04-01', '2021-04-30', '0', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `fee_structure_detail` (`fees_code`, `id`, `fees_master_id`, `fees_type_id`, `fees_amount`, `fees_from_date`, `fees_end_date`, `fees_is_new_addmission`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES ('FE001', NULL, '1', '3', '6600', '2021-06-01', '2021-07-31', '0', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `fee_structure_detail` (`fees_code`, `id`, `fees_master_id`, `fees_type_id`, `fees_amount`, `fees_from_date`, `fees_end_date`, `fees_is_new_addmission`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES ('FE001', NULL, '1', '4', '4000', '2021-10-01', '2021-10-31', '0', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `fee_structure_detail` (`fees_code`, `id`, `fees_master_id`, `fees_type_id`, `fees_amount`, `fees_from_date`, `fees_end_date`, `fees_is_new_addmission`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES ('FE001', NULL, '1', '1', '3300', '2021-08-01', '2021-12-31', '0', NULL, NULL, NULL, NULL, NULL, '1'); 
INSERT INTO `fee_structure_detail` (`fees_code`, `id`, `fees_master_id`, `fees_type_id`, `fees_amount`, `fees_from_date`, `fees_end_date`, `fees_is_new_addmission`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_enable`) VALUES ('FE001', NULL, '1', '5', '4400', '', '', '1', NULL, NULL, NULL, NULL, NULL, '1');


insert into `bank`(`id`,`organization_id`,`name`,`ac_no`,`type`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) 
values (1,1,'UBL Bank Ltd','222694359',1,'2021-09-16 16:44:51',NULL,NULL,NULL,'2021-09-16 16:44:31',1),(2,1,'Kuickpay','73060',2,'2021-09-16 16:44:55',NULL,NULL,NULL,'2021-09-16 16:44:45',1);

insert  into `discount_type`(`id`,`discount_type`,`discount_desc`,`is_enable`,`created_at`,`updated_at`,`created_by`,`updated_by`,`deleted_at`) 
values (1,'S.P Subsidy','Special Discount(Subsidy) Description',1,NULL,'2021-08-13 11:45:03',NULL,1,NULL),(2,'Structure Discount (Old Fee & Other)','Structure Discount (Old Fee & Other)',1,NULL,NULL,NULL,NULL,NULL),(3,'Admission 30% Off','New Admission 30% Off',1,NULL,NULL,NULL,NULL,NULL);

insert  into `discount_policy`(`id`,`no_of_month`,`disc_code`,`discount_type`,`fees_type_id`,`disc_percentage`,`condition`,`discription`,`disc_amount`,`disc_from_date`,`disc_end_date`,`disc_is_new_addmission`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) 
values (1,1,'DP001',3,1,'30','','New registration ',0,'2021-07-01','2021-07-31',1,NULL,NULL,NULL,NULL,NULL,1),(2,1,'DP002',1,1,'25','','aa',0,'2021-09-01','2021-09-30',1,NULL,NULL,NULL,NULL,NULL,1),(3,2,'DP003',1,1,'15','','aa',0,'2021-09-01','2021-09-30',1,NULL,NULL,NULL,NULL,NULL,1);




insert  into `assign_discount_policy`
(`id`,`disc_code`,`organization_id`,`country_id`,`state_id`,`region_id`,`city_id`,`campus_id`,`class_id`,`student_adm_id`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) 
values (1,'DP001',1,1,7,3,1,1,10,0,NULL,NULL,NULL,NULL,NULL,1),
(7,'DP002',1,1,7,3,1,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,1),
(8,'DP003',1,1,7,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1);


insert  into `assign_fee_structure`(`id`,`fees_code`,`organization_id`,`country_id`,`state_id`,`region_id`,`city_id`,`campus_id`,`class_id`,`student_adm_id`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) 
values (1,'FE001',1,1,7,3,1,1,0,0,NULL,NULL,NULL,NULL,NULL,1);



TRUNCATE TABLE 'reg_rept_note';

insert  into `reg_rept_note`(`id`,`note_type`,`type`,`organization_id`,`state_id`,`region_id`,`city_id`,`sort_no`,`is_enable`,`note`,`created_by`,`created_at`,`updated_by`,`updated_at`) values (1,1,1,1,1,1,1,1,1,'اس رسید کو سنبھال کر رکھئے کہ یہ اگلے مراحل کے لئے مطلوب ہے۔',1,'2021-09-13 12:31:47',1,'2021-09-13 12:32:07'),(2,1,1,1,1,1,1,2,1,'. ایک سے زائد رجسٹریشن کروانے کی صورت میں درخواست گزار کا نام نکال دیا جائے گا۔',1,'2021-09-13 14:38:39',NULL,NULL),(3,1,1,1,1,1,1,3,1,'یہ رجسٹریشن داخلے کی ضمانت نہیں ہے۔ نیز صرف اسی دارالمدینہ کیمپس کے لئے ہے۔',1,'2021-09-13 14:38:39',NULL,NULL),(4,1,1,1,1,1,1,4,1,'ٹسٹ/انٹرویو کے لئے مقررہ دن/وقت میں تشریف لائیں، بصورتِ دیگر دارالمدینہ ٹیسٹ انٹرویو لینے کا پابند نہ ہوگا۔',1,'2021-09-13 14:38:39',NULL,NULL),(5,1,1,1,1,1,1,5,1,'ٹیسٹ/انٹرویو داخلے کے لئے لازمی مرحلہ ہے۔',1,'2021-09-13 14:38:39',NULL,NULL),(6,1,1,1,1,1,1,6,1,'بچے/بچی کو اسکول پہنچانے اور لے جانے کی ذمہ داری والدین/سرپرست کی ہوگی۔',1,'2021-09-13 14:38:39',NULL,NULL),(7,1,1,1,1,1,1,7,1,'مزید معلومات کے لئے دارالمدینہ کی ویب سائٹ www.darulmadinah.net پر وزٹ کریں۔',1,'2021-09-13 14:38:39',NULL,NULL),(8,1,1,1,1,1,1,8,1,'ٹیسٹ انٹرویو کے لئے آنے والوں کو درج ذیل کاغذات ساتھ لانا لازمی ہیں\r\n',1,'2021-09-13 14:38:39',NULL,NULL),(9,1,2,1,1,1,1,1,1,'فیس کو بینک میں ہی جمع کروائیے۔ کسی بھی شخص کو چاہے کیسا ہی ذمہ دار ہو، فیس کی وصولی کا اختیار نہیں۔ خلاف ورزی کی صورت میں دارالمدینہ ذمہ دار نہ ہوگا۔',1,'2021-09-14 10:41:59',1,'0000-00-00 00:00:00'),(10,2,1,1,1,1,1,1,1,'واؤچر مختلف بینکوں میں جمع کروائے جاسکتے ہیں',1,NULL,NULL,NULL),(11,2,2,1,1,1,1,1,1,'All Branches across Pakistan are designated.',1,NULL,NULL,NULL),(12,2,2,1,1,1,1,2,1,'Only Cash Deposit is acceptable.',1,'2021-09-16 12:16:07',NULL,'2021-09-16 12:16:15'),(13,2,2,1,1,1,1,3,1,'Bank Copy of the challan to be retained by the branch while school copy and studnet copy must be handed over to the depositor.',1,'2021-09-16 12:16:07',NULL,'2021-09-16 12:16:15'),(14,2,2,1,1,1,1,4,1,'The fee challan is only acceptable till due date. after due date branches are not authorized to accept fee challan.',1,'2021-09-16 12:16:07',NULL,'2021-09-16 12:16:15'),(15,2,1,1,1,1,1,2,1,' فیس کو بینک میں ہی جمع کروائیے۔ کسی بھی شخص کو چاہے کیسا ہی ذمہ دار ہو، فیس کی وصولی کا اختیار نہیں۔ خلاف ورزی کی صورت میں دارالمدینہ ذمہ دار نہ ہوگا۔',1,NULL,NULL,NULL),(16,1,1,1,1,1,1,9,1,'* اصل رجسٹریشن رسید',1,'2021-09-13 14:38:39',NULL,NULL),(17,1,1,1,1,1,1,10,1,'* بچے/بچی کا کمپیوٹرائزڈ (ب) فارم یا کمپیوٹرائزڈ برتھ سرٹیفیکٹ',1,'2021-09-13 14:38:39',NULL,NULL),(18,1,1,1,1,1,1,11,1,'والدین کا کمپیوٹرائزڈ شناختی کارڈ (CNIC)',1,'2021-09-13 14:38:39',NULL,NULL);




-- usama


INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Generate Monthly Voucher', 'admission_setup/gen_monthly_voucher', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"GET\":\"View\"}', '1', '1', '1', '1', '2021-09-24 14:27:13', NULL, '1', NULL, NULL);
