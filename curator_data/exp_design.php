<?php
/**
 * Download Gateway New
 *
 * PHP version 5.3
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/exp_design.php
 *
 */

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
require $config['root_dir'] . 'lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
require $config['root_dir'] . 'lib/phpRW.php';
require 'exp_design_class.php';
$mysqli = connecti();

new Fieldbook($_REQUEST['function']);
