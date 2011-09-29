-- MySQL dump 10.13  Distrib 5.1.41, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: T3wheat
-- ------------------------------------------------------
-- Server version	5.1.41-3ubuntu12.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `barley_pedigree_catalog`
--

DROP TABLE IF EXISTS `barley_pedigree_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barley_pedigree_catalog` (
  `barley_pedigree_catalog_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `barley_pedigree_catalog_name` varchar(255) NOT NULL,
  `link_out` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`barley_pedigree_catalog_uid`),
  UNIQUE KEY `barley_pedigree_catalog_index_name` (`barley_pedigree_catalog_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Links to GRIN, Eucarpia, and other pedigree data sources';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barley_pedigree_catalog`
--

LOCK TABLES `barley_pedigree_catalog` WRITE;
/*!40000 ALTER TABLE `barley_pedigree_catalog` DISABLE KEYS */;
INSERT INTO `barley_pedigree_catalog` VALUES (2,'GRIN','http://www.ars-grin.gov/cgi-bin/npgs/acc/search.pl?accid=XXXX','The Germplasm Resources Information Network (GRIN) web server provides germplasm information about plants, animals, microbes and invertebrates. This program is within the U.S. Department of Agriculture\'s Agricultural Research Service. ','2009-07-27 20:38:31','0000-00-00 00:00:00'),(3,'Crop Science Identifier','http://www.ars-grin.gov/cgi-bin/npgs/acc/search.pl?accid=XXXX%3A%3Ahordeum','Crop Science Society of America','2009-07-27 20:42:40','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `barley_pedigree_catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `experiment_types`
--

DROP TABLE IF EXISTS `experiment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `experiment_types` (
  `experiment_type_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `experiment_type_name` enum('genotype','phenotype') NOT NULL,
  `experiment_subtype` varchar(45) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`experiment_type_uid`),
  UNIQUE KEY `experiment_types_index_name` (`experiment_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Genotypic or phenotypic experiment.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `experiment_types`
--

LOCK TABLES `experiment_types` WRITE;
/*!40000 ALTER TABLE `experiment_types` DISABLE KEYS */;
INSERT INTO `experiment_types` VALUES (1,'phenotype',NULL,NULL,'2008-07-29 20:15:36','2008-07-29 20:15:36'),(2,'genotype',NULL,NULL,'2008-07-29 20:16:42','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `experiment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `institutions`
--

DROP TABLE IF EXISTS `institutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutions` (
  `institutions_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institutions_name` varchar(255) NOT NULL,
  `institute_acronym` char(20) NOT NULL,
  `email_domain` varchar(100) DEFAULT NULL COMMENT 'domain of email at the institution',
  `institute_address` text NOT NULL,
  `institute_state` varchar(45) DEFAULT NULL,
  `institute_country` varchar(105) NOT NULL DEFAULT 'United States',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`institutions_uid`),
  UNIQUE KEY `institutions_name` (`institutions_name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COMMENT='List of all institutions involved with THT project. ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `institutions`
--

LOCK TABLES `institutions` WRITE;
/*!40000 ALTER TABLE `institutions` DISABLE KEYS */;
INSERT INTO `institutions` VALUES (1,'Iowa State University','ISU','iastate.edu','','Iowa','USA','2008-07-30 19:26:54','0000-00-00 00:00:00'),(2,'University of California','UCDavis','ucdavis.edu','','California','USA','2008-07-30 19:29:06','0000-00-00 00:00:00'),(3,'Busch Agricultural Resources Inc.','BARI',NULL,'','Colorado','USA','2008-07-30 19:30:11','0000-00-00 00:00:00'),(4,'USDA/ARS-Aberdeen, ID','U of Idaho','uidaho.edu','','Idaho','USA','2008-07-30 19:30:38','0000-00-00 00:00:00'),(5,'University of Minnesota','UMN','umn.edu','','Minneosota','USA','2008-07-30 19:31:04','0000-00-00 00:00:00'),(6,'Montana State University','MSU','montana.edu','','Montana','USA','2008-07-30 19:31:24','0000-00-00 00:00:00'),(7,'Oregon State University','OSU','osu.edu','','Oregon','USA','2008-07-30 19:31:41','0000-00-00 00:00:00'),(8,'North Dakota State University','NDSU','ndsu.edu','','North Dakota','USA','2008-07-30 19:32:02','0000-00-00 00:00:00'),(9,'Utah State University','Utah State','usu.edu','','Utah','USA','2008-07-30 19:32:21','0000-00-00 00:00:00'),(10,'Virginia Tech','Virginia Tech','vt.edu','','Virginia','USA','2008-07-30 19:32:35','0000-00-00 00:00:00'),(11,'Washington State University','WSU','wsu.edu','','Washington','USA','2008-07-30 19:32:55','0000-00-00 00:00:00'),(12,'USDA-ARS','USDA, WI',NULL,'','Wisconsin','USA','2008-07-30 19:33:28','0000-00-00 00:00:00'),(13,'USDA-Agricultural Research','USDA_ARS',NULL,'','North Dakota','USA','2008-07-30 19:34:34','0000-00-00 00:00:00'),(14,'University of California, Riverside','UCR','ucr.edu','','California','USA','2008-09-19 15:17:20','0000-00-00 00:00:00'),(15,'Test','Test',NULL,'','Test','USA','2008-11-11 07:21:28','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `institutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marker_annotation_types`
--

DROP TABLE IF EXISTS `marker_annotation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_annotation_types` (
  `marker_annotation_type_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_annotation` varchar(45) CHARACTER SET utf8 NOT NULL,
  `comments` text NOT NULL,
  `linkout_string_for_annotation` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`marker_annotation_type_uid`),
  UNIQUE KEY `annot_source` (`name_annotation`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COMMENT='Types of annotations and links,e.g., HarvEST assembly 32, Pr';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marker_annotation_types`
--

LOCK TABLES `marker_annotation_types` WRITE;
/*!40000 ALTER TABLE `marker_annotation_types` DISABLE KEYS */;
INSERT INTO `marker_annotation_types` VALUES (1,'HarvEST Unigenes 32','Close Lab: HarvEST Assembly 32 Unigene Number','http://harvest-web.org/viewsetno.wc?assembly=32&unigeneid=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(2,'HarvEST Unigenes 35','Close Lab: HarvEST Assembly 35 Unigene number','http://harvest-web.org/viewsetno.wc?assembly=35&unigeneid=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(3,'PLEXdb U32 Probe Set','Close Lab, HarvEST Assembly 32; at least 5 mateches','http://www.plexdb.org/modules/PD_probeset/annotation.php?genechip=Barley1&exemplar=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(4,'Gramene U32 rice locus','Close Lab, using Barley Assembly 32','http://gramene.org/db/markers/marker_view?marker_name=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(5,'Gramene U32 rice annotation','Close Lab, using HarvEST Barley Assembly 32',NULL,'2009-01-19 05:49:57','2009-01-19 05:49:57'),(6,'PLEXdb U35 Probe Set','Close Lab, HarvEST Assembly 35, at least 5 matches','http://www.plexdb.org/modules/PD_probeset/annotation.php?genechip=Barley1&exemplar=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(7,'Gramene U35 rice locus','Close lab using Barley Assembly 35','http://gramene.org/db/markers/marker_view?marker_name=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(8,'Gramene U35 rice annotation','Close Lab, using HarvEST Barley Assembly 35',NULL,'2009-01-19 05:49:57','2009-01-19 05:49:57'),(9,'GrainGenes','','http://wheat.pw.usda.gov/cgi-bin/graingenes/report.cgi?class=marker&name=XXXX','2010-05-03 23:58:05','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `marker_annotation_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marker_synonym_types`
--

DROP TABLE IF EXISTS `marker_synonym_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_synonym_types` (
  `marker_synonym_type_uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`marker_synonym_type_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COMMENT='This table lists the types of marker names and links. Exampl';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marker_synonym_types`
--

LOCK TABLES `marker_synonym_types` WRITE;
/*!40000 ALTER TABLE `marker_synonym_types` DISABLE KEYS */;
INSERT INTO `marker_synonym_types` VALUES (1,'EST Name','Original Lab  EST Name'),(2,'POPA Name','Name from POPA array'),(3,'BOPA Combined Name','New name showing tracking between POPAs and BOPAs'),(4,'BOPA Name','Original BOPA name'),(5,'DArT Name','DArT marker'),(6,'Historical Name','Historical SNP Name from Grain Genes');
/*!40000 ALTER TABLE `marker_synonym_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marker_types`
--

DROP TABLE IF EXISTS `marker_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_types` (
  `marker_type_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `marker_type_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`marker_type_uid`),
  UNIQUE KEY `marker_types_uidx` (`marker_type_name`),
  KEY `marker_types_index_name` (`marker_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='Denotes the types of markers. Currently all markers come fro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marker_types`
--

LOCK TABLES `marker_types` WRITE;
/*!40000 ALTER TABLE `marker_types` DISABLE KEYS */;
INSERT INTO `marker_types` VALUES (1,'OPA SNP Name','BarleyCAP marker','2008-08-11 16:59:02','2008-08-11 16:59:02'),(2,'DArT Marker','DArT Hayes Lab','2009-01-18 15:18:43','0000-00-00 00:00:00'),(3,'Historical','GrainGenes','2009-01-18 15:19:23','0000-00-00 00:00:00'),(4,'QTL','GrainGenes','2009-01-18 16:10:21','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `marker_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phenotype_category`
--

DROP TABLE IF EXISTS `phenotype_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phenotype_category` (
  `phenotype_category_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phenotype_category_name` varchar(255) NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`phenotype_category_uid`),
  UNIQUE KEY `phenotype_category_name` (`phenotype_category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COMMENT='Phenotypes are computed across different categories such as ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phenotype_category`
--

LOCK TABLES `phenotype_category` WRITE;
/*!40000 ALTER TABLE `phenotype_category` DISABLE KEYS */;
INSERT INTO `phenotype_category` VALUES (1,'Agronomic Traits','2008-07-30 19:23:50','0000-00-00 00:00:00'),(2,'Malting Quality','2008-07-30 19:23:56','0000-00-00 00:00:00'),(3,'Diseases','2008-07-30 19:24:01','0000-00-00 00:00:00'),(4,'Morphological traits','2008-07-30 19:24:06','0000-00-00 00:00:00'),(5,'Dry matter partition','2008-07-30 19:24:10','0000-00-00 00:00:00'),(6,'Drought Responses','2008-07-30 19:24:15','0000-00-00 00:00:00'),(7,'Winter growth habit','2008-07-30 19:24:23','0000-00-00 00:00:00'),(8,'Quality for food and other uses','2008-07-30 19:24:29','0000-00-00 00:00:00'),(9,'Misc','2008-11-24 16:01:53','0000-00-00 00:00:00'),(10,'agronomic trait','2011-07-05 16:50:18','2011-07-05 16:50:18'),(11,'morphological trait','2011-07-05 16:50:18','2011-07-05 16:50:18');
/*!40000 ALTER TABLE `phenotype_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `name` varchar(25) NOT NULL,
  `value` varchar(100) NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Settings for authentications';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('smtpserver','graingenes.org:25'),('mailfrom','noreply@graingenes.org'),('encryptionkey','AJrm0Tc9N3qmLpTWMoeVguzOU'),('capmail','tht_curator@graingenes.org'),('feedbackmail','tht_curator@graingenes.org'),('capencryptionkey','P7aYaR4AlJD7GREqZoFzBB72b'),('passresetkey','n0IIF6oeH9ab7Ik30uyFiQaz9');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `unit_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(255) NOT NULL,
  `unit_abbreviation` varchar(255) DEFAULT NULL,
  `unit_description` varchar(255) DEFAULT NULL,
  `sigdigits_display` tinyint(3) DEFAULT NULL COMMENT 'Gives significant digit for table display',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`unit_uid`),
  UNIQUE KEY `units_name` (`unit_name`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'kg ha -1',NULL,NULL,2,'2008-07-30 19:20:21','0000-00-00 00:00:00'),(2,'centimeter','cm',NULL,1,'2008-07-30 19:20:27','0000-00-00 00:00:00'),(3,'days from planting',NULL,NULL,1,'2008-07-30 19:20:56','0000-00-00 00:00:00'),(4,'percent','%',NULL,1,'2008-07-30 19:21:20','0000-00-00 00:00:00'),(5,'% by wt on 6/64â€ sieve ',NULL,NULL,1,'2008-07-30 19:21:30','0000-00-00 00:00:00'),(6,'g liter -1',NULL,NULL,2,'2008-07-30 19:21:37','0000-00-00 00:00:00'),(7,'milligram','mg',NULL,2,'2008-07-30 19:21:43','0000-00-00 00:00:00'),(8,'Agtron',NULL,NULL,2,'2008-07-30 19:21:48','0000-00-00 00:00:00'),(9,'Disease rating 0-4 with qualifiers','0-4','string',-1,'2008-07-30 19:21:54','0000-00-00 00:00:00'),(10,'&deg;ASBC',NULL,NULL,1,'2008-07-30 19:22:00','0000-00-00 00:00:00'),(11,'20&deg;DU',NULL,NULL,2,'2008-07-30 19:22:05','0000-00-00 00:00:00'),(12,'parts per million','ppm',NULL,2,'2008-07-30 19:22:11','0000-00-00 00:00:00'),(13,'ASBC',NULL,NULL,2,'2008-07-30 19:22:17','0000-00-00 00:00:00'),(14,'days',NULL,NULL,1,'2008-07-30 19:22:22','0000-00-00 00:00:00'),(15,'% survival',NULL,NULL,0,'2008-07-30 19:22:46','0000-00-00 00:00:00'),(16,'kernels/spike',NULL,NULL,0,'2009-02-02 04:04:36','0000-00-00 00:00:00'),(17,'Disease Rating 0-9','0-9','numerical rating',0,'2009-04-17 21:45:16','0000-00-00 00:00:00'),(18,'millimeters','mm',NULL,1,'2009-05-21 18:41:33','0000-00-00 00:00:00'),(19,'Disease Rating 0-8','0-8',NULL,0,'2009-08-16 21:50:10','2009-08-16 05:00:00'),(20,'Infection response rating 0-5','0-5',NULL,0,'2009-08-16 21:55:25','2009-08-16 05:00:00'),(21,'Infection response rating 1-9','1-9',NULL,1,'2009-08-16 21:56:22','2009-08-16 05:00:00'),(22,'U/kg malt',NULL,NULL,0,'2009-09-13 02:02:02','0000-00-00 00:00:00'),(23,'Tekauz scale (1-10)',NULL,'Tekauz scale (1-10) where 1-3 is resistant, 4-5 is moderately resistant (MR), 6-7 is moderately susceptible (MS), and 8-10 is susceptible (S)',0,'2009-09-13 15:21:48','2009-09-13 05:00:00'),(24,'septoria seedling infection response (0-5)','ssir (0-5)','A 0-5 rating scale was developed by H. Toubia-Rahme was used for barley infected by S. passerinii.  This scale is based on one developed for wheat by Rosielle 1972 (Euphytica 21:152-161), where 0=immune: no visible symptoms; 1=highly resistant: presence o',2,'2009-09-13 16:22:42','2009-09-13 05:00:00'),(25,'spot blotch seedling infection response (1-9)','SBSIR (1-9)','1-9 rating scale where 1-4 is indicative of a low IP, 5 of intermediate IP, and 6-9 of a high IP.',1,'2009-09-13 16:27:50','2009-09-13 05:00:00'),(26,'Head drop rating scale',NULL,'head drop rating scale (0-9)',1,'2009-09-13 17:58:34','2009-09-13 05:00:00'),(27,'single kernel characterization system','SKCS','single kernel characterization system',1,'2009-09-15 22:27:59','0000-00-00 00:00:00'),(28,'% dry weight basis',NULL,NULL,3,'2009-09-15 22:28:34','0000-00-00 00:00:00'),(29,'absorbance, dry weight basis',NULL,NULL,2,'2009-09-15 22:29:43','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_types`
--

DROP TABLE IF EXISTS `user_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_types` (
  `user_types_uid` int(8) NOT NULL AUTO_INCREMENT,
  `user_types_name` enum('public','CAPprivate','CAPcurator','CAPadministrator') NOT NULL DEFAULT 'public',
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`user_types_uid`),
  UNIQUE KEY `user_types_index_name` (`user_types_name`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=latin1 COMMENT='This table defines the type of the user in the THT database.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_types`
--

LOCK TABLES `user_types` WRITE;
/*!40000 ALTER TABLE `user_types` DISABLE KEYS */;
INSERT INTO `user_types` VALUES (100,'CAPadministrator','Full privileges to update/change the database','2007-06-28 18:13:02','2007-06-28 17:34:30'),(101,'CAPcurator','Curates and uploads data to THT','2007-08-31 20:43:25','0000-00-00 00:00:00'),(102,'CAPprivate','Part of the BarleyCAP program','2008-09-08 06:10:41','0000-00-00 00:00:00'),(103,'public','Not part of BarleyCAP consortium','2008-09-08 06:10:57','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `user_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `users_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_types_uid` int(8) NOT NULL,
  `institution` varchar(100) DEFAULT NULL COMMENT 'name of the institution',
  `users_name` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(45) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'whether user''s email was confirmed',
  `lastaccess` datetime DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`users_uid`),
  UNIQUE KEY `users_name` (`users_name`),
  KEY `user_types_uid` (`user_types_uid`),
  KEY `uid_index` (`users_uid`),
  CONSTRAINT `fk_user_types` FOREIGN KEY (`user_types_uid`) REFERENCES `user_types` (`user_types_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=latin1 COMMENT='Signed in users for the THT database.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (113,102,NULL,'power134@umn.edu','634bfb980ceb63b57b8af06fb126aa14','Carol Powers','power134@umn.edu',1,'2008-06-16 13:12:50','2009-03-09 04:02:00','0000-00-00 00:00:00'),(114,100,NULL,'patrick.m.hayes@oregonstate.edu','861ed95174b73bc7cbc79f3b206f0336','Pat Hayes','patrick.m.hayes@oregonstate.edu',1,'2008-02-27 09:36:23','2011-02-09 04:18:45','0000-00-00 00:00:00'),(115,103,NULL,'jsv@life.ku.dk','9df698def3aa471a94dba8eb33ea1e5e','Jan Svensson','jsv@life.ku.dk',1,'2008-05-14 08:17:30','2009-03-09 04:02:00','0000-00-00 00:00:00'),(116,103,NULL,'matthew.hayden@adelaide.edu.au','d4e92d18c546b05f6e0c2fe8755c1a17','Matt Hayden','matthew.hayden@adelaide.edu.au',1,'2008-01-08 05:12:47','2009-03-09 04:02:00','0000-00-00 00:00:00'),(117,102,NULL,'david.hole@usu.edu','37ad38230951ef8eec5a22b89eb95f21','David Hole','david.hole@usu.edu',1,'2010-01-29 17:33:31','2010-01-29 23:33:31','0000-00-00 00:00:00'),(118,102,NULL,'timothy.close@ucr.edu','e5d4daaaf0ba06a50449d1e357c5944c','Timothy Close','timothy.close@ucr.edu',1,'0000-00-00 00:00:00','2009-03-09 04:02:00','0000-00-00 00:00:00'),(119,102,NULL,'bruce.westlund@anheuser-busch.com','8338bee615886117ae86f3fc177b2878','Bruce Westlund','bruce.westlund@anheuser-busch.com',1,'2010-04-16 10:10:42','2010-04-16 17:10:42','0000-00-00 00:00:00'),(120,102,NULL,'fabio.pedraza-garcia@ndsu.edu','f6120609c0a3e6e0e7b8626079134fd6','Fabio','fabio.pedraza-garcia@ndsu.edu',1,'2008-08-21 13:57:34','2009-03-09 04:02:00','0000-00-00 00:00:00'),(121,102,NULL,'sanjaya.gyawali@ndsu.edu','ff076cbf48c0a081821dc5220d906b50','Sanjaya Gyawali','sanjaya.gyawali@ndsu.edu',1,'2008-07-18 10:37:10','2009-03-09 04:02:00','0000-00-00 00:00:00'),(123,102,NULL,'blake.cooper@anheuser-busch.com','b1430cf0cd75b772b4b0dd78c7e898bd','Blake Cooper','blake.cooper@anheuser-busch.com',1,'2010-01-08 17:58:47','2010-01-08 23:58:47','0000-00-00 00:00:00'),(124,101,NULL,'muehl003@umn.edu','e9be5a98a3ef7e9de0893ef3f88212df','Gary Muehlbauer','muehl003@umn.edu',1,'2008-02-27 09:32:25','2010-11-08 15:27:08','0000-00-00 00:00:00'),(125,100,NULL,'smith376@umn.edu','0f22ebb6491e0c231b99d75884145c6d','Kevin P. Smith','smith376@umn.edu',1,'2010-11-24 08:11:45','2011-02-09 04:18:17','0000-00-00 00:00:00'),(126,103,NULL,'David.Marshall@scri.ac.uk','87f7f23ef6a274e351bbcb9af8182142','David Marshall','David.Marshall@scri.ac.uk',1,'2008-02-27 09:37:45','2009-03-09 04:02:00','0000-00-00 00:00:00'),(127,102,NULL,'bates@montana.edu','ea81d20045d41481a27d9cc5a439d788','Stan Bates','bates@montana.edu',1,'2008-03-06 14:26:33','2009-03-09 04:02:00','0000-00-00 00:00:00'),(128,103,NULL,'sylviane.comparot@bbsrc.ac.uk','8621e77d22f0830a46b462e3f46ed917','Sylviane Comparot','sylviane.comparot@bbsrc.ac.uk',1,'2008-03-12 07:37:26','2009-03-09 04:02:00','0000-00-00 00:00:00'),(129,103,NULL,'rlh@mb.au.dk','b2a45223bd6e6461eb5ae435838194ab','Rasmus HjortshÃ¸j','rlh@mb.au.dk',1,'2008-04-03 02:25:04','2009-03-09 04:02:00','0000-00-00 00:00:00'),(130,100,NULL,'jeanluc.jannink@ars.usda.gov','9ea4044dd92002f7f9e8c8bb5961f737','Jean-Luc Jannink','jeanluc.jannink@ars.usda.gov',1,'2011-07-18 11:49:34','2011-07-18 18:49:34','0000-00-00 00:00:00'),(131,103,NULL,'paul.bury@syngenta.com','cd8dd8237558308123ed00d4ae33e707','Paul Bury','paul.bury@syngenta.com',1,'2008-04-17 11:08:21','2009-03-09 04:02:00','0000-00-00 00:00:00'),(132,100,NULL,'dem3@cornell.edu','bf779e0933a882808585d19455cd7937','Dave Matthews','dem3@cornell.edu',1,'2011-07-29 08:06:45','2011-07-29 15:06:45','0000-00-00 00:00:00'),(133,103,NULL,'cnboyd@wsu.edu','c9391a2b9fc8542a62d2b47948f3820c','Christine Boyd','cnboyd@wsu.edu',1,'2008-05-05 18:02:40','2009-03-09 04:02:00','0000-00-00 00:00:00'),(134,103,NULL,'clara@fagro.edu.uy','5ce7360ea6561167b2144b3d048bd79a','Clara Pritsch','clara@fagro.edu.uy',1,'2008-05-06 15:22:52','2009-03-09 04:02:00','0000-00-00 00:00:00'),(135,100,NULL,'julied@iastate.edu','ea3fc6f539482e0ba5faf070aa8b493b','julie dickerson','julied@iastate.edu',1,'2010-01-07 11:22:20','2010-01-07 17:22:20','0000-00-00 00:00:00'),(136,102,NULL,'shreymuk@iastate.edu','02ab506932245cd848642bc9b1d7fb63','Shreyartha Mukherjee','shreymuk@iastate.edu',1,'2008-06-11 09:28:30','2009-03-09 04:02:00','0000-00-00 00:00:00'),(137,103,NULL,'stockinger.4@osu.edu','0e5556de263806b083c97a5bff634a9c','Eric Stockinger','stockinger.4@osu.edu',1,'2008-05-24 10:14:30','2009-03-09 04:02:00','0000-00-00 00:00:00'),(138,103,NULL,'Arnis.Druka@scri.ac.uk','aacebd553fe07c1328c566e63de47ba9','Arnis Druka','Arnis.Druka@scri.ac.uk',1,'2008-06-01 01:22:01','2009-03-09 04:02:00','0000-00-00 00:00:00'),(139,100,NULL,'jsherman@montana.edu','2ecd29be4f3425c8d2e5917ba155e969','Jamie Sherman','jsherman@montana.edu',1,'2008-06-16 13:09:54','2011-02-09 04:17:42','0000-00-00 00:00:00'),(140,102,NULL,'alsop002@umn.edu','95e6c792909c16d16fc695e0d8b04230','Ben Alsop','alsop002@umn.edu',1,'2008-06-16 13:16:15','2009-03-09 04:02:00','0000-00-00 00:00:00'),(141,103,NULL,'chenggen.chu@ndsu.edu','a55d3468753aa357173726f31eb39951','Chenggen Chu','chenggen.chu@ndsu.edu',1,'2008-06-16 13:16:59','2009-03-09 04:02:00','0000-00-00 00:00:00'),(142,100,NULL,'pjb39@cornell.edu','35344dc8053a7c9d5b2eaccadd0b8f5c','Peter Bradbury','pjb39@cornell.edu',1,'2011-06-25 12:56:01','2011-06-25 19:56:01','0000-00-00 00:00:00'),(143,103,NULL,'pietsch@ipk-gatersleben.de','2a08b08778b22376acfa94d7e6f7a58c','Christof Pietsch','pietsch@ipk-gatersleben.de',1,'2008-07-11 02:58:35','2009-03-09 04:02:00','0000-00-00 00:00:00'),(144,103,NULL,'veerle.vandamme@gmail.com','55c691e10068bd5a28ab47a015da2a9e','Veerle Van Damme','veerle.vandamme@gmail.com',1,'0000-00-00 00:00:00','2009-03-09 04:02:00','0000-00-00 00:00:00'),(146,103,NULL,'thirstymerk@hotmail.com','a7af5e7a19ad81b57c5c3a83b73a7608','Chris','thirstymerk@hotmail.com',1,'2008-07-29 05:07:12','2009-03-09 04:02:00','0000-00-00 00:00:00'),(147,102,NULL,'alfonso.cuesta-marcos@oregonstate.edu','adcc03d49b6bde21597379d2c1b78748','Alfonso Cuesta Marcos','alfonso.cuesta-marcos@oregonstate.edu',1,'2008-08-07 14:02:40','2009-03-09 04:02:00','0000-00-00 00:00:00'),(148,103,NULL,'Karen.Cichy@ars.usda.gov','67d9dd15076953d20a88098ead61c6d6','Karen Cichy','Karen.Cichy@ars.usda.gov',1,'2008-08-22 13:12:30','2009-03-09 04:02:00','0000-00-00 00:00:00'),(149,102,NULL,'ullrich@wsu.edu','fb2fcd534b0ff3bbed73cc51df620323','STEVE ULLRICH','ullrich@wsu.edu',1,'2008-08-28 12:17:53','2009-03-09 04:02:00','0000-00-00 00:00:00'),(150,103,NULL,'jdiaz@inia.org.uy','ed1dc7aeed72f5f9a0b93827617e27f2','Juan E. DÃ­az-Lago','jdiaz@inia.org.uy',1,'2008-09-04 14:23:46','2009-03-09 04:02:00','0000-00-00 00:00:00'),(151,103,'Vlad Sukhoy','vladimir.sukhoy@gmail.com','96e79218965eb72c92a549dd5a330112','Vlad Sukhoy','vladimir.sukhoy@gmail.com',1,'2009-03-09 16:31:45','2009-03-09 22:01:33','0000-00-00 00:00:00'),(152,100,'Iowa State University','sukhoy@iastate.edu','96e79218965eb72c92a549dd5a330112','Vlad Sukhoy','sukhoy@iastate.edu',1,'2009-08-17 18:28:54','2009-08-17 23:31:23','0000-00-00 00:00:00'),(153,103,'North Dakota State University','zhh.liu@ndsu.edu','a199c7918da924543d1df2c67552dc20','Zhaohui Liu','zhh.liu@ndsu.edu',1,'2009-04-08 11:15:49','2009-04-08 18:24:41','0000-00-00 00:00:00'),(154,102,'Pioneer Hi-Bred International','julie.ho@pioneer.com','2fb754638b492550f317729ce9d8f7d0','Julie Ho','julie.ho@pioneer.com',1,'2009-05-13 14:27:12','2009-05-13 19:27:12','0000-00-00 00:00:00'),(155,103,'Kansas State University','jcn@ksu.edu','4adec5d38f1edeca13cb29624666c4d0','Clare Nelson','jcn@ksu.edu',1,'2009-05-25 16:49:55','2009-05-25 21:59:51','0000-00-00 00:00:00'),(156,101,'Iowa State University','jillella@iastate.edu','42ce5617e7702cd471d01be3ebb6782e','suman reddy jillella','jillella@iastate.edu',1,'2011-01-02 18:55:13','2011-01-03 02:55:13','0000-00-00 00:00:00'),(157,101,'GrainGenes','davidhane@gmail.com','d8578edf8458ce06fbc5bb76a58c5ca4','David Hane','davidhane@gmail.com',1,'2010-06-28 10:16:14','2010-06-28 17:16:14','0000-00-00 00:00:00'),(158,100,'USDA','john.lee@ars.usda.gov','b12a3a6d70c7bf2d5b049fcd658c36f7','jpltanis','john.lee@ars.usda.gov',1,'2011-07-13 14:11:39','2011-07-13 21:11:39','0000-00-00 00:00:00'),(159,102,'University of Minnesota','wang1928@umn.edu','4844417b398786a0b6d2967fd8a812cb','Hongyun Wang','wang1928@umn.edu',1,'2009-12-21 17:06:21','2009-12-21 23:06:21','0000-00-00 00:00:00'),(160,102,'Montana State University','blake@montana.edu','d00ddaa1f59b882c34a151547d0d68c9','Tom Blake','blake@montana.edu',1,'2010-08-10 10:19:21','2010-08-10 17:19:21','0000-00-00 00:00:00'),(161,103,'Cornell','ajl289@cornell.edu','b9fa3b5120ec4d73145532ea13515e8b','Aaron Lorenz','ajl289@cornell.edu',1,'2009-10-25 16:28:17','2009-10-25 21:28:17','0000-00-00 00:00:00'),(163,101,'Oregon State University','jennifer.kling@oregonstate.edu','e299d7cb0f7866cce7d90da2af14047c','Jennifer Kling','jennifer.kling@oregonstate.edu',1,'2010-11-23 10:05:09','2010-11-23 18:05:09','0000-00-00 00:00:00'),(164,103,NULL,'julied515@gmail.com','f5d1278e8109edd94e1e4197e04873b9','julie','julied515@gmail.com',1,NULL,'2009-08-24 22:42:27','0000-00-00 00:00:00'),(165,102,'University of Minnesota','vikra003@umn.edu','e7fef452d66bc0df21e5274c591a10d0','Vikas Vikram','vikra003@umn.edu',1,'2010-12-21 11:00:18','2010-12-21 19:00:18','0000-00-00 00:00:00'),(166,103,'Limagrain','anne-marie.bochard@limagrain.com','4274342fb7aed0ae48b51b3a542c95b7','BOCHARD','anne-marie.bochard@limagrain.com',1,'2009-09-02 08:01:51','2009-09-02 13:01:51','0000-00-00 00:00:00'),(167,103,'Montana State University','vblake@montana.edu','a444ae8b4f11758c2257a775890d0ba1','Victoria Blake','vblake@montana.edu',1,'2010-03-29 11:18:19','2010-03-29 18:18:19','0000-00-00 00:00:00'),(168,102,'University of Minnesota','nava0080@umn.edu','2fcb3f7f7174de3bb2ab6b48ca7af4a1','Stephanie Navara','nava0080@umn.edu',1,'2010-08-13 12:16:49','2010-08-13 19:16:49','2009-09-11 05:00:00'),(169,103,NULL,'julied@mchsi.com','8ffacdb2b75195d944d3c9fb88fcb83a','test user, case','julied@mchsi.com',0,NULL,'2009-09-12 03:21:28','0000-00-00 00:00:00'),(170,103,'Chonbuk National University','sjyun@chonbuk.ac.kr','e1ee8175c76e4427e3fd96a715f465fd','Song J. Yun','sjyun@chonbuk.ac.kr',1,'2009-10-05 00:13:43','2009-10-05 05:13:43','0000-00-00 00:00:00'),(171,102,'North Dakota State University','richard.horsley@ndsu.edu','afb9dbbf5d77d5fe8a9c158a92ddae3f','Richard Horsley','richard.horsley@ndsu.edu',1,'2010-05-13 08:45:46','2010-05-13 15:45:46','0000-00-00 00:00:00'),(172,103,'LSU AgCenter','joard@agcenter.lsu.edu','5a18a38191ef44c9e3dd4dc88b6e1b47','JAMES OARD','joard@agcenter.lsu.edu',0,'2009-11-13 14:05:23','2009-11-13 20:05:23','0000-00-00 00:00:00'),(173,103,'AAFC','nick.tinker@agr.gc.ca','fdbcdc7f4897ec66d54283c543818aec','Nick Tinker','nick.tinker@agr.gc.ca',1,'2009-11-20 12:07:44','2009-11-20 18:07:44','0000-00-00 00:00:00'),(174,102,'The University of Adelaide','diane.mather@adelaide.edu.au','63b56aad75f8fed460e9d8b24e3850a4','Diane Mather','diane.mather@adelaide.edu.au',1,'2010-02-08 04:32:19','2010-02-08 10:32:19','0000-00-00 00:00:00'),(175,102,'USDA, Cereal Crops Research','mlwise@wisc.edu','426c4c1e524733ab066c22c155c6455d','Mitchell Wise','mlwise@wisc.edu',1,NULL,'2010-01-12 22:47:20','0000-00-00 00:00:00'),(176,100,'USDA-ARS, Cornell University','jap226@cornell.edu','63d70b24c58b7a5326cd58431095ac95','Jesse Poland','jap226@cornell.edu',1,'2010-01-20 10:25:26','2011-02-09 04:18:02','0000-00-00 00:00:00'),(177,103,NULL,'chanan.rubin@evogene.co.il','78d3c2c37daeaad81269108f32fbe033','Dr. Chanan Rubin','chanan.rubin@evogene.co.il',0,'2010-01-21 01:13:22','2010-01-21 07:13:22','0000-00-00 00:00:00'),(178,103,NULL,'chanan.rubin@gmail.com','78d3c2c37daeaad81269108f32fbe033','Chanan Rubin','chanan.rubin@gmail.com',1,NULL,'2010-01-21 07:15:10','0000-00-00 00:00:00'),(179,100,'Montana State / GrainGenes','vcarolloblake@gmail.com','a444ae8b4f11758c2257a775890d0ba1','Victoria Blake','vcarolloblake@gmail.com',1,'2011-05-31 17:40:21','2011-06-03 23:37:01','0000-00-00 00:00:00'),(180,103,'Facultad de AgronomÃ­a - Uruguay','luciag@fagro.edu.uy','65278687e9069f3fe95ee1206c1d774d','Lucia Gutierrez','luciag@fagro.edu.uy',1,'2010-03-30 09:21:01','2010-03-30 16:21:01','0000-00-00 00:00:00'),(181,103,'Alberta Agriculture and Rural Development','yadeta.kabeta@gov.ab.ca','6298ce52e3bb2c48f4e504b8d807dca6','Yadeta','yadeta.kabeta@gov.ab.ca',1,'2010-04-29 07:49:52','2010-04-29 14:49:52','0000-00-00 00:00:00'),(182,103,'Generation Challenge Program','d.gdeleon@cgiar.org','20846936d3ab70f127a31c890dacd85a','Diego Gonzalez-de-Leon','d.gdeleon@cgiar.org',0,'2010-05-16 17:46:49','2010-05-17 00:46:49','0000-00-00 00:00:00'),(183,103,'USDA-ARS-MWA-CCRU','jackson.moeller@ars.usda.gov','7c7b6a7963c3145dfdc7dc7d8ce22853','Jackson Moeller','jackson.moeller@ars.usda.gov',1,'2010-06-23 14:18:24','2010-06-23 21:32:32','0000-00-00 00:00:00'),(184,103,'IPK, Gatersleben','ariyadasa@ipk-gatersleben.de','9efab2399c7c560b34de477b9aa0a465','ruvini ariyadasa','ariyadasa@ipk-gatersleben.de',0,NULL,'2010-09-29 11:01:58','0000-00-00 00:00:00'),(185,103,NULL,'dlhtux@gmail.com','d8578edf8458ce06fbc5bb76a58c5ca4','Wayne Schlagle','dlhtux@gmail.com',0,NULL,'2010-10-27 20:42:24','0000-00-00 00:00:00'),(187,103,'USDA-ARS','jpltanis@netzero.net','37fb6b97e901d413763d8334426f09c8','Testing','jpltanis@netzero.net',1,'2011-06-24 09:24:44','2011-06-24 16:24:44','0000-00-00 00:00:00'),(188,102,NULL,'dematth@gmail.com','bf779e0933a882808585d19455cd7937','Dave, TCAP Participant','dematth@gmail.com',1,'2011-03-13 12:37:45','2011-03-13 19:37:45','0000-00-00 00:00:00'),(189,103,'none','matthews@wheat.pw.usda.gov','bf779e0933a882808585d19455cd7937','Dave, non-CAP-participant','matthews@wheat.pw.usda.gov',1,'2011-02-05 14:12:55','2011-02-05 22:12:55','0000-00-00 00:00:00'),(190,101,'Washington State University','peter.nyori@wsu.edu','9f4a9da13ba3806bb9992cdc8cbd40bd','Peter Bulli Nyori','peter.nyori@wsu.edu',1,'2011-03-24 11:38:55','2011-03-24 18:38:55','0000-00-00 00:00:00'),(191,101,'University of Idaho-Aberdeen, ID','jwheeler@uidaho.edu','d2562c0243dd4fe3df9cf9be0f71cca0','Justin Wheeler','jwheeler@uidaho.edu',1,'2011-03-31 13:55:03','2011-03-31 20:55:03','0000-00-00 00:00:00'),(192,103,'Washington State University','kgcamp@wsu.edu','825ee59798caf086ed2fe4edf89801ee','Kimberly Garland-Campbell','kgcamp@wsu.edu',1,NULL,'2011-03-24 22:25:05','0000-00-00 00:00:00'),(193,103,'USDA','claybirkett@gmail.com','0e6ea5245f60dbaa053b2ace2b2df6de','Clay Birkett','claybirkett@gmail.com',0,NULL,'2011-07-18 20:29:15','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-07-29  9:07:03
