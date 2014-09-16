<?php 
/**
 * Report for a single genotyping experiment.
 *
 * PHP version 5.3
 * 
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/display_genotype.php
 *
 */

namespace T3;

// dem 23mar12: Default 30 sec is too short for experiment 2011_9K_NB_allplates.
ini_set("max_execution_time", "300");
// dem 23mar12: Default 500M is too small for experiment 2011_9K_NB_allplates.
ini_set('memory_limit', '4096M');
require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
require_once 'Spreadsheet/Excel/Writer.php';
require 'display_genotype_class.php';
connect();

new ShowData($_REQUEST['function']);
