<?php
/**
 * Download Gateway New
 *
 * PHP version 5.3
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/downloads.php
 *
 * The purpose of this script is to provide the user with an interface
 * for downloading certain kinds of files from T3.
 */

namespace T3;

set_time_limit(0);
ini_set('memory_limit', '3G');
$tmpdir = "/tmp/tht";

// For live website file
require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
set_include_path(GET_INCLUDE_PATH() . PATH_SEPARATOR . '../pear/');
date_default_timezone_set('America/Los_Angeles');
require_once $config['root_dir'].'includes/MIME/Type.php';

// connect to database
$mysqli = connecti();

require_once $config['root_dir'].'downloads/marker_filter.php';
require_once $config['root_dir'].'downloads/vcf_class.php';
require 'downloads_class.php';

if (isset($_GET['function'])) {
    new Downloads($_GET['function']);
} else {
    new Downloads("web");
}
