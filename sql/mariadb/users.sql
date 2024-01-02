-- Active: 1704156387972@@127.0.0.1@3306@site
-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               11.2.2-MariaDB-1:11.2.2+maria~ubu2204 - mariadb.org binary distribution
-- Операционная система:         debian-linux-gnu
-- HeidiSQL Версия:              12.5.0.6749
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Дамп структуры для таблица site.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL DEFAULT '',
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `password` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL,
  `accessToken` char(32) DEFAULT NULL,
  `serverID` varchar(41) DEFAULT NULL,
  `hwidId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`user_id`) USING BTREE,
  UNIQUE KEY `name` (`username`) USING BTREE,
  UNIQUE KEY `uuid` (`uuid`) USING BTREE,
  UNIQUE KEY `email` (`email`),
  KEY `uuid_idx` (`uuid`) USING BTREE,
  KEY `username_idx` (`username`) USING BTREE,
  KEY `users_hwidfk` (`hwidId`),
  CONSTRAINT `users_hwidfk` FOREIGN KEY (`hwidId`) REFERENCES `hwids` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
