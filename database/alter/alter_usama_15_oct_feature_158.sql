ALTER TABLE `assign_fee_structure` CHANGE `class_id` `class_id` INT(11) NULL DEFAULT NULL;

ALTER TABLE `users` ADD COLUMN `is_superadmin` INT(11) DEFAULT 0 NULL COMMENT '1:superadmin|it, 0:normaluser|other' AFTER `user_type`; 
