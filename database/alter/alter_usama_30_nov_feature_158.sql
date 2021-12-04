ALTER TABLE `std_registration_interview_test` CHANGE `is_enable` `is_enable` INT(11) DEFAULT 1 NULL; 


ALTER TABLE `discount_policy` CHANGE `disc_percentage` `disc_percentage` DECIMAL(11,2) NOT NULL; 


DROP TRIGGER `admission_sequence_generator`; 

CREATE TRIGGER `admission_sequence_generator` BEFORE INSERT ON `student_admission` FOR EACH ROW BEGIN SELECT MAX(CAST(`admission_code` AS UNSIGNED)) + 1 INTO @admissionTotalID FROM student_admission ; SET NEW.admission_code = LPAD(IF(@admissionTotalID IS NULL , 1, @admissionTotalID ), 6, '0') ; END; ;