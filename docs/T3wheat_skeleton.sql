-- MySQL dump 10.13  Distrib 5.1.54, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: T3wheat
-- ------------------------------------------------------
-- Server version	5.1.54-1ubuntu4

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
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=latin1 COMMENT='List of all institutions involved with THT project. ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `institutions`
--

LOCK TABLES `institutions` WRITE;
/*!40000 ALTER TABLE `institutions` DISABLE KEYS */;
INSERT INTO `institutions` VALUES (1,'Iowa State University','ISU','iastate.edu','','Iowa','USA','2008-07-30 19:26:54','0000-00-00 00:00:00'),(2,'University of California','UCDavis','ucdavis.edu','','California','USA','2008-07-30 19:29:06','0000-00-00 00:00:00'),(3,'Busch Agricultural Resources Inc.','BARI',NULL,'','Colorado','USA','2008-07-30 19:30:11','0000-00-00 00:00:00'),(4,'USDA/ARS-Aberdeen, ID','U of Idaho','uidaho.edu','','Idaho','USA','2008-07-30 19:30:38','0000-00-00 00:00:00'),(5,'University of Minnesota','UMN','umn.edu','','Minneosota','USA','2008-07-30 19:31:04','0000-00-00 00:00:00'),(6,'Montana State University','MSU','montana.edu','','Montana','USA','2008-07-30 19:31:24','0000-00-00 00:00:00'),(7,'Oregon State University','OSU','osu.edu','','Oregon','USA','2008-07-30 19:31:41','0000-00-00 00:00:00'),(8,'North Dakota State University','NDSU','','','North Dakota','USA','2008-07-30 19:32:02','0000-00-00 00:00:00'),(9,'Utah State University','Utah State','usu.edu','','Utah','USA','2008-07-30 19:32:21','0000-00-00 00:00:00'),(10,'Virginia Tech','Virginia Tech','vt.edu','','Virginia','USA','2008-07-30 19:32:35','0000-00-00 00:00:00'),(11,'Washington State University','WSU','wsu.edu','','Washington','USA','2008-07-30 19:32:55','0000-00-00 00:00:00'),(12,'USDA-ARS','USDA, WI',NULL,'','Wisconsin','USA','2008-07-30 19:33:28','0000-00-00 00:00:00'),(13,'USDA-Agricultural Research','USDA_ARS',NULL,'','North Dakota','USA','2008-07-30 19:34:34','0000-00-00 00:00:00'),(14,'University of California, Riverside','UCR','ucr.edu','','California','USA','2008-09-19 15:17:20','0000-00-00 00:00:00'),(15,'Test','Test',NULL,'','Test','USA','2008-11-11 07:21:28','0000-00-00 00:00:00'),(16,'Colorado State University','CSU','N/A','N/A','CO','N/A','2011-08-08 19:34:22','2011-08-08 19:34:22'),(17,'Cornell University','CNL','N/A','N/A','NY','N/A','2011-08-08 19:35:00','2011-08-08 19:35:00'),(18,'Kansas State University','KSM','N/A','N/A','KS','USA','2011-08-08 19:48:40','2011-08-08 19:48:40'),(19,'Kansas State University - Hutchinson','KSH','N/A','N/A','KS','USA','2011-08-08 19:48:59','2011-08-08 19:48:59'),(20,'Louisiana State University','LSU','N/A','N/A','LS','USA','2011-08-08 19:49:24','2011-08-08 19:49:24'),(21,'Michigan State University','MIS','N/A','N/A','MI','USA','2011-08-08 19:51:25','2011-08-08 19:51:25'),(22,'North Carolina State University','NCS','N/A','N/A','NC','USA','2011-08-08 19:51:54','2011-08-08 19:51:54'),(23,'Oklahoma State University','OKS','N/A','N/A','OK','USA','2011-08-08 19:52:31','2011-08-08 19:52:31'),(24,'Purdue University','PUR','N/A','N/A','IN','USA','2011-08-08 19:53:25','2011-08-08 19:53:25'),(25,'The Ohio State University','OSU','N/A','N/A','OH','USA','2011-08-08 19:53:52','2011-08-08 19:53:52'),(26,'University of Arkansas','UAR','N/A','N/A','AR','USA','2011-08-08 19:54:15','2011-08-08 19:54:15'),(27,'University of Georgia','UGA','N/A','N/A','GA','USA','2011-08-08 19:55:06','2011-08-08 19:55:06'),(28,'University of Guelph','UGA','N/A','N/A','ON','Canada','2011-08-08 19:56:05','2011-08-08 19:56:05'),(29,'University of Guelph - Ridgetown','UGR','N/A','N/A','ON','Canada','2011-08-08 19:56:28','2011-08-08 19:56:28'),(30,'University of Idaho','UIA','N/A','N/A','ID','USA','2011-08-08 19:56:49','2011-08-08 19:56:49'),(31,'University of Illinois','UIL','N/A','N/A','IL','USA','2011-08-08 19:57:11','2011-08-08 19:57:11'),(32,'University of Kentucky','UKY','N/A','N/A','KY','USA','2011-08-08 19:57:27','2011-08-08 19:57:27'),(33,'University of Maryland','UMD','N/A','N/A','MD','USA','2011-08-08 19:57:45','2011-08-08 19:57:45'),(34,'University of Missouri','UMO','N/A','N/A','MO','USA','2011-08-08 19:58:16','2011-08-08 19:58:16'),(35,'University of Nebraska','NEB','N/A','N/A','NE','USA','2011-08-08 19:58:37','2011-08-08 19:58:37'),(36,'USDA-Aberdeen Idaho','ABI','N/A','N/A','ID','USA','2011-08-08 19:59:31','2011-08-08 19:59:31'),(37,'Pioneer','PIO','N/A','N/A','N/A','USA','2011-08-08 20:00:25','2011-08-08 20:00:25'),(38,'WestBred','WES','N/A','N/A','MT','USA','2011-08-08 20:00:49','2011-08-08 20:00:49'),(39,'Syngenta','SYN','N/A','N/A','N/A','USA','2011-08-08 20:01:03','2011-08-08 20:01:03'),(40,'Coker','SYN','N/A','N/A','N/A','USA','2011-08-08 20:01:22','2011-08-08 20:01:22'),(41,'Limagrain','LIM','N/A','N/A','CO','USA','2011-08-08 20:01:56','2011-08-08 20:01:56'),(42,'Trio Seeds','TRI','N/A','N/A','N/A','USA','2011-08-08 20:02:16','2011-08-08 20:02:16'),(43,'Sunbeam','KWS','N/A','N/A','N/A','USA','2011-08-08 20:02:40','2011-08-08 20:02:40'),(44,'CIMMYT','CMT','N/A','N/A','N/A','Mexico','2011-08-08 20:03:15','2011-08-08 20:03:15'),(45,'South Dakota State University','SDSU','N/A','N/A','SD','USA','2011-08-08 20:03:49','2011-08-08 20:03:49'),(46,'Texas A&M University','TAM','N/A','N/A','TX','USA','2011-08-08 20:04:10','2011-08-08 20:04:10'),(47,'USDA ARS North Dakota','USDA-ARS, BRL','N/A','N/A','ND','USA','2011-08-09 17:04:52','2011-08-09 17:04:52'),(48,'National Small Grains Collection','NSGC','N/A','N/A','ID','USA','2011-08-14 20:28:33','2011-08-14 20:28:33'),(49,'National Small Grains Collection - Barley','NSGC','N/A','N/A','ID','USA','2011-08-16 20:48:14','2011-08-16 20:48:14'),(50,'AAFC Alberta','ALB','N/A','N/A','Alberta','Canada','2012-02-08 19:40:02','2012-02-08 19:40:02'),(51,'AAFC Manitoba','MTB','N/A','N/A','Manitoba','Canada','2012-02-08 19:40:31','2012-02-08 19:40:31'),(52,'AAFC Saskatchewan','SSK','N/A','N/A','Saskatchewan','Canada','2012-02-08 19:41:08','2012-02-08 19:41:08'),(53,'AgriPro-Syngenta','APS','N/A','N/A','N/A','USA','2012-03-12 22:43:25','2012-03-12 22:43:25'),(54,'Nebraska USDA','NEU','N/A','N/A','Nebraska','USA','2012-03-12 22:44:28','2012-03-12 22:44:28');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COMMENT='Types of annotations and links,e.g., HarvEST assembly 32, Pr';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marker_annotation_types`
--

LOCK TABLES `marker_annotation_types` WRITE;
/*!40000 ALTER TABLE `marker_annotation_types` DISABLE KEYS */;
INSERT INTO `marker_annotation_types` VALUES (1,'HarvEST Unigenes 32','Close Lab: HarvEST Assembly 32 Unigene Number','http://harvest-web.org/viewsetno.wc?assembly=32&unigeneid=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(2,'HarvEST Unigenes 35','Close Lab: HarvEST Assembly 35 Unigene number','http://harvest-web.org/viewsetno.wc?assembly=35&unigeneid=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(3,'PLEXdb U32 Probe Set','Close Lab, HarvEST Assembly 32; at least 5 mateches','http://www.plexdb.org/modules/PD_probeset/annotation.php?genechip=Barley1&exemplar=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(4,'Gramene U32 rice locus','Close Lab, using Barley Assembly 32','http://gramene.org/db/markers/marker_view?marker_name=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(5,'Gramene U32 rice annotation','Close Lab, using HarvEST Barley Assembly 32',NULL,'2009-01-19 05:49:57','2009-01-19 05:49:57'),(6,'PLEXdb U35 Probe Set','Close Lab, HarvEST Assembly 35, at least 5 matches','http://www.plexdb.org/modules/PD_probeset/annotation.php?genechip=Barley1&exemplar=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(7,'Gramene U35 rice locus','Close lab using Barley Assembly 35','http://gramene.org/db/markers/marker_view?marker_name=XXXX','2009-01-19 05:49:57','2009-01-19 05:49:57'),(8,'Gramene U35 rice annotation','Close Lab, using HarvEST Barley Assembly 35',NULL,'2009-01-19 05:49:57','2009-01-19 05:49:57'),(9,'GrainGenes','','http://wheat.pw.usda.gov/cgi-bin/graingenes/report.cgi?class=marker&name=XXXX','2010-05-03 23:58:05','0000-00-00 00:00:00'),(10,'NCBI EST','N/A','http://www.ncbi.nlm.nih.gov/nucest?term=XXXX','2011-08-03 18:09:04','2011-08-03 18:09:04');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COMMENT='Denotes the types of markers. Currently all markers come fro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marker_types`
--

LOCK TABLES `marker_types` WRITE;
/*!40000 ALTER TABLE `marker_types` DISABLE KEYS */;
INSERT INTO `marker_types` VALUES (1,'OPA SNP Name','CAP marker','2008-08-11 16:59:02','2008-08-11 16:59:02'),(2,'DArT Marker','DArT Hayes Lab','2009-01-18 15:18:43','0000-00-00 00:00:00'),(3,'Historical','GrainGenes','2009-01-18 15:19:23','0000-00-00 00:00:00'),(4,'QTL','GrainGenes','2009-01-18 16:10:21','0000-00-00 00:00:00'),(5,'SNP','single nucleotide polymorphism','2011-10-21 22:49:45','2011-10-21 22:49:45');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COMMENT='Phenotypes are computed across different categories such as ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phenotype_category`
--

LOCK TABLES `phenotype_category` WRITE;
/*!40000 ALTER TABLE `phenotype_category` DISABLE KEYS */;
INSERT INTO `phenotype_category` VALUES (1,'Agronomic Traits','2008-07-30 19:23:50','0000-00-00 00:00:00'),(3,'Diseases','2008-07-30 19:24:01','0000-00-00 00:00:00'),(4,'Morphological traits','2008-07-30 19:24:06','0000-00-00 00:00:00'),(5,'Dry matter partition','2008-07-30 19:24:10','0000-00-00 00:00:00'),(6,'Drought Responses','2008-07-30 19:24:15','0000-00-00 00:00:00'),(7,'Winter growth habit','2008-07-30 19:24:23','0000-00-00 00:00:00'),(14,'Quality Traits','2011-12-09 18:30:02','2011-12-09 18:30:02');
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
INSERT INTO `settings` VALUES ('smtpserver','graingenes.org:25'),('mailfrom','noreply@graingenes.org'),('encryptionkey','AJrm0Tc9N3qmLpTWMoeVguzOU'),('capmail','tht_curator@graingenes.org'),('feedbackmail','tht_curator@graingenes.org'),('capencryptionkey','P7aYaR4AlJD7GREqZoFzBB72b'),('passresetkey','n0IIF6oeH9ab7Ik30uyFiQaz9'),('allele_count','25778672');
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'kg ha -1',NULL,NULL,2,'2008-07-30 19:20:21','0000-00-00 00:00:00'),(2,'centimeter','cm',NULL,1,'2008-07-30 19:20:27','0000-00-00 00:00:00'),(3,'days from planting',NULL,NULL,1,'2008-07-30 19:20:56','0000-00-00 00:00:00'),(4,'percent','%',NULL,1,'2008-07-30 19:21:20','0000-00-00 00:00:00'),(5,'% by wt on 6/64â€ sieve ',NULL,NULL,1,'2008-07-30 19:21:30','0000-00-00 00:00:00'),(6,'g liter -1',NULL,NULL,2,'2008-07-30 19:21:37','0000-00-00 00:00:00'),(7,'milligram','mg',NULL,2,'2008-07-30 19:21:43','0000-00-00 00:00:00'),(8,'Agtron',NULL,NULL,2,'2008-07-30 19:21:48','0000-00-00 00:00:00'),(9,'Disease rating 0-4 with qualifiers','0-4','string',-1,'2008-07-30 19:21:54','0000-00-00 00:00:00'),(10,'&deg;ASBC',NULL,NULL,1,'2008-07-30 19:22:00','0000-00-00 00:00:00'),(11,'20&deg;DU',NULL,NULL,2,'2008-07-30 19:22:05','0000-00-00 00:00:00'),(12,'parts per million','ppm',NULL,2,'2008-07-30 19:22:11','0000-00-00 00:00:00'),(13,'ASBC',NULL,NULL,2,'2008-07-30 19:22:17','0000-00-00 00:00:00'),(14,'days',NULL,NULL,1,'2008-07-30 19:22:22','0000-00-00 00:00:00'),(15,'% survival',NULL,NULL,1,'2008-07-30 19:22:46','0000-00-00 00:00:00'),(16,'kernels/spike',NULL,NULL,1,'2009-02-02 04:04:36','0000-00-00 00:00:00'),(17,'Disease Rating 0-9','0-9','numerical rating',1,'2009-04-17 21:45:16','0000-00-00 00:00:00'),(18,'millimeters','mm',NULL,1,'2009-05-21 18:41:33','0000-00-00 00:00:00'),(19,'Disease Rating 0-8','0-8',NULL,1,'2009-08-16 21:50:10','2009-08-16 05:00:00'),(20,'Infection response rating 0-5','0-5',NULL,1,'2009-08-16 21:55:25','2009-08-16 05:00:00'),(21,'Infection response rating 1-9','1-9',NULL,1,'2009-08-16 21:56:22','2009-08-16 05:00:00'),(22,'U/kg malt',NULL,NULL,1,'2009-09-13 02:02:02','0000-00-00 00:00:00'),(23,'Tekauz scale (1-10)',NULL,'Tekauz scale (1-10) where 1-3 is resistant, 4-5 is moderately resistant (MR), 6-7 is moderately susceptible (MS), and 8-10 is susceptible (S)',1,'2009-09-13 15:21:48','2009-09-13 05:00:00'),(24,'septoria seedling infection response (0-5)','ssir (0-5)','A 0-5 rating scale was developed by H. Toubia-Rahme was used for barley infected by S. passerinii.  This scale is based on one developed for wheat by Rosielle 1972 (Euphytica 21:152-161), where 0=immune: no visible symptoms; 1=highly resistant: presence o',2,'2009-09-13 16:22:42','2009-09-13 05:00:00'),(25,'spot blotch seedling infection response (1-9)','SBSIR (1-9)','1-9 rating scale where 1-4 is indicative of a low IP, 5 of intermediate IP, and 6-9 of a high IP.',1,'2009-09-13 16:27:50','2009-09-13 05:00:00'),(26,'Head drop rating scale',NULL,'head drop rating scale (0-9)',1,'2009-09-13 17:58:34','2009-09-13 05:00:00'),(27,'single kernel characterization system','SKCS','single kernel characterization system',1,'2009-09-15 22:27:59','0000-00-00 00:00:00'),(28,'% dry weight basis',NULL,NULL,3,'2009-09-15 22:28:34','0000-00-00 00:00:00'),(29,'absorbance, dry weight basis',NULL,NULL,2,'2009-09-15 22:29:43','0000-00-00 00:00:00'),(30,'N/A',NULL,NULL,NULL,'2011-08-25 23:18:15','2011-08-25 23:18:15'),(31,'celsius',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(32,'spad',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(33,'skcs',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(34,'mm',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(35,'growth habit rating 1-4',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(36,'g',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(37,'kernels per spike',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(38,'mg',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(39,'spikelets per head',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(40,'solidness rating 1-5',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(41,'spikes per area',NULL,NULL,NULL,'2011-08-26 16:55:59','2011-08-26 16:55:59'),(42,'alphabetical scale',NULL,NULL,NULL,'2011-08-30 19:40:55','2011-08-30 19:40:55');
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-03-13 10:43:07
