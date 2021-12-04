INSERT INTO `auth_modules` ( `id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at` ) VALUES ( NULL, 'Department', 'setup/department', NULL, 'mdi mdi-folder-multiple-outline', '15', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL ) ;  
INSERT INTO `auth_modules` ( `id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at` ) VALUES ( NULL, 'Designation', 'setup/designation', NULL, 'mdi mdi-folder-multiple-outline', '15', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL );


  
  
  
-- php artisan db:seed --class=UserHierarchyLevels
  
  
ALTER TABLE `auth_role_module_perms` ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`); 


ALTER TABLE `users` 
  DROP COLUMN `role_id`,
  ADD COLUMN `department_id` INT (11) NULL AFTER `user_type`,
  ADD COLUMN `designation_id` INT (11) NULL AFTER `department_id`,
  ADD COLUMN `is_teacher` TINYINT (1) NULL AFTER `designation_id`,
  ADD COLUMN `whatsapp_num` VARCHAR (15) NULL AFTER `is_teacher`,
  ADD COLUMN `gender` TINYINT (1) NULL AFTER `whatsapp_num`,
  ADD COLUMN `education` VARCHAR (50) NULL AFTER `gender`,
  ADD COLUMN `reporting_manager` INT (11) NULL AFTER `education`,
  DROP INDEX `idx_role_id`,
  DROP FOREIGN KEY `users_role_id_fk` ;


ALTER TABLE `user_role_levels` ADD COLUMN `role_ids_obj` TEXT NULL AFTER `role_id`;
ALTER TABLE `user_data_permission` ADD COLUMN `data_permissions_obj` TEXT NULL AFTER `data_permissions_id`; 

ALTER TABLE `users` ADD COLUMN `is_manager` TINYINT(1) NULL AFTER `reporting_manager`;

INSERT INTO `auth_modules` ( `id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at` ) VALUES ( NULL, 'Seating Capacity', 'campus_setup/seating_capacity', NULL, 'mdi mdi-folder-multiple-outline', '59', '0', '{"POST":"Add","PUT":"Update","PATCH":"Status","DELETE":"Remove","GET":"View"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL ) ;


ALTER TABLE `campus_subject`  DROP FOREIGN KEY `campus_subject_city_id_foreign`, DROP FOREIGN KEY `campus_subject_class_id_foreign`, DROP FOREIGN KEY `campus_subject_countries_id_foreign`, DROP FOREIGN KEY `campus_subject_organization_id_foreign`, DROP FOREIGN KEY `campus_subject_region_id_foreign`, DROP FOREIGN KEY `campus_subject_state_id_foreign`;
ALTER TABLE `campus_subject` DROP COLUMN `organization_id`, DROP COLUMN `countries_id`, DROP COLUMN `state_id`, DROP COLUMN `region_id`, DROP COLUMN `city_id`, DROP INDEX `campus_subject_organization_id_foreign`, DROP INDEX `campus_subject_countries_id_foreign`, DROP INDEX `campus_subject_state_id_foreign`, DROP INDEX `campus_subject_region_id_foreign`, DROP INDEX `campus_subject_city_id_foreign`; 

UPDATE `auth_modules` SET `is_visible` = '1' WHERE `default_url` = 'permission'; 
UPDATE `auth_modules` SET `is_visible` = '1' WHERE `default_url` = 'permission/role_module'; 
UPDATE `auth_modules` SET `is_visible` = '1' WHERE `default_url` = 'permission/user_role'; 
UPDATE `auth_modules` SET `is_visible` = '1' WHERE `default_url` = 'permission/role_permission';
UPDATE `auth_modules` SET `is_enable` = '1'  WHERE `default_url` = 'permission/role_permission';
UPDATE `auth_modules` SET `is_visible` = '1' WHERE `default_url` = 'setup/organization'; 

UPDATE `auth_modules` SET `allowed_permissions` = '{"POST":"Add","PUT":"Update","PATCH":"Status","DELETE":"Remove","GET":"View"}' WHERE allowed_permissions IS NOT NULL; 
