INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES ('71', 'Admission Module', 'admission_setup', NULL, 'mdi mdi-folder-multiple-outline', '0', '1', NULL, '1', '1', '0', '1', '2021-09-06 12:00:54', NULL, NULL, NULL, NULL); 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES ('72', 'Registration', 'admission_setup/registration', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"PATCH\":\"Status\",\"DELETE\":\"Remove\",\"GET\":\"View\"}', '1', '1', '1', '1', '2021-09-06 14:51:37', NULL, NULL, NULL, NULL); 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES ('73', 'Test and Interview', 'admission_setup/test_interview', NULL, 'mdi mdi-folder-multiple-outline', '71', '0', '{\"POST\":\"Add\",\"GET\":\"View\"}', '1', '1', '1', '1', '2021-09-06 14:51:37', NULL, NULL, NULL, NULL); 


CREATE TABLE `occupation`( `id` INT NOT NULL AUTO_INCREMENT, `occupation_name` VARCHAR(200), `is_enable` INT DEFAULT 1, PRIMARY KEY (`id`) );
INSERT INTO `occupation` (`id`, `occupation_name`, `is_enable`) VALUES (NULL, 'Teacher', '1'); 
INSERT INTO `occupation` (`id`, `occupation_name`, `is_enable`) VALUES (NULL, 'Private Job', '1'); 
INSERT INTO `occupation` (`id`, `occupation_name`, `is_enable`) VALUES (NULL, 'Doctor', '1'); 




ALTER TABLE `student_registration` DROP COLUMN `nationality`; 

ALTER TABLE `student_registration` ADD COLUMN `reg_code_prefix` VARCHAR(50) NULL AFTER `admission_type_id`; 


ALTER TABLE `student_registration` ADD COLUMN `organization_id` INT NOT NULL AFTER `id`;


UPDATE `countries` SET `short_code` = 'PK' WHERE `id` = '1'; 


DELIMITER $$

DROP TRIGGER /*!50032 IF EXISTS */ `register_sequence_generator`$$

CREATE

    TRIGGER `register_sequence_generator` BEFORE INSERT ON `student_registration` 
    FOR EACH ROW BEGIN
    
    SELECT MAX(CAST(SUBSTRING(registration_code, 6, LENGTH(registration_code)-5) AS UNSIGNED)) + 1  INTO @reg_totalID  FROM student_registration WHERE reg_code_prefix = NEW.reg_code_prefix AND organization_id = NEW.organization_id; 
    
    SET NEW.registration_code = CONCAT(NEW.reg_code_prefix, LPAD(IF(@reg_totalID IS NULL , 1, @reg_totalID ), 6, '0')) ;
 END;
$$

DELIMITER ;

DELIMITER $$


DROP TRIGGER /*!50032 IF EXISTS */ `student_registraion_delete_restrict`$$

CREATE
    TRIGGER `student_registraion_delete_restrict` BEFORE DELETE ON `student_registration` 
    FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sorry! Delete Query are Blocked!';
END;
$$

DELIMITER ;



ALTER TABLE `std_registration_interview_test` ADD COLUMN `test_date` DATE NULL AFTER `final_result_id`, ADD COLUMN `is_enable` INT(1) NULL AFTER `is_seat_alloted`;



UPDATE `auth_modules` SET `is_visible` = '1' WHERE `id` = '16'; 
UPDATE `auth_modules` SET `is_visible` = '1' WHERE `id` = '18';