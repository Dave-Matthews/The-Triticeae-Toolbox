<?php
/**
 * Display Map information and save selection in session variable
 *
 * PHP version 5.3
 * jQuery
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/maps/select_map.php
 */

namespace T3;

require_once 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();
set_time_limit(120);
ini_set('memory_limit', '4G');
require_once 'select_map_class.php';

new Maps($_GET['function']);
