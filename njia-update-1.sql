ALTER TABLE `user_table` ADD `expiration_date` DATE DEFAULT NULL COMMENT 'Set NULL for accounts which do not expire';
ALTER TABLE `user_table` ADD `email` VARCHAR(60) DEFAULT NULL AFTER `login_name`;
ALTER TABLE `user_table` ADD `last_login_date` TIMESTAMP NULL DEFAULT NULL AFTER `user_creation_date`;