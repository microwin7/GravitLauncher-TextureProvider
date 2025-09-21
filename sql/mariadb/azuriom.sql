CREATE TABLE `user_assets` (
	`user_id` INT(10) UNSIGNED NOT NULL,
	`type` ENUM('SKIN','CAPE') NOT NULL COLLATE 'utf8mb4_general_ci',
	`hash` TINYTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`meta` ENUM('SLIM') NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`user_id`, `type`) USING BTREE,
	INDEX `uid` (`user_id`) USING BTREE,
	INDEX `uid_name` (`user_id`, `type`) USING BTREE,
	CONSTRAINT `FK_user_assets_from_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;