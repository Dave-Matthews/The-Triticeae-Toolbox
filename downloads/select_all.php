<?php
/**
 * Download Gateway
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/select_all.php
 *
 */

namespace T3;

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'downloads/select_class.php';
$mysqli = connecti();

new SelectPhenotypeExp($_GET['function']);
