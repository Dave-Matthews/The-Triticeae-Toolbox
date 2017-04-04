-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: T3wheat
-- ------------------------------------------------------
-- Server version	5.7.17-0ubuntu0.16.04.1-log

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
-- Table structure for table `CAPdata_programs`
--

DROP TABLE IF EXISTS `CAPdata_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAPdata_programs` (
  `CAPdata_programs_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_program_code` varchar(10) NOT NULL,
  `data_program_name` varchar(255) NOT NULL DEFAULT 'N/A',
  `institutions_uid` int(10) unsigned NOT NULL,
  `program_type` enum('breeding','data','mapping') NOT NULL,
  `collaborator_name` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`CAPdata_programs_uid`),
  UNIQUE KEY `data_program_code` (`data_program_code`),
  UNIQUE KEY `data_program_name` (`data_program_name`),
  KEY `institutions_uid` (`institutions_uid`),
  CONSTRAINT `CAPdata_programs_ibfk_1` FOREIGN KEY (`institutions_uid`) REFERENCES `institutions` (`institutions_uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=latin1 COMMENT='Contains a list of all CAP data creation programs in the Bar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acxyz`
--

DROP TABLE IF EXISTS `acxyz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acxyz` (
  `line_record_uid` int(10) unsigned DEFAULT NULL,
  `line_record_name` varchar(255) DEFAULT NULL,
  `marker_uid` int(10) unsigned DEFAULT NULL,
  `marker_name` varchar(255) DEFAULT NULL,
  `count` int(10) DEFAULT NULL,
  KEY `line_record_uid` (`line_record_uid`),
  KEY `marker_uid` (`marker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_byline`
--

DROP TABLE IF EXISTS `allele_byline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_byline` (
  `line_record_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `line_record_name` varchar(50) DEFAULT NULL,
  `alleles` mediumtext,
  PRIMARY KEY (`line_record_uid`),
  KEY `line_record_uid` (`line_record_uid`),
  CONSTRAINT `allele_byline_fk1` FOREIGN KEY (`line_record_uid`) REFERENCES `line_records` (`line_record_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_byline_clust`
--

DROP TABLE IF EXISTS `allele_byline_clust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_byline_clust` (
  `line_record_uid` int(11) NOT NULL,
  `line_record_name` varchar(50) DEFAULT NULL,
  `alleles` mediumtext,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`line_record_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Cache created from table allele_byline.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_byline_exp`
--

DROP TABLE IF EXISTS `allele_byline_exp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_byline_exp` (
  `experiment_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `line_record_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `line_record_name` varchar(255) DEFAULT NULL,
  `count` int(10) unsigned DEFAULT NULL,
  `alleles` mediumtext,
  PRIMARY KEY (`experiment_uid`,`line_record_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_byline_expidx`
--

DROP TABLE IF EXISTS `allele_byline_expidx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_byline_expidx` (
  `experiment_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `marker_index` mediumtext,
  `marker_name_index` mediumtext,
  PRIMARY KEY (`experiment_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_byline_idx`
--

DROP TABLE IF EXISTS `allele_byline_idx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_byline_idx` (
  `marker_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `marker_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`marker_uid`),
  CONSTRAINT `allele_byline_idx_fk1` FOREIGN KEY (`marker_uid`) REFERENCES `markers` (`marker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_bymarker`
--

DROP TABLE IF EXISTS `allele_bymarker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_bymarker` (
  `marker_uid` int(11) NOT NULL,
  `marker_name` varchar(50) DEFAULT NULL,
  `alleles` mediumtext,
  PRIMARY KEY (`marker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_bymarker_exp_101`
--

DROP TABLE IF EXISTS `allele_bymarker_exp_101`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_bymarker_exp_101` (
  `experiment_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `marker_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `marker_name` varchar(255) DEFAULT NULL,
  `chrom` varchar(25) DEFAULT NULL,
  `pos` int(10) unsigned DEFAULT NULL,
  `alleles` mediumtext,
  PRIMARY KEY (`experiment_uid`,`marker_uid`),
  KEY `allele_bymarker_exp_101_fk2` (`marker_uid`),
  CONSTRAINT `allele_bymarker_exp_101_fk1` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`),
  CONSTRAINT `allele_bymarker_exp_101_fk2` FOREIGN KEY (`marker_uid`) REFERENCES `markers` (`marker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_bymarker_exp_ACTG`
--

DROP TABLE IF EXISTS `allele_bymarker_exp_ACTG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_bymarker_exp_ACTG` (
  `experiment_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `marker_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `marker_name` varchar(255) DEFAULT NULL,
  `chrom` varchar(25) DEFAULT NULL,
  `pos` int(10) unsigned DEFAULT NULL,
  `alleles` mediumtext,
  PRIMARY KEY (`experiment_uid`,`marker_uid`),
  KEY `allele_bymarker_exp_ACTG_fk2` (`marker_uid`),
  CONSTRAINT `allele_bymarker_exp_ACTG_fk1` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`),
  CONSTRAINT `allele_bymarker_exp_ACTG_fk2` FOREIGN KEY (`marker_uid`) REFERENCES `markers` (`marker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_bymarker_expidx`
--

DROP TABLE IF EXISTS `allele_bymarker_expidx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_bymarker_expidx` (
  `experiment_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `line_index` mediumtext,
  `line_name_index` mediumtext,
  PRIMARY KEY (`experiment_uid`),
  CONSTRAINT `allele_bymarker_expidx_fk1` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_bymarker_idx`
--

DROP TABLE IF EXISTS `allele_bymarker_idx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_bymarker_idx` (
  `line_record_uid` int(11) NOT NULL,
  `line_record_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`line_record_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_cache`
--

DROP TABLE IF EXISTS `allele_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_cache` (
  `marker_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `marker_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `line_record_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `line_record_name` varchar(255) NOT NULL,
  `experiment_uid` int(10) unsigned NOT NULL,
  `allele_uid` int(12) unsigned NOT NULL DEFAULT '0',
  `alleles` varchar(2) DEFAULT NULL,
  KEY `experiment_uid` (`experiment_uid`),
  KEY `line_record_uid` (`line_record_uid`),
  KEY `marker_uid` (`marker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_conflicts`
--

DROP TABLE IF EXISTS `allele_conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_conflicts` (
  `line_record_uid` int(10) unsigned DEFAULT NULL,
  `marker_uid` int(10) unsigned DEFAULT NULL,
  `alleles` varchar(2) DEFAULT NULL,
  `experiment_uid` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_duplicates`
--

DROP TABLE IF EXISTS `allele_duplicates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_duplicates` (
  `line_record_uid` int(10) unsigned NOT NULL,
  `duplicates` int(10) unsigned DEFAULT NULL,
  `conflicts` int(10) unsigned DEFAULT NULL,
  `percent_conf` int(10) unsigned DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`line_record_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allele_frequencies`
--

DROP TABLE IF EXISTS `allele_frequencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allele_frequencies` (
  `allele_frequency_uid` int(11) NOT NULL AUTO_INCREMENT,
  `marker_uid` int(10) unsigned NOT NULL,
  `experiment_uid` int(10) unsigned NOT NULL,
  `missing` smallint(6) NOT NULL COMMENT 'Number of missing measurements',
  `aa_cnt` int(11) NOT NULL COMMENT 'Number of AA combinations',
  `aa_freq` double NOT NULL COMMENT 'Percentage of AA alleles  out of all calls.',
  `ab_cnt` int(11) NOT NULL,
  `ab_freq` double NOT NULL COMMENT 'Percentage of AB alleles  out of all calls.',
  `bb_cnt` int(11) NOT NULL,
  `bb_freq` double NOT NULL COMMENT 'Percentage of BB alleles  out of all calls.',
  `total` int(11) NOT NULL COMMENT 'Total number of calls',
  `monomorphic` enum('Y','N') NOT NULL COMMENT 'Is the allele monomorphic (same allele for all lines in the experiment)?',
  `maf` double NOT NULL,
  `gentrain_score` double NOT NULL COMMENT 'Gentrain score from summary data sheets',
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`allele_frequency_uid`),
  KEY `fk_markers` (`marker_uid`),
  KEY `fk_allele_frequencies_experiments` (`experiment_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=9460073 DEFAULT CHARSET=latin1 COMMENT='Table contains marker statistics for a single marker across ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alleles`
--

DROP TABLE IF EXISTS `alleles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alleles` (
  `allele_uid` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `genotyping_data_uid` int(12) unsigned NOT NULL,
  `allele_1` char(1) DEFAULT NULL,
  `allele_2` char(1) DEFAULT NULL,
  `theta` double DEFAULT NULL,
  `R` double DEFAULT NULL COMMENT 'Must be positive',
  `X` double DEFAULT NULL,
  `Y` double DEFAULT NULL,
  `X_raw` int(11) DEFAULT NULL,
  `Y_raw` int(11) DEFAULT NULL,
  `GC_score` double DEFAULT NULL,
  `GT_score` double DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`allele_uid`),
  UNIQUE KEY `fk_genotyping_data` (`genotyping_data_uid`),
  KEY `genotyping_data_uid_index` (`genotyping_data_uid`),
  CONSTRAINT `alleles_ibfk_1` FOREIGN KEY (`genotyping_data_uid`) REFERENCES `genotyping_data` (`genotyping_data_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=208230241 DEFAULT CHARSET=latin1 COMMENT='Illumina data for a given SNP';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alleles_genotyping_data`
--

DROP TABLE IF EXISTS `alleles_genotyping_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alleles_genotyping_data` (
  `alleles_genotyping_data_uid` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `tht_base_uid` int(11) NOT NULL,
  `marker_uid` int(10) unsigned NOT NULL,
  `allele_1` char(1) DEFAULT NULL,
  `allele_2` char(1) DEFAULT NULL,
  `theta` double DEFAULT NULL,
  `R` double DEFAULT NULL COMMENT 'Must be positive',
  `X` double DEFAULT NULL,
  `Y` double DEFAULT NULL,
  `X_raw` int(11) DEFAULT NULL,
  `Y_raw` int(11) DEFAULT NULL,
  `GC_score` double DEFAULT NULL,
  `GT_score` double DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`alleles_genotyping_data_uid`),
  UNIQUE KEY `tb_marker` (`tht_base_uid`,`marker_uid`),
  KEY `marker_uid` (`marker_uid`),
  KEY `tht_base_uid` (`tht_base_uid`),
  KEY `fk_tht_base` (`tht_base_uid`),
  KEY `fk_marker` (`marker_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `barley_pedigree_catalog_ref`
--

DROP TABLE IF EXISTS `barley_pedigree_catalog_ref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barley_pedigree_catalog_ref` (
  `barley_pedigree_catalog_ref_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `barley_pedigree_catalog_uid` int(10) unsigned NOT NULL,
  `line_record_uid` int(10) unsigned NOT NULL COMMENT 'Number of line linked to',
  `barley_ref_number` varchar(45) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`barley_pedigree_catalog_ref_uid`),
  UNIQUE KEY `refnumber` (`barley_ref_number`),
  KEY `fk_barley_pedigree_catalog_ref_barley_pedigree_catalog` (`barley_pedigree_catalog_uid`),
  KEY `line_record_uid` (`line_record_uid`),
  CONSTRAINT `barley_pedigree_catalog_ref_ibfk_1` FOREIGN KEY (`line_record_uid`) REFERENCES `line_records` (`line_record_uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_barley_pedigree_catalog_ref_barley_pedigree_catalog` FOREIGN KEY (`barley_pedigree_catalog_uid`) REFERENCES `barley_pedigree_catalog` (`barley_pedigree_catalog_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6158 DEFAULT CHARSET=latin1 COMMENT='Links to GRIN and other pedigree data sources, ID number and';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `csr_measurement`
--

DROP TABLE IF EXISTS `csr_measurement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `csr_measurement` (
  `measurement_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(10) unsigned DEFAULT NULL,
  `radiation_dir_uid` int(11) NOT NULL,
  `measure_date` datetime NOT NULL,
  `growth_stage` varchar(20) DEFAULT NULL,
  `growth_stage_name` varchar(255) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `integration_time` int(11) DEFAULT NULL,
  `weather` varchar(20) DEFAULT NULL,
  `spect_sys_uid` int(11) NOT NULL,
  `num_measurements` int(11) DEFAULT NULL,
  `height_from_canopy` int(11) DEFAULT NULL,
  `incident_adj` varchar(10) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `raw_file_name` varchar(50) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`measurement_uid`),
  KEY `experiment_fkey` (`experiment_uid`),
  CONSTRAINT `csr_measurement_ibfk_1` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `csr_measurement_rd`
--

DROP TABLE IF EXISTS `csr_measurement_rd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `csr_measurement_rd` (
  `radiation_dir_uid` int(11) NOT NULL AUTO_INCREMENT,
  `direction` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`radiation_dir_uid`),
  UNIQUE KEY `direction` (`direction`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `csr_system`
--

DROP TABLE IF EXISTS `csr_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `csr_system` (
  `system_uid` int(11) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(50) DEFAULT NULL,
  `instrument` varchar(50) DEFAULT NULL,
  `serial_num` varchar(50) DEFAULT NULL,
  `serial_num2` varchar(50) DEFAULT NULL,
  `grating` varchar(50) DEFAULT NULL,
  `collection_lens` varchar(50) DEFAULT NULL,
  `longpass_filter` varchar(50) DEFAULT NULL,
  `slit_aperture` int(11) DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `cable_type` varchar(50) DEFAULT NULL,
  `wavelengths` varchar(100) DEFAULT NULL,
  `bandwidths` varchar(50) DEFAULT NULL,
  `comments` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`system_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_providers`
--

DROP TABLE IF EXISTS `data_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_providers` (
  `data_providers_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CAPdata_programs_uid` int(10) unsigned NOT NULL,
  `provider_name` varchar(255) NOT NULL,
  `provider_department` varchar(255) DEFAULT NULL,
  `provider_email` varchar(45) NOT NULL,
  `CAP_role` varchar(255) DEFAULT NULL COMMENT 'e.g., breeder, genotyper, biochemist, food chemist, disease phenotypes',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`data_providers_uid`),
  UNIQUE KEY `users_name` (`provider_name`),
  KEY `fk_data_providers_CAPdata_programs` (`CAPdata_programs_uid`),
  CONSTRAINT `fk_data_providers_CAPdata_programs` FOREIGN KEY (`CAPdata_programs_uid`) REFERENCES `CAPdata_programs` (`CAPdata_programs_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Data providers for the CAP project.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datasets`
--

DROP TABLE IF EXISTS `datasets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datasets` (
  `datasets_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CAPdata_programs_uid` int(10) unsigned NOT NULL,
  `breeding_year` year(4) NOT NULL,
  `dataset_name` varchar(255) NOT NULL,
  `datasets_pedigree_data` text COMMENT 'Contains the QTLminer pedigree string',
  `comments` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`datasets_uid`),
  UNIQUE KEY `datasets_index_name` (`dataset_name`),
  KEY `breeding_programs_uid` (`CAPdata_programs_uid`),
  CONSTRAINT `fk_datasets_CAPdata_programs` FOREIGN KEY (`CAPdata_programs_uid`) REFERENCES `CAPdata_programs` (`CAPdata_programs_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=latin1 COMMENT='Defines a group of trials for 1 breeding program for 1 year.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datasets_experiments`
--

DROP TABLE IF EXISTS `datasets_experiments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datasets_experiments` (
  `datasets_experiments_uid` int(10) NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(10) unsigned NOT NULL,
  `datasets_uid` int(10) unsigned NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`datasets_experiments_uid`),
  UNIQUE KEY `datasets_experiments_uidx` (`datasets_uid`,`experiment_uid`),
  KEY `experiment_uid` (`experiment_uid`),
  KEY `fk_experiments` (`experiment_uid`),
  KEY `fk_datasets` (`datasets_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=511 DEFAULT CHARSET=latin1 COMMENT='Allow for a many-to-many relationship between ''experiments'' ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `experiment_set`
--

DROP TABLE IF EXISTS `experiment_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `experiment_set` (
  `experiment_set_uid` int(11) NOT NULL AUTO_INCREMENT,
  `experiment_set_name` varchar(250) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `coordinator` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`experiment_set_uid`),
  UNIQUE KEY `experiment_set_name` (`experiment_set_name`),
  UNIQUE KEY `experiment_set_name_2` (`experiment_set_name`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `experiments`
--

DROP TABLE IF EXISTS `experiments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `experiments` (
  `experiment_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `experiment_type_uid` int(10) unsigned NOT NULL,
  `experiment_set_uid` int(11) DEFAULT NULL,
  `CAPdata_programs_uid` int(10) unsigned NOT NULL COMMENT 'ID of data producer for this experiment',
  `experiment_short_name` varchar(250) DEFAULT NULL,
  `experiment_desc_name` varchar(300) DEFAULT NULL,
  `trial_code` varchar(50) NOT NULL DEFAULT '',
  `traits` varchar(2000) DEFAULT NULL,
  `experiment_year` year(4) NOT NULL DEFAULT '0000',
  `data_public_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '=1 yes, =0 no, CAPprivate',
  `input_data_file_name` varchar(255) DEFAULT NULL,
  `comments` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`experiment_uid`),
  UNIQUE KEY `trial_code_index` (`experiment_uid`,`trial_code`),
  UNIQUE KEY `trial_code` (`trial_code`),
  KEY `experiment_type_uid` (`experiment_type_uid`),
  KEY `experiment_year` (`experiment_year`),
  KEY `experiment_name` (`experiment_short_name`),
  KEY `CAPdata_programs_uid` (`CAPdata_programs_uid`),
  CONSTRAINT `experiments_ibfk_1` FOREIGN KEY (`CAPdata_programs_uid`) REFERENCES `CAPdata_programs` (`CAPdata_programs_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1554 DEFAULT CHARSET=latin1 COMMENT='Table gives annotation information about the experiment.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fieldbook`
--

DROP TABLE IF EXISTS `fieldbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fieldbook` (
  `plot_uid` int(10) NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(11) NOT NULL,
  `range_id` int(11) DEFAULT NULL,
  `plot` int(11) DEFAULT NULL,
  `entry` int(11) DEFAULT NULL,
  `plot_id` varchar(30) DEFAULT NULL,
  `line_uid` int(11) DEFAULT NULL,
  `field_id` varchar(50) DEFAULT NULL,
  `note` varchar(100) DEFAULT NULL,
  `replication` int(11) DEFAULT NULL,
  `block` int(11) DEFAULT NULL,
  `subblock` int(11) DEFAULT NULL,
  `row_id` int(11) DEFAULT NULL,
  `column_id` int(11) DEFAULT NULL,
  `treatment` varchar(50) DEFAULT NULL,
  `block_tmt` varchar(50) DEFAULT NULL,
  `subblock_tmt` varchar(50) DEFAULT NULL,
  `check_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`plot_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=15793 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fieldbook_info`
--

DROP TABLE IF EXISTS `fieldbook_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fieldbook_info` (
  `fieldbook_info_uid` int(11) NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(11) NOT NULL,
  `fieldbook_file_name` varchar(150) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`fieldbook_info_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_process`
--

DROP TABLE IF EXISTS `file_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_process` (
  `file_process_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `def_file_name` varchar(255) DEFAULT NULL,
  `dir_destination` varchar(255) DEFAULT NULL,
  `file_desc` varchar(255) DEFAULT NULL,
  `dataset_name` varchar(255) DEFAULT NULL,
  `process_program` varchar(255) DEFAULT NULL,
  `target_tables` varchar(255) DEFAULT NULL,
  `users_name` varchar(255) DEFAULT NULL,
  `process_result` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`file_process_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filter_sets`
--

DROP TABLE IF EXISTS `filter_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filter_sets` (
  `filter_set_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_uid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`filter_set_uid`),
  KEY `fk_users_uid` (`users_uid`),
  CONSTRAINT `fk_users_uid` FOREIGN KEY (`users_uid`) REFERENCES `tempdb`.`users` (`users_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Currently unused ~Gavin';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filters`
--

DROP TABLE IF EXISTS `filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filters` (
  `filter_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_uid` int(10) unsigned NOT NULL,
  `filter_set_uid` int(10) unsigned NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`filter_uid`),
  KEY `fk_users` (`users_uid`),
  KEY `fk_filter_set` (`filter_set_uid`),
  CONSTRAINT `fk_filter_set` FOREIGN KEY (`filter_set_uid`) REFERENCES `filter_sets` (`filter_set_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_users` FOREIGN KEY (`users_uid`) REFERENCES `tempdb`.`users` (`users_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genotype_experiment_info`
--

DROP TABLE IF EXISTS `genotype_experiment_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genotype_experiment_info` (
  `genotype_experiment_info_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(10) unsigned NOT NULL,
  `marker_type_uid` int(10) unsigned DEFAULT NULL,
  `platform_uid` int(10) unsigned NOT NULL,
  `processing_date` varchar(12) DEFAULT NULL,
  `manifest_file_name` varchar(100) NOT NULL DEFAULT 'N/A',
  `cluster_file_name` varchar(100) NOT NULL DEFAULT 'N/A',
  `OPA_name` varchar(45) NOT NULL DEFAULT 'N/A' COMMENT 'OPA name, Barley OPA, Preliminary OPA, etc',
  `analysis_software` varchar(255) NOT NULL DEFAULT 'N/A',
  `BGST_version_number` varchar(45) NOT NULL DEFAULT 'N/A',
  `sample_sheet_filename` varchar(100) NOT NULL,
  `raw_datafile_archive` varchar(100) DEFAULT NULL COMMENT 'name of zip archive of image files',
  `comments` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`genotype_experiment_info_uid`),
  KEY `fk_phenotype_experiment_info_experiments_uid` (`experiment_uid`),
  KEY `fk_genotype_experiment_info_experiments` (`experiment_uid`),
  KEY `marker_type_uid` (`marker_type_uid`),
  KEY `platform_uid` (`platform_uid`),
  CONSTRAINT `genotype_experiment_info_ibfk_1` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `genotype_experiment_info_ibfk_2` FOREIGN KEY (`marker_type_uid`) REFERENCES `marker_types` (`marker_type_uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `genotype_experiment_info_ibfk_3` FOREIGN KEY (`platform_uid`) REFERENCES `platform` (`platform_uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=latin1 COMMENT='Table gives annotation information about an experiment for g';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genotyping_data`
--

DROP TABLE IF EXISTS `genotyping_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genotyping_data` (
  `genotyping_data_uid` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `tht_base_uid` int(11) NOT NULL,
  `marker_uid` int(10) unsigned NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`genotyping_data_uid`),
  KEY `marker_uid` (`marker_uid`),
  KEY `tht_base_uid` (`tht_base_uid`),
  KEY `fk_tht_base` (`tht_base_uid`),
  KEY `fk_marker` (`marker_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=208858336 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `input_file_log`
--

DROP TABLE IF EXISTS `input_file_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `input_file_log` (
  `input_file_log_uid` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `users_name` varchar(255) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`input_file_log_uid`),
  FULLTEXT KEY `file_name_2` (`file_name`)
) ENGINE=MyISAM AUTO_INCREMENT=1184 DEFAULT CHARSET=latin1 COMMENT='table is a log of data files submitted to THT';
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1 COMMENT='List of all institutions involved with THT project. ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `line_properties`
--

DROP TABLE IF EXISTS `line_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `line_properties` (
  `line_properties_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `line_record_uid` int(10) unsigned NOT NULL,
  `property_value_uid` int(10) unsigned NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`line_properties_uid`),
  KEY `line_record_uid` (`line_record_uid`),
  KEY `property_value_uid` (`property_value_uid`),
  CONSTRAINT `line_properties_ibfk_1` FOREIGN KEY (`line_record_uid`) REFERENCES `line_records` (`line_record_uid`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `line_properties_ibfk_2` FOREIGN KEY (`property_value_uid`) REFERENCES `property_values` (`property_values_uid`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81641 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `line_records`
--

DROP TABLE IF EXISTS `line_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `line_records` (
  `line_record_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `line_record_name` varchar(255) NOT NULL,
  `pedigree_string` longtext,
  `generation` smallint(5) unsigned DEFAULT NULL,
  `breeding_program_code` varchar(10) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`line_record_uid`),
  UNIQUE KEY `line_record_name` (`line_record_name`)
) ENGINE=InnoDB AUTO_INCREMENT=18666 DEFAULT CHARSET=latin1 COMMENT='List of line records';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `line_synonyms`
--

DROP TABLE IF EXISTS `line_synonyms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `line_synonyms` (
  `line_synonyms_uid` int(11) NOT NULL AUTO_INCREMENT,
  `line_record_uid` int(10) unsigned NOT NULL,
  `line_synonym_name` varchar(255) NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`line_synonyms_uid`),
  UNIQUE KEY `line_syn` (`line_synonyms_uid`,`line_synonym_name`),
  KEY `line_record_uid_index` (`line_record_uid`),
  CONSTRAINT `line_synonyms_ibfk_1` FOREIGN KEY (`line_record_uid`) REFERENCES `line_records` (`line_record_uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14348 DEFAULT CHARSET=latin1 COMMENT='Central table linking the database together. What is the don';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `linepanels`
--

DROP TABLE IF EXISTS `linepanels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `linepanels` (
  `linepanels_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_uid` int(10) unsigned DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `line_ids` mediumtext NOT NULL,
  PRIMARY KEY (`linepanels_uid`),
  KEY `users_uid` (`users_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `map`
--

DROP TABLE IF EXISTS `map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map` (
  `map_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mapset_uid` int(10) unsigned NOT NULL,
  `map_name` varchar(255) NOT NULL,
  `map_start` double DEFAULT NULL,
  `map_end` double DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`map_uid`),
  UNIQUE KEY `map_index_name` (`map_name`),
  KEY `mapset_uid` (`mapset_uid`),
  KEY `fk_mapset` (`mapset_uid`),
  CONSTRAINT `fk_mapset` FOREIGN KEY (`mapset_uid`) REFERENCES `mapset` (`mapset_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=724 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mapset`
--

DROP TABLE IF EXISTS `mapset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mapset` (
  `mapset_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mapset_name` varchar(255) NOT NULL,
  `species` varchar(255) DEFAULT NULL,
  `map_type` varchar(45) DEFAULT NULL,
  `map_unit` varchar(45) DEFAULT NULL,
  `published_on` datetime DEFAULT NULL,
  `data_public_flag` tinyint(4) DEFAULT '1',
  `comments` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL,
  PRIMARY KEY (`mapset_uid`),
  UNIQUE KEY `mapset_index_name` (`mapset_name`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COMMENT='Types of annotations and links,e.g., HarvEST assembly 32, Pr';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marker_annotations`
--

DROP TABLE IF EXISTS `marker_annotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_annotations` (
  `marker_annotation_uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `marker_uid` int(10) unsigned NOT NULL,
  `marker_annotation_type_uid` int(10) unsigned NOT NULL,
  `value` varchar(40) CHARACTER SET utf8 NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`marker_annotation_uid`),
  KEY `fk_markers_uid` (`marker_uid`),
  KEY `fk_marker_annotations_marker_annotation_types` (`marker_annotation_type_uid`),
  CONSTRAINT `fk_marker_annotations_marker_annotation_types` FOREIGN KEY (`marker_annotation_type_uid`) REFERENCES `marker_annotation_types` (`marker_annotation_type_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `marker_annotations_ibfk_1` FOREIGN KEY (`marker_uid`) REFERENCES `markers` (`marker_uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6930566 DEFAULT CHARSET=latin1 COMMENT='Links data on marker annotations from other data sources.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marker_report_ref_iwgs`
--

DROP TABLE IF EXISTS `marker_report_ref_iwgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_report_ref_iwgs` (
  `marker1_uid` int(11) NOT NULL DEFAULT '0',
  `marker1_name` varchar(30) DEFAULT NULL,
  `chrom` varchar(30) NOT NULL DEFAULT '',
  `pos` int(11) NOT NULL DEFAULT '0',
  `perc` double DEFAULT NULL,
  `length` int(11) DEFAULT NULL,
  PRIMARY KEY (`marker1_uid`,`chrom`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marker_report_reference`
--

DROP TABLE IF EXISTS `marker_report_reference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_report_reference` (
  `marker1_uid` int(11) NOT NULL DEFAULT '0',
  `marker1_name` varchar(30) DEFAULT NULL,
  `contig` varchar(30) NOT NULL DEFAULT '',
  `contig_pos` int(11) unsigned DEFAULT NULL,
  `contig_strand` char(1) DEFAULT NULL,
  `file_strand` char(1) DEFAULT NULL,
  `chrom` varchar(30) DEFAULT NULL,
  `scaffold` varchar(30) DEFAULT NULL,
  `chrom_pos` int(11) unsigned DEFAULT NULL,
  `type` varchar(1000) DEFAULT NULL,
  `gene` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`marker1_uid`,`contig`),
  KEY `marker_name_index` (`marker1_name`),
  KEY `contig_index` (`contig`),
  KEY `contig_pos` (`contig_pos`),
  KEY `chrom_pos` (`chrom_pos`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marker_report_synonyms`
--

DROP TABLE IF EXISTS `marker_report_synonyms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_report_synonyms` (
  `marker1_uid` int(11) NOT NULL DEFAULT '0',
  `marker1_name` varchar(30) DEFAULT NULL,
  `marker2_uid` int(11) NOT NULL DEFAULT '0',
  `marker2_name` varchar(30) DEFAULT NULL,
  `perc` double DEFAULT NULL,
  `length` int(11) DEFAULT NULL,
  PRIMARY KEY (`marker1_uid`,`marker2_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COMMENT='This table lists the types of marker names and links. Exampl';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marker_synonyms`
--

DROP TABLE IF EXISTS `marker_synonyms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker_synonyms` (
  `marker_synonym_uid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'All entries in markers.marker_name must also be here. Some of the code assumes this.',
  `marker_uid` int(10) unsigned NOT NULL,
  `marker_synonym_type_uid` int(11) NOT NULL,
  `value` varchar(40) NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`marker_synonym_uid`),
  UNIQUE KEY `marker_syn` (`marker_synonym_uid`,`value`),
  KEY `fk_markers_uid1` (`marker_uid`),
  KEY `fk_marker_synonym_types` (`marker_synonym_type_uid`),
  CONSTRAINT `fk_marker_synonym_types` FOREIGN KEY (`marker_synonym_type_uid`) REFERENCES `marker_synonym_types` (`marker_synonym_type_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_markers_uid1` FOREIGN KEY (`marker_uid`) REFERENCES `markers` (`marker_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=33554 DEFAULT CHARSET=latin1 COMMENT='All entries in markers.marker_name must also be here. Some c';
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COMMENT='Denotes the types of markers. Currently all markers come fro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `markerpanels`
--

DROP TABLE IF EXISTS `markerpanels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `markerpanels` (
  `markerpanels_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_uid` int(10) unsigned DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `marker_ids` text NOT NULL COMMENT 'Comma-delimited list of marker_uids',
  PRIMARY KEY (`markerpanels_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `markers`
--

DROP TABLE IF EXISTS `markers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `markers` (
  `marker_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `marker_type_uid` int(10) unsigned NOT NULL,
  `marker_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `A_allele` varchar(10) DEFAULT NULL,
  `B_allele` varchar(10) DEFAULT NULL,
  `sequence` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`marker_uid`),
  UNIQUE KEY `markers_index_name` (`marker_name`),
  KEY `marker_type_uid` (`marker_type_uid`),
  KEY `fk_marker_type` (`marker_type_uid`),
  CONSTRAINT `fk_marker_type` FOREIGN KEY (`marker_type_uid`) REFERENCES `marker_types` (`marker_type_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4465724 DEFAULT CHARSET=latin1 COMMENT='Marker definition table with links to synonyms, types and st';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `markers_in_maps`
--

DROP TABLE IF EXISTS `markers_in_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `markers_in_maps` (
  `markers_in_maps_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `marker_uid` int(10) unsigned NOT NULL,
  `map_uid` int(10) unsigned NOT NULL,
  `start_position` double DEFAULT NULL,
  `end_position` double DEFAULT NULL,
  `bin_name` varchar(45) DEFAULT NULL,
  `chromosome` varchar(12) DEFAULT NULL,
  `arm` varchar(8) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`markers_in_maps_uid`),
  UNIQUE KEY `markers_in_maps_index_foreign` (`marker_uid`,`map_uid`),
  KEY `fk_marker_uid` (`marker_uid`),
  KEY `fk_map_uid` (`map_uid`),
  KEY `start_position` (`start_position`),
  CONSTRAINT `fk_map` FOREIGN KEY (`map_uid`) REFERENCES `map` (`map_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_marker_uid` FOREIGN KEY (`marker_uid`) REFERENCES `markers` (`marker_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=15578983 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pedigree_relations`
--

DROP TABLE IF EXISTS `pedigree_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedigree_relations` (
  `pedigree_relation_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `line_record_uid` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `contribution` double DEFAULT NULL,
  `selfing` varchar(20) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`pedigree_relation_uid`),
  UNIQUE KEY `pedigree_relations_unique` (`parent_id`,`line_record_uid`),
  KEY `line_record_uid` (`line_record_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=5461 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1 COMMENT='Phenotypes are computed across different categories such as ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `phenotype_data`
--

DROP TABLE IF EXISTS `phenotype_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phenotype_data` (
  `phenotype_data_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phenotype_uid` int(10) unsigned NOT NULL,
  `tht_base_uid` int(11) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `recording_date` datetime DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`phenotype_data_uid`),
  KEY `value` (`value`),
  KEY `fk_phenotype_uid` (`phenotype_uid`),
  KEY `fk_tht_base_uid` (`tht_base_uid`),
  KEY `phenotype_data_index_name` (`tht_base_uid`,`phenotype_uid`),
  CONSTRAINT `phenotype_data_ibfk_1` FOREIGN KEY (`phenotype_uid`) REFERENCES `phenotypes` (`phenotype_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=843569 DEFAULT CHARSET=latin1 COMMENT='Contains phenotype data with links to phenotype definitions,';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `phenotype_descstat`
--

DROP TABLE IF EXISTS `phenotype_descstat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phenotype_descstat` (
  `phenotype_descstat_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phenotype_uid` int(10) unsigned NOT NULL,
  `mean_val` double DEFAULT NULL,
  `max_val` double DEFAULT NULL,
  `min_val` double DEFAULT NULL,
  `sample_size` int(10) unsigned DEFAULT NULL,
  `std_val` double DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`phenotype_descstat_uid`),
  KEY `phenotype_uid` (`phenotype_uid`),
  CONSTRAINT `fk_phenotype_uid1` FOREIGN KEY (`phenotype_uid`) REFERENCES `phenotypes` (`phenotype_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=latin1 COMMENT='Phenotype descriptive statistics such as mean, standard devi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `phenotype_experiment_info`
--

DROP TABLE IF EXISTS `phenotype_experiment_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phenotype_experiment_info` (
  `phenotype_experiment_info_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(10) unsigned NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` varchar(25) DEFAULT NULL,
  `longitude` varchar(25) DEFAULT NULL,
  `collaborator` varchar(50) DEFAULT NULL,
  `planting_date` date DEFAULT NULL,
  `harvest_date` date DEFAULT NULL,
  `begin_weather_date` date DEFAULT NULL,
  `greenhouse_trial` enum('yes','no') DEFAULT NULL,
  `seeding_rate` varchar(255) DEFAULT NULL,
  `experiment_design` varchar(255) DEFAULT NULL,
  `number_entries` smallint(5) unsigned DEFAULT NULL,
  `plot_size` varchar(255) DEFAULT NULL,
  `harvest_area` varchar(255) DEFAULT NULL,
  `irrigation` varchar(20) DEFAULT NULL,
  `latitude_longitude` varchar(255) DEFAULT NULL,
  `number_replications` smallint(5) unsigned DEFAULT NULL,
  `other_remarks` text,
  `mean_calculation` enum('import','calculated') DEFAULT 'import',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`phenotype_experiment_info_uid`),
  KEY `fk_phenotype_experiment_info_experiments` (`experiment_uid`),
  CONSTRAINT `phenotype_experiment_info_ibfk_1` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1472 DEFAULT CHARSET=latin1 COMMENT='Table gives annotation information about an experiment for p';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `phenotype_mean_data`
--

DROP TABLE IF EXISTS `phenotype_mean_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phenotype_mean_data` (
  `phenotype_mean_data_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(10) unsigned NOT NULL,
  `phenotype_uid` int(10) unsigned NOT NULL,
  `mean_value` double DEFAULT NULL,
  `standard_error` double DEFAULT NULL,
  `std_err_diff` double DEFAULT NULL COMMENT 'Standard Error of the Difference',
  `number_replicates` int(11) DEFAULT NULL,
  `prob_gt_F` varchar(25) DEFAULT NULL,
  `cv` double DEFAULT NULL COMMENT 'Coefficient of Variation',
  `note` varchar(255) DEFAULT NULL,
  `mean_calculation` enum('import','calculated') DEFAULT 'import',
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`phenotype_mean_data_uid`),
  KEY `phenotype_uid` (`phenotype_uid`),
  KEY `value` (`mean_value`),
  KEY `fk_phenotype_uid2` (`phenotype_uid`),
  KEY `fk_phenotype_mean_data_experiments` (`experiment_uid`),
  KEY `fk_phenotype_mean_data_phenotypes` (`phenotype_uid`),
  KEY `phenotype_data_index_name` (`phenotype_uid`),
  CONSTRAINT `phenotype_mean_data_ibfk_1` FOREIGN KEY (`phenotype_uid`) REFERENCES `phenotypes` (`phenotype_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2012 DEFAULT CHARSET=latin1 COMMENT='Contains computed means from complete trials for phenotype d';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `phenotype_plot_data`
--

DROP TABLE IF EXISTS `phenotype_plot_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phenotype_plot_data` (
  `phenotype_data_uid` int(10) NOT NULL AUTO_INCREMENT,
  `phenotype_uid` int(10) NOT NULL,
  `experiment_uid` int(10) NOT NULL,
  `plot_uid` int(10) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`phenotype_data_uid`),
  KEY `experiment_uid` (`experiment_uid`),
  KEY `phenotype_uid` (`phenotype_uid`),
  KEY `plot_uid` (`plot_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=131437 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `phenotypes`
--

DROP TABLE IF EXISTS `phenotypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phenotypes` (
  `phenotype_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unit_uid` int(10) unsigned NOT NULL,
  `phenotype_category_uid` int(10) unsigned NOT NULL,
  `phenotypes_name` varchar(255) NOT NULL,
  `alternate_name` varchar(255) DEFAULT NULL,
  `description` varchar(2500) DEFAULT NULL,
  `TO_number` varchar(50) DEFAULT NULL,
  `datatype` enum('text','numeric') DEFAULT NULL,
  `max_pheno_value` double DEFAULT NULL,
  `min_pheno_value` double DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`phenotype_uid`),
  UNIQUE KEY `phenotypes_name` (`phenotype_category_uid`,`phenotypes_name`),
  KEY `unit_uid` (`unit_uid`),
  KEY `fk_phenotype_category` (`phenotype_category_uid`),
  CONSTRAINT `fk_phenotype_category` FOREIGN KEY (`phenotype_category_uid`) REFERENCES `phenotype_category` (`phenotype_category_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_unit` FOREIGN KEY (`unit_uid`) REFERENCES `units` (`unit_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=297 DEFAULT CHARSET=latin1 COMMENT='Defines phenotype names, short names and any text descriptio';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `platform`
--

DROP TABLE IF EXISTS `platform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform` (
  `platform_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `platform_name` varchar(100) DEFAULT NULL COMMENT 'GoldenGate, Infinium, VeraCode, Sequenom, KASP, Restriction-site GBS, Exome-capture GBS',
  `description` text,
  PRIMARY KEY (`platform_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `properties` (
  `properties_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phenotype_category_uid` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL COMMENT 'e.g. Color',
  `description` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`properties_uid`),
  UNIQUE KEY `name` (`name`),
  KEY `phenotype_category_uid` (`phenotype_category_uid`),
  CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`phenotype_category_uid`) REFERENCES `phenotype_category` (`phenotype_category_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=latin1 COMMENT='Qualitative characters for germplasm lines';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `property`
--

DROP TABLE IF EXISTS `property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `property` (
  `property_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `property` varchar(50) NOT NULL COMMENT 'e.g. Color',
  `vals` varchar(500) DEFAULT NULL COMMENT 'If restricted. Comma-separated, e.g. Red,White',
  PRIMARY KEY (`property_uid`),
  UNIQUE KEY `property` (`property`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Qualitative characters for germplasm lines';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `property_values`
--

DROP TABLE IF EXISTS `property_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `property_values` (
  `property_values_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `property_uid` int(10) unsigned NOT NULL,
  `value` varchar(50) NOT NULL COMMENT 'e.g. Red, 2, Present',
  PRIMARY KEY (`property_values_uid`),
  KEY `property_uid` (`property_uid`),
  CONSTRAINT `property_values_ibfk_1` FOREIGN KEY (`property_uid`) REFERENCES `properties` (`properties_uid`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=latin1 COMMENT='Allowed values for each property of a germplasm line';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qtl_annotations`
--

DROP TABLE IF EXISTS `qtl_annotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qtl_annotations` (
  `qtl_annotation_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `marker_uid` int(10) unsigned DEFAULT NULL,
  `marker_name` varchar(50) DEFAULT NULL,
  `assembly_ver` varchar(50) DEFAULT NULL,
  `gene` varchar(50) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `uniprot` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`qtl_annotation_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=150196 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qtl_imputed`
--

DROP TABLE IF EXISTS `qtl_imputed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qtl_imputed` (
  `phenotype_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `genotype_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `phenotype_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `platform` int(11) DEFAULT NULL,
  `method` int(11) NOT NULL DEFAULT '0',
  `chrom` varchar(10) NOT NULL DEFAULT '',
  `gwas` mediumtext,
  PRIMARY KEY (`genotype_exp`,`phenotype_uid`,`phenotype_exp`,`method`,`chrom`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qtl_raw`
--

DROP TABLE IF EXISTS `qtl_raw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qtl_raw` (
  `phenotype_uid` int(10) unsigned NOT NULL,
  `genotype_exp` int(10) unsigned NOT NULL,
  `phenotype_exp` int(10) unsigned NOT NULL,
  `platform` int(11) DEFAULT NULL,
  `method` int(11) NOT NULL,
  `chrom` varchar(10) NOT NULL,
  `gwas` json DEFAULT NULL,
  PRIMARY KEY (`genotype_exp`,`phenotype_uid`,`phenotype_exp`,`method`,`chrom`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qtl_set`
--

DROP TABLE IF EXISTS `qtl_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qtl_set` (
  `phenotype_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `genotype_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `phenotype_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `platform` int(11) DEFAULT NULL,
  `method` int(11) NOT NULL DEFAULT '0',
  `chrom` varchar(10) NOT NULL DEFAULT '',
  `gwas` mediumtext,
  PRIMARY KEY (`genotype_exp`,`phenotype_uid`,`phenotype_exp`,`method`,`chrom`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rawfiles`
--

DROP TABLE IF EXISTS `rawfiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rawfiles` (
  `rawfile_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `experiment_uid` int(10) unsigned NOT NULL,
  `users_uid` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `directory` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rawfile_uid`),
  UNIQUE KEY `experiment_uid` (`experiment_uid`,`name`),
  KEY `users_uid` (`users_uid`),
  CONSTRAINT `rawfiles_ibfk_2` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rawfiles_ibfk_3` FOREIGN KEY (`users_uid`) REFERENCES `users` (`users_uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session_variables`
--

DROP TABLE IF EXISTS `session_variables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_variables` (
  `session_variables_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `session_variables_name` varchar(255) DEFAULT NULL,
  `serialized_values` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '2000-01-01 08:00:00',
  PRIMARY KEY (`session_variables_uid`),
  UNIQUE KEY `session_variable_uidx` (`users_uid`,`session_variables_name`),
  KEY `fk_users_uid3` (`users_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=222 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `sharegroup`
--

DROP TABLE IF EXISTS `sharegroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sharegroup` (
  `sharegroup_uid` int(11) NOT NULL AUTO_INCREMENT,
  `owner_users_uid` int(10) unsigned DEFAULT NULL,
  `shareto_users_uid` int(10) unsigned DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`sharegroup_uid`),
  KEY `owner_users_uid` (`owner_users_uid`),
  KEY `shareto_users_uid` (`shareto_users_uid`),
  CONSTRAINT `fk_owner_users_uid` FOREIGN KEY (`owner_users_uid`) REFERENCES `tempdb`.`users` (`users_uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_shareto_users_uid` FOREIGN KEY (`shareto_users_uid`) REFERENCES `tempdb`.`users` (`users_uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_filters`
--

DROP TABLE IF EXISTS `temp_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_filters` (
  `temp_filters_uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_uid` int(10) unsigned NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`temp_filters_uid`),
  KEY `fk_users_uid2` (`users_uid`),
  CONSTRAINT `fk_users_uid2` FOREIGN KEY (`users_uid`) REFERENCES `tempdb`.`users` (`users_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tht_base`
--

DROP TABLE IF EXISTS `tht_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tht_base` (
  `tht_base_uid` int(11) NOT NULL AUTO_INCREMENT,
  `line_record_uid` int(10) unsigned NOT NULL,
  `experiment_uid` int(10) unsigned NOT NULL,
  `datasets_experiments_uid` int(10) DEFAULT NULL,
  `trial_code_number` varchar(50) DEFAULT NULL,
  `check_line` enum('yes','no') NOT NULL DEFAULT 'no',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`tht_base_uid`),
  UNIQUE KEY `tht_base_index_name` (`line_record_uid`,`experiment_uid`,`check_line`),
  KEY `tht_base_experiments_frk` (`experiment_uid`),
  KEY `fk_line_record_uid1` (`line_record_uid`),
  CONSTRAINT `fk_experiment_uid1` FOREIGN KEY (`experiment_uid`) REFERENCES `experiments` (`experiment_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_line_record_uid1` FOREIGN KEY (`line_record_uid`) REFERENCES `line_records` (`line_record_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=223419 DEFAULT CHARSET=latin1 COMMENT='Central table linking the database together. What is the don';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trait_ontology_term`
--

DROP TABLE IF EXISTS `trait_ontology_term`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trait_ontology_term` (
  `gramene_uid` int(10) NOT NULL AUTO_INCREMENT,
  `phenotype_uid` int(10) unsigned NOT NULL,
  `term` varchar(255) NOT NULL DEFAULT 'N/A',
  `TO_number` varchar(45) NOT NULL,
  `definition` varchar(255) NOT NULL DEFAULT 'N/A',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`gramene_uid`),
  KEY `phenotype_uid` (`phenotype_uid`),
  KEY `fk_phenotype` (`phenotype_uid`),
  CONSTRAINT `fk_phenotype` FOREIGN KEY (`phenotype_uid`) REFERENCES `phenotypes` (`phenotype_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contains  trait ontology terms';
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=328 DEFAULT CHARSET=latin1 COMMENT='Signed in users for the THT database.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-04-04 11:01:11
