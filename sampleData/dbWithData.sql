-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 09, 2016 at 11:48 PM
-- Server version: 10.1.12-MariaDB-1~trusty
-- PHP Version: 5.6.18-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `amrit`
--

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_agent`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_agent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `emailId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` bigint(20) unsigned DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commissionPercentage` int(10) unsigned NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  `instituteId` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inst_agnt_mbl_unq` (`instituteId`,`mobile`),
  UNIQUE KEY `inst_agnt_email_unq` (`instituteId`,`emailId`),
  KEY `IDX_B8022B1108E0BBB` (`instituteId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `itfi_finmgmt_agent`
--

INSERT INTO `itfi_finmgmt_agent` (`id`, `name`, `emailId`, `mobile`, `address`, `commissionPercentage`, `enabled`, `updatedOn`, `createdOn`, `instituteId`) VALUES
(1, 'Imp Agent', 'info@afent.com', 1243569870, 'Test Imp Agent Address', 25, 1, 1457333911, 1457333911, 1),
(2, 'Agent Two', 'agenttwo@info.com', 7236514890, 'FGG AGent two', 45, 1, 1457341960, 1457341960, 2),
(3, 'Agent Two', 'agenttwo@info.com', 7236514811, 'ABS', 20, 1, 1457365151, 1457365151, 1);

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_agent_payment`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_agent_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `emailId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `totalCommission` bigint(20) unsigned NOT NULL,
  `paidAmmount` bigint(20) unsigned NOT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `itfi_finmgmt_agent_payment`
--

INSERT INTO `itfi_finmgmt_agent_payment` (`id`, `emailId`, `totalCommission`, `paidAmmount`, `updatedOn`, `createdOn`) VALUES
(10, 'agenttwo@info.com', 40610, 500, 1457460336, 1457460336),
(12, 'agenttwo@info.com', 40610, 4063, 1457460548, 1457460548),
(13, 'info@afent.com', 16250, 1650, 1457462250, 1457462250),
(14, 'info@afent.com', 16250, 14600, 1457462275, 1457462275);

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_institute`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_institute` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emailId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emailIdTwo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phoneNumber` bigint(20) unsigned DEFAULT NULL,
  `phoneNumberTwo` bigint(20) unsigned DEFAULT NULL,
  `phoneNumberThree` bigint(20) unsigned DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pincode` bigint(20) unsigned DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `itfi_finmgmt_institute`
--

INSERT INTO `itfi_finmgmt_institute` (`id`, `name`, `emailId`, `emailIdTwo`, `phoneNumber`, `phoneNumberTwo`, `phoneNumberThree`, `country`, `pincode`, `enabled`, `updatedOn`, `createdOn`) VALUES
(1, 'Imperial', 'info@imperial.edu', NULL, 2356897410, NULL, NULL, 'AU', 124563, 1, 1457193224, 1457193224),
(2, 'Test Institute', 'info@test.com', NULL, 7894521036, NULL, NULL, 'AU', 142635, 1, 1457329725, 1457329725);

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_institute_fee_structure`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_institute_fee_structure` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  `instituteId` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inst_cmp_name_unq` (`instituteId`,`name`),
  KEY `IDX_A38E49C108E0BBB` (`instituteId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `itfi_finmgmt_institute_fee_structure`
--

INSERT INTO `itfi_finmgmt_institute_fee_structure` (`id`, `name`, `amount`, `enabled`, `updatedOn`, `createdOn`, `instituteId`) VALUES
(1, 'Infra', 202, 0, 1457547168, 1457258972, 1),
(4, 'Building Fund', 256, 1, 1457547168, 1457329508, 1),
(5, 'Medical_temp', 8956, 1, 1457547168, 1457329508, 1),
(6, 'Insurance-ty', 789, 1, 1457547168, 1457329677, 1),
(7, 'medical_rt', 895, 1, 1457547168, 1457329677, 1),
(10, 'Medical Insurance', 520, 0, 1457547512, 1457343354, 2),
(11, 'Vehucular Warfare', 5222, 1, 1457547512, 1457343354, 2),
(12, 'Cmp2', 234, 0, 1457547168, 1457544429, 1),
(13, 'Test-one', 1245, 1, 1457547512, 1457547480, 2),
(14, 'Test-Seven', 123, 1, 1457547512, 1457547499, 2);

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_programme`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_programme` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `abbreviation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `feeAmount` bigint(20) unsigned NOT NULL,
  `feeCurrency` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  `instituteId` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inst_prog_unq` (`instituteId`,`name`),
  UNIQUE KEY `inst_proga_unq` (`instituteId`,`abbreviation`),
  KEY `IDX_BED3FFDD108E0BBB` (`instituteId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `itfi_finmgmt_programme`
--

INSERT INTO `itfi_finmgmt_programme` (`id`, `name`, `abbreviation`, `feeAmount`, `feeCurrency`, `enabled`, `updatedOn`, `createdOn`, `instituteId`) VALUES
(1, 'Information Technology', NULL, 65000, 'AUD', 1, 1457333635, 1457333635, 1),
(2, 'Test InstiProg', NULL, 45123, 'AUD', 1, 1457341987, 1457341987, 2);

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_student`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_student` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `emailId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dateOfBirth` bigint(20) unsigned DEFAULT NULL,
  `mobile` bigint(20) unsigned DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `feeAmount` int(10) unsigned DEFAULT NULL,
  `feeCurrency` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commissionToBePaidByInstitute` bigint(20) unsigned DEFAULT NULL,
  `commissionStatus` tinyint(1) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  `programmeId` int(10) unsigned DEFAULT NULL,
  `agentId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inst_stu_unq` (`programmeId`,`id`),
  UNIQUE KEY `inst_stu_email_unq` (`programmeId`,`emailId`),
  KEY `IDX_A2CB771DB248874` (`programmeId`),
  KEY `IDX_A2CB771D17EB4E41` (`agentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `itfi_finmgmt_student`
--

INSERT INTO `itfi_finmgmt_student` (`id`, `name`, `emailId`, `dateOfBirth`, `mobile`, `address`, `gender`, `feeAmount`, `feeCurrency`, `commissionToBePaidByInstitute`, `commissionStatus`, `enabled`, `updatedOn`, `createdOn`, `programmeId`, `agentId`) VALUES
(1, 'Test Student', 'teststu@gmail.com', 731269800, 7213604598, 'Test Student', 'Male', 33842, 'AUD', 20305, 0, NULL, 1457355137, 1457350671, 2, 2),
(2, 'Student Three', 'stthree@gmail.com', 718569000, 7102365489, 'STu three ad', 'Male', 45000, 'AUD', 20305, 0, NULL, 1457365031, 1457353406, 2, 2),
(3, 'Student Teb', 'teb@gmail.com', 740169000, 1023654789, 'Student teb adress', 'Male', 60000, 'AUD', 16250, 1, NULL, 1457356263, 1457355226, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_student_fee_breakdown`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_student_fee_breakdown` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `studentId` bigint(20) NOT NULL,
  `componentId` bigint(20) NOT NULL,
  `amount` int(11) NOT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inst_stu_cmp_unq` (`studentId`,`componentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Dumping data for table `itfi_finmgmt_student_fee_breakdown`
--

INSERT INTO `itfi_finmgmt_student_fee_breakdown` (`id`, `studentId`, `componentId`, `amount`, `updatedOn`, `createdOn`) VALUES
(1, 1, 10, 458, 1457355137, 1457355117),
(2, 1, 11, 500, 1457355137, 1457355117),
(3, 3, 1, 600, 1457356263, 1457355226),
(4, 3, 4, 60, 1457356263, 1457355226),
(5, 3, 5, 600, 1457356263, 1457355226),
(6, 3, 6, 500, 1457356263, 1457355226),
(8, 3, 7, 56, 1457356263, 1457356263),
(9, 2, 10, 522, 1457365031, 1457365031),
(10, 2, 11, 5222, 1457365031, 1457365031);

-- --------------------------------------------------------

--
-- Table structure for table `itfi_finmgmt_user`
--

CREATE TABLE IF NOT EXISTS `itfi_finmgmt_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fullName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `loginId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `accessLevel` int(11) NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `personalEmailId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dateOfBirth` bigint(20) unsigned DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` bigint(20) unsigned DEFAULT NULL,
  `marritalStatus` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pincode` bigint(20) unsigned DEFAULT NULL,
  `permanentAddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correspondenceAddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imageId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `basicBioComplete` tinyint(1) NOT NULL,
  `accountVerified` tinyint(1) DEFAULT NULL,
  `emailVerified` tinyint(1) DEFAULT NULL,
  `updatedOn` bigint(20) unsigned NOT NULL,
  `createdOn` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_84C277B66CC9E093` (`loginId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `itfi_finmgmt_user`
--

INSERT INTO `itfi_finmgmt_user` (`id`, `fullName`, `loginId`, `accessLevel`, `password`, `personalEmailId`, `dateOfBirth`, `gender`, `mobile`, `marritalStatus`, `nationality`, `state`, `city`, `district`, `pincode`, `permanentAddress`, `correspondenceAddress`, `imageId`, `basicBioComplete`, `accountVerified`, `emailVerified`, `updatedOn`, `createdOn`) VALUES
(1, 'Amrit Singh', 'amritsingh183@gmail.com', 7, '$2y$13$0fK8sBjyEBu2GdHCnr5K8uYXjvxh5mONMoLTHyV4gYvSm6uy0r56m', 'amritsingh183@gmail.com', 642191400, 'Male', 8053283283, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1457192888, 1457192887);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `itfi_finmgmt_agent`
--
ALTER TABLE `itfi_finmgmt_agent`
  ADD CONSTRAINT `FK_B8022B1108E0BBB` FOREIGN KEY (`instituteId`) REFERENCES `itfi_finmgmt_institute` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itfi_finmgmt_institute_fee_structure`
--
ALTER TABLE `itfi_finmgmt_institute_fee_structure`
  ADD CONSTRAINT `FK_A38E49C108E0BBB` FOREIGN KEY (`instituteId`) REFERENCES `itfi_finmgmt_institute` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itfi_finmgmt_programme`
--
ALTER TABLE `itfi_finmgmt_programme`
  ADD CONSTRAINT `FK_BED3FFDD108E0BBB` FOREIGN KEY (`instituteId`) REFERENCES `itfi_finmgmt_institute` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itfi_finmgmt_student`
--
ALTER TABLE `itfi_finmgmt_student`
  ADD CONSTRAINT `FK_A2CB771D17EB4E41` FOREIGN KEY (`agentId`) REFERENCES `itfi_finmgmt_agent` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_A2CB771DB248874` FOREIGN KEY (`programmeId`) REFERENCES `itfi_finmgmt_programme` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
