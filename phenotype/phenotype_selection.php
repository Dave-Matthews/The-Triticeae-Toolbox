<?php
/**
 * Download Gateway
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @author   Clay Birkett <cbirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 * 
 */

namespace T3;

require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';
require_once $config['root_dir'].'phenotype/phenotype_selection_class.php';

// connect to database
$mysqli = connecti();

new Downloads($_GET['function']);
