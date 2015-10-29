<?php
/**
 * Download Gateway New
 * 
 * PHP version 5.3
 * 
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/select_genotype.php
 */

namespace T3;

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require 'select_genotype_class.php';
$mysqli = connecti();

new SelectGenotypeExp($_GET['function']);
