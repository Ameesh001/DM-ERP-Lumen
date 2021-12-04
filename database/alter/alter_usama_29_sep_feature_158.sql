-- <Usama>

ALTER TABLE `fee_slip_master` ADD COLUMN `fees_master_id` INT(11) NOT NULL AFTER `id`, ADD CONSTRAINT `fee_slip_fee_master_id_foreign` FOREIGN KEY (`fees_master_id`) REFERENCES `fee_structure_master`(`id`);

ALTER TABLE `fee_slip_master` ADD COLUMN `challan_no` VARCHAR(20) NOT NULL AFTER `fees_master_id`;

ALTER TABLE `student_admission` 
  ADD COLUMN `student_status` INT (11) DEFAULT 1 NOT NULL AFTER `other_language`,
  ADD CONSTRAINT `student_admission_students_status_id_foreign` FOREIGN KEY (`student_status`) REFERENCES `students_status` (`id`) ;


ALTER TABLE `assign_fee_structure` CHANGE `student_adm_id` `admission_code` VARCHAR(20) NULL; 

ALTER TABLE `assign_fee_structure` CHANGE `state_id` `state_id` INT(11) NULL, CHANGE `city_id` `city_id` INT(11) NULL; 


-- php artisan db:seed --class=StudentsStatus


-- </Usama>
