# --------------------------------------------------------
# Host:                         127.0.0.1
# Database:                     recsys-nju
# Server version:               5.1.49-community
# Server OS:                    Win32
# HeidiSQL version:             5.0.0.3272
# Date/time:                    2011-05-01 12:36:18
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
# Dumping database structure for recsys-nju
DROP DATABASE IF EXISTS `recsys-nju`;
CREATE DATABASE IF NOT EXISTS `recsys-nju` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `recsys-nju`;


# Dumping structure for table recsys-nju.agecommunity
DROP TABLE IF EXISTS `agecommunity`;
CREATE TABLE IF NOT EXISTS `agecommunity` (
  `Id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT 'children',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.agecommunity: 0 rows
DELETE FROM `agecommunity`;
/*!40000 ALTER TABLE `agecommunity` DISABLE KEYS */;
INSERT INTO `agecommunity` (`Id`, `name`) VALUES (1, 'children'), (2, 'teenagers'), (3, 'adults'), (4, 'seniors');
/*!40000 ALTER TABLE `agecommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.genrecommunity
DROP TABLE IF EXISTS `genrecommunity`;
CREATE TABLE IF NOT EXISTS `genrecommunity` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.genrecommunity: 0 rows
DELETE FROM `genrecommunity`;
/*!40000 ALTER TABLE `genrecommunity` DISABLE KEYS */;
INSERT INTO `genrecommunity` (`id`, `name`) VALUES (1, 'unknown'), (2, 'action'), (3, 'adventure'), (4, 'animation'), (5, 'children'), (6, 'comedy'), (7, 'crime'), (8, 'documentary'), (9, 'drama'), (10, 'fantasy'), (11, 'film-Noir'), (12, 'horror'), (13, 'musical'), (14, 'mystery'), (15, 'romance'), (16, 'sci-Fi'), (17, 'thriller'), (18, 'war'), (19, 'western');
/*!40000 ALTER TABLE `genrecommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.locationcommunity
DROP TABLE IF EXISTS `locationcommunity`;
CREATE TABLE IF NOT EXISTS `locationcommunity` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT 'New York',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.locationcommunity: 0 rows
DELETE FROM `locationcommunity`;
/*!40000 ALTER TABLE `locationcommunity` DISABLE KEYS */;
INSERT INTO `locationcommunity` (`id`, `name`) VALUES (1, 'Alabama'), (2, 'Alaska'), (3, 'Arigona'), (4, 'Arkansas'), (5, 'California'), (6, 'Colorado'), (7, 'Connecticut'), (8, 'Delaware'), (9, 'Florida'), (10, 'Georgia'), (11, 'Hawaii'), (12, 'Idaho'), (13, 'Illinois'), (14, 'Indiana'), (15, 'Iowa'), (16, 'Kansas'), (17, 'Louisiana'), (18, 'Maine'), (19, 'Massachusetts'), (20, 'Masyland'), (21, 'Michigan'), (22, 'Minnesota'), (23, 'Mississippi'), (24, 'Missouri'), (25, 'Montana'), (26, 'Nevada'), (27, 'New hampshise'), (28, 'New Jersey'), (29, 'New Mexico'), (30, 'New York'), (31, 'North Carolina'), (32, 'North Dakota'), (33, 'Ohio'), (34, 'Oklahoma'), (35, 'Oregon'), (36, 'Pennsylvania'), (37, 'Rhode'), (38, 'South Cardina'), (39, 'South Dakota'), (40, 'Tennessee'), (41, 'Texas'), (42, 'Utah'), (43, 'Vermont'), (44, 'Virginia'), (45, 'Washington'), (46, 'West Visginia'), (47, 'Wisconsin'), (48, 'Wyoming');
/*!40000 ALTER TABLE `locationcommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.movie
DROP TABLE IF EXISTS `movie`;
CREATE TABLE IF NOT EXISTS `movie` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `releaseDate` date NOT NULL,
  `IMDBUrl` varchar(200) DEFAULT NULL,
  `unKnowGenre` int(1) NOT NULL DEFAULT '0',
  `actionGenre` int(1) NOT NULL DEFAULT '0',
  `adventureGenre` int(1) NOT NULL DEFAULT '0',
  `animationGenre` int(1) NOT NULL DEFAULT '0',
  `childrenGenre` int(1) NOT NULL DEFAULT '0',
  `comedyGenre` int(1) NOT NULL DEFAULT '0',
  `crimeGenre` int(1) NOT NULL DEFAULT '0',
  `documentaryGenre` int(1) NOT NULL DEFAULT '0',
  `dramaGenre` int(1) NOT NULL DEFAULT '0',
  `fantasyGenre` int(1) NOT NULL DEFAULT '0',
  `film-NoirGenre` int(1) NOT NULL DEFAULT '0',
  `horrorGenre` int(1) NOT NULL DEFAULT '0',
  `musicalGenre` int(1) NOT NULL DEFAULT '0',
  `mysteryGenre` int(1) NOT NULL DEFAULT '0',
  `romanceGenre` int(1) NOT NULL DEFAULT '0',
  `sci-FiGenre` int(1) NOT NULL DEFAULT '0',
  `thrillerGenre` int(1) NOT NULL DEFAULT '0',
  `warGenre` int(1) NOT NULL DEFAULT '0',
  `westernGenre` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.movie: 0 rows
DELETE FROM `movie`;
/*!40000 ALTER TABLE `movie` DISABLE KEYS */;
/*!40000 ALTER TABLE `movie` ENABLE KEYS */;


# Dumping structure for table recsys-nju.occupationcommunity
DROP TABLE IF EXISTS `occupationcommunity`;
CREATE TABLE IF NOT EXISTS `occupationcommunity` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.occupationcommunity: 0 rows
DELETE FROM `occupationcommunity`;
/*!40000 ALTER TABLE `occupationcommunity` DISABLE KEYS */;
INSERT INTO `occupationcommunity` (`id`, `name`) VALUES (1, 'administrator'), (2, 'artist'), (3, 'doctor'), (4, 'educator'), (5, 'engineer'), (6, 'entertainment'), (7, 'executive'), (8, 'healthcare'), (9, 'homemaker'), (10, 'lawyer'), (11, 'librarian'), (12, 'marketing'), (13, 'none'), (14, 'other'), (15, 'programmer'), (16, 'retired'), (17, 'salesman'), (18, 'scientist'), (19, 'student'), (20, 'technician'), (21, 'writer');
/*!40000 ALTER TABLE `occupationcommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.ratingcommunity
DROP TABLE IF EXISTS `ratingcommunity`;
CREATE TABLE IF NOT EXISTS `ratingcommunity` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL DEFAULT 'Gr#1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.ratingcommunity: 0 rows
DELETE FROM `ratingcommunity`;
/*!40000 ALTER TABLE `ratingcommunity` DISABLE KEYS */;
INSERT INTO `ratingcommunity` (`id`, `name`) VALUES (1, 'Gr#1'), (2, 'Gr#2'), (3, 'Gr#3'), (4, 'Gr#4'), (5, 'Gr#5');
/*!40000 ALTER TABLE `ratingcommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.user
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `age` int(11) unsigned NOT NULL,
  `gender` char(1) NOT NULL DEFAULT 'F',
  `occupation` varchar(100) NOT NULL DEFAULT 'none',
  `zipCode` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.user: 0 rows
DELETE FROM `user`;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;


# Dumping structure for table recsys-nju.user_agecommunity
DROP TABLE IF EXISTS `user_agecommunity`;
CREATE TABLE IF NOT EXISTS `user_agecommunity` (
  `userId` int(255) unsigned NOT NULL,
  `ageCommunityId` int(255) unsigned NOT NULL,
  PRIMARY KEY (`ageCommunityId`,`userId`),
  UNIQUE KEY `userId` (`userId`),
  CONSTRAINT `FK_user_agecommunity_agecommunity` FOREIGN KEY (`ageCommunityId`) REFERENCES `agecommunity` (`Id`),
  CONSTRAINT `FK_user_agecommunity_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.user_agecommunity: 0 rows
DELETE FROM `user_agecommunity`;
/*!40000 ALTER TABLE `user_agecommunity` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_agecommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.user_genrecommunity
DROP TABLE IF EXISTS `user_genrecommunity`;
CREATE TABLE IF NOT EXISTS `user_genrecommunity` (
  `userId` int(255) unsigned NOT NULL,
  `genreCommunityId` int(255) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`genreCommunityId`),
  UNIQUE KEY `userId` (`userId`),
  KEY `FK_user_genrecommunity_genrecommunity` (`genreCommunityId`),
  CONSTRAINT `FK_user_genrecommunity_genrecommunity` FOREIGN KEY (`genreCommunityId`) REFERENCES `genrecommunity` (`id`),
  CONSTRAINT `FK_user_genrecommunity_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.user_genrecommunity: 0 rows
DELETE FROM `user_genrecommunity`;
/*!40000 ALTER TABLE `user_genrecommunity` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_genrecommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.user_locationcommunity
DROP TABLE IF EXISTS `user_locationcommunity`;
CREATE TABLE IF NOT EXISTS `user_locationcommunity` (
  `userId` int(255) unsigned NOT NULL,
  `locationCommunityId` int(255) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`locationCommunityId`),
  UNIQUE KEY `userId` (`userId`),
  KEY `FK_user_locationcommunity_locationcommunity` (`locationCommunityId`),
  CONSTRAINT `FK_user_locationcommunity_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_user_locationcommunity_locationcommunity` FOREIGN KEY (`locationCommunityId`) REFERENCES `locationcommunity` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.user_locationcommunity: 0 rows
DELETE FROM `user_locationcommunity`;
/*!40000 ALTER TABLE `user_locationcommunity` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_locationcommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.user_movie
DROP TABLE IF EXISTS `user_movie`;
CREATE TABLE IF NOT EXISTS `user_movie` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(255) unsigned NOT NULL,
  `movieId` int(255) unsigned NOT NULL,
  `rating` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `FK_User_Movie_user` (`userId`),
  KEY `FK_User_Movie_movie` (`movieId`),
  CONSTRAINT `FK_User_Movie_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_User_Movie_movie` FOREIGN KEY (`movieId`) REFERENCES `movie` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.user_movie: 0 rows
DELETE FROM `user_movie`;
/*!40000 ALTER TABLE `user_movie` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_movie` ENABLE KEYS */;


# Dumping structure for table recsys-nju.user_occupationcommunity
DROP TABLE IF EXISTS `user_occupationcommunity`;
CREATE TABLE IF NOT EXISTS `user_occupationcommunity` (
  `userId` int(255) unsigned NOT NULL,
  `occupationCommunityId` int(255) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`occupationCommunityId`),
  UNIQUE KEY `userId` (`userId`),
  KEY `FK_User_OccupationCommunity_occupationcommunity` (`occupationCommunityId`),
  CONSTRAINT `FK_User_OccupationCommunity_occupationcommunity` FOREIGN KEY (`occupationCommunityId`) REFERENCES `occupationcommunity` (`id`),
  CONSTRAINT `FK_User_OccupationCommunity_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.user_occupationcommunity: 0 rows
DELETE FROM `user_occupationcommunity`;
/*!40000 ALTER TABLE `user_occupationcommunity` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_occupationcommunity` ENABLE KEYS */;


# Dumping structure for table recsys-nju.user_ratingcommunity
DROP TABLE IF EXISTS `user_ratingcommunity`;
CREATE TABLE IF NOT EXISTS `user_ratingcommunity` (
  `userId` int(255) unsigned NOT NULL,
  `ratingCommunityId` int(255) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`ratingCommunityId`),
  UNIQUE KEY `userId` (`userId`),
  KEY `FK_user_ratingcommunity_ratingcommunity` (`ratingCommunityId`),
  CONSTRAINT `FK_user_ratingcommunity_ratingcommunity` FOREIGN KEY (`ratingCommunityId`) REFERENCES `ratingcommunity` (`id`),
  CONSTRAINT `FK_user_ratingcommunity_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dumping data for table recsys-nju.user_ratingcommunity: 0 rows
DELETE FROM `user_ratingcommunity`;
/*!40000 ALTER TABLE `user_ratingcommunity` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_ratingcommunity` ENABLE KEYS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
