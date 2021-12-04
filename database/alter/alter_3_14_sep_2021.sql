DROP TABLE IF EXISTS `reg_rept_note`;

CREATE TABLE `reg_rept_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_type` int(11) DEFAULT NULL COMMENT '1= Std Regration, 2 = admision voucher',
  `type` int(11) DEFAULT NULL COMMENT '1 = Instruction , 2 = Note',
  `organization_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `sort_no` int(11) NOT NULL,
  `is_enable` int(11) NOT NULL DEFAULT 1,
  `note` text CHARACTER SET utf8 NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

insert  into `reg_rept_note`(`id`,`note_type`,`type`,`organization_id`,`state_id`,`region_id`,`city_id`,`sort_no`,`is_enable`,`note`,`created_by`,`created_at`,`updated_by`,`updated_at`) values (1,1,1,1,1,1,1,1,1,'\r\nبرائے مہربانی اس رسید کو محفوظ طریقے سے رکھیں کہ اس کے لیے آئندہ کی ضرورت ہوگی۔ ',1,'2021-09-13 12:31:47',1,'2021-09-13 12:32:07'),(2,1,1,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-13 14:38:39',NULL,NULL),(3,1,1,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-13 14:38:39',NULL,NULL),(4,1,1,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-13 14:38:39',NULL,NULL),(5,1,1,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-13 14:38:39',NULL,NULL),(6,1,1,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-13 14:38:39',NULL,NULL),(7,1,1,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-13 14:38:39',NULL,NULL),(8,1,1,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-13 14:38:39',NULL,NULL),(9,1,2,1,1,1,1,1,1,'براہ کرم یہ رسید اپنے پاس رکھیں۔',1,'2021-09-14 10:41:59',1,'0000-00-00 00:00:00'),(10,2,1,1,1,1,1,1,1,'des',1,NULL,NULL,NULL);


INSERT INTO `auth_modules` (
    `id`,
    `name`,
    `default_url`,
    `module_name`,
    `icon_class`,
    `parent_id`,
    `have_child`,
    `allowed_permissions`,
    `sorting`,
    `is_visible`,
    `detail`,
    `is_enable`,
    `created_at`,
    `updated_at`,
    `created_by`,
    `updated_by`,
    `deleted_at`
  ) 
  VALUES
    (
      NULL,
      'Generate Admission Voucher',
      'admission_setup/gen_admission_voucher',
      NULL,
      'mdi mdi-folder-multiple-outline',
      '71',
      '0',
      '{"POST":"Add","PUT":"Update","PATCH":"Status","DELETE":"Remove","GET":"View"}',
      '1',
      '1',
      '1',
      '1',
      NULL,
      NULL,
      NULL,
      NULL,
      NULL
    ) ;




---- new

ALTER TABLE `std_registration_interview_test` CHANGE `is_seat_alloted` `is_seat_alloted` INT(11) DEFAULT 0 NULL; 

ALTER TABLE `student_registration` CHANGE `registration_date` `registration_date` DATE NOT NULL; 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Teacher Subject Assign', 'campus_setup/teacher_subject_assign', NULL, 'mdi mdi-folder-multiple-outline', '59', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL);

UPDATE `auth_modules` SET `allowed_permissions` = '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}' WHERE `id` = '16';

insert  into `bank`(`id`,`organization_id`,`name`,`ac_no`,`type`,`deleted_at`,`created_by`,`updated_by`,`created_at`,`updated_at`,`is_enable`) values (1,1,'UBL Bank Ltd','222694359',1,'2021-09-16 16:44:51',NULL,NULL,NULL,'2021-09-16 16:44:31',1),(2,1,'Kuickpay','73061',2,'2021-09-16 16:44:55',NULL,NULL,NULL,'2021-09-16 16:44:45',1);

ALTER TABLE `student_registration` CHANGE `entry_status` `entry_status` INT(11) DEFAULT 1 NOT NULL COMMENT '1: normal , 2: online'; 



ALTER TABLE `users` ADD COLUMN `is_data_perm` TINYINT(1) DEFAULT 0 NULL AFTER `is_manager`; 