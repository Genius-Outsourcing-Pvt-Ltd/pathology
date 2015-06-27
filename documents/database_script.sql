/*
SQLyog Ultimate v10.00 Beta1
MySQL - 5.6.16 : Database - pathology
*********************************************************************
*/ 
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`pathology` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `pathology`;

CREATE USER 'pathology'@'%'
IDENTIFIED BY PASSWORD 'pathology';

GRANT ALL PRIVILEGES ON pathology.* TO 'pathology'@'%';


/*Table structure for table `lab_sections` */

DROP TABLE IF EXISTS `lab_sections`;

CREATE TABLE `lab_sections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `name` varchar(100) CHARACTER SET latin1 DEFAULT NULL COMMENT 'section name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `lab_sections` */

insert  into `lab_sections`(`id`,`name`) values (1,'Haematology'),(2,'Clinical Pathology'),(3,'Bio Chemistry'),(4,'Clinical Chemistry');

/*Table structure for table `order_samples` */

DROP TABLE IF EXISTS `order_samples`;

CREATE TABLE `order_samples` (
  `order_id` int(10) unsigned NOT NULL,
  `sample_id` int(10) unsigned NOT NULL,
  `quantity` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`order_id`,`sample_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `order_samples` */

/*Table structure for table `order_tests` */

DROP TABLE IF EXISTS `order_tests`;

CREATE TABLE `order_tests` (
  `order_id` int(10) unsigned NOT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `results` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `result_calculated_at` datetime DEFAULT NULL COMMENT 'if null, result is not calculated yet',
  PRIMARY KEY (`order_id`,`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `order_tests` */

/*Table structure for table `patient_orders` */

DROP TABLE IF EXISTS `patient_orders`;

CREATE TABLE `patient_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `patient_id` int(10) unsigned DEFAULT NULL COMMENT 'foreign key patients',
  `created_at` datetime DEFAULT NULL COMMENT 'date time at which test is created',
  `total_tests` int(11) DEFAULT NULL COMMENT 'no. of tests ordered',
  `total_results_calculated` int(11) DEFAULT NULL COMMENT 'no. of results calculated out of total',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `patient_orders` */

/*Table structure for table `patients` */

DROP TABLE IF EXISTS `patients`;

CREATE TABLE `patients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) CHARACTER SET latin1 DEFAULT NULL COMMENT 'name of the patient',
  `age` decimal(10,0) DEFAULT NULL COMMENT 'age in years of the patient',
  `sex` enum('Male','Female','Unknown') CHARACTER SET latin1 DEFAULT NULL,
  `ref_by_doctor` varchar(100) CHARACTER SET latin1 DEFAULT NULL COMMENT 'name of the doctor who refered',
  `m_r_no` varchar(20) CHARACTER SET latin1 DEFAULT NULL COMMENT 'm.r.no. of the patient',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `patients` */

/*Table structure for table `samples` */

DROP TABLE IF EXISTS `samples`;

CREATE TABLE `samples` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `name` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `unit` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `samples` */

insert  into `samples`(`id`,`name`,`unit`) values (1,'Blood','CC'),(2,'Urine','CC');

/*Table structure for table `tests` */

DROP TABLE IF EXISTS `tests`;

CREATE TABLE `tests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `name` varchar(100) CHARACTER SET latin1 DEFAULT NULL COMMENT 'name of the test',
  `lab_section_id` int(10) unsigned DEFAULT NULL COMMENT 'lab section id where tests are performed',
  `units` varchar(20) CHARACTER SET latin1 DEFAULT NULL COMMENT 'units of the test',
  `reference_value` varchar(50) CHARACTER SET latin1 DEFAULT NULL COMMENT 'reference value of the test',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `tests` */

insert  into `tests`(`id`,`name`,`lab_section_id`,`units`,`reference_value`) values (1,'Haemoglobin',1,'g/dl','M: 14-18 F: 12-15'),(2,'T L C',1,'X10^9/L','4.0-11.0'),(3,'HBA1C',3,'%','Normal: 4.2 to 6.2'),(4,'Total Bilirubin',4,'mg / dl','0.2 -1.0'),(5,'ALT (S.G.P.T)',4,'U / L','M = 0 - 40, F = 0 - 31, Child = 0 - 40');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `password` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `user_type` enum('patient','staff') CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `users` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
