INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Region', 'setup/region', NULL, 'mdi mdi-folder-multiple-outline', '15', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 
INSERT INTO `auth_modules` ( `id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at` ) VALUES ( NULL, 'City', 'setup/city', NULL, 'mdi mdi-folder-multiple-outline', '15', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL ) ;


ALTER TABLE `city` ADD COLUMN `organization_id` INT(11) NOT NULL AFTER `updated_by`, ADD CONSTRAINT `city_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `darulmadinah_mysql`.`organization_list`(`id`) ON UPDATE RESTRICT ON DELETE RESTRICT; 

INSERT INTO `auth_modules` (`id`, `name`, `default_url`, `module_name`, `icon_class`, `parent_id`, `have_child`, `allowed_permissions`, `sorting`, `is_visible`, `detail`, `is_enable`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES (NULL, 'Campus', 'setup/campus', NULL, 'mdi mdi-folder-multiple-outline', '15', '0', '{\"POST\":\"Add\",\"PUT\":\"Update\",\"GET\":\"View\",\"DELETE\":\"Remove\"}', '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL); 

ALTER TABLE `countries` ADD COLUMN `lang_id` VARCHAR(50) NULL AFTER `id`, ADD COLUMN `lang_name` VARCHAR(250) NULL AFTER `lang_id`; 

ALTER TABLE `languages` ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, DROP PRIMARY KEY, ADD PRIMARY KEY (`id`); 


ALTER TABLE `countries` CHANGE `lang_id` `lang_id` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL; 