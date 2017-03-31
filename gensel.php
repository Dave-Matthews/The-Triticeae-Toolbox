<?php

namespace T3;

/**
 * Download Gateway New
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/downloads.php
 *
 * The purpose of this script is to provide the user with an interface
 * for downloading certain kinds of files from T3.
 */

set_time_limit(0);
ini_set('memory_limit', '3G');

// For live website file
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'downloads/marker_filter.php';
require $config['root_dir'].'gensel_class.php';

// connect to database
$mysqli = connecti();

if (isset($_GET['function'])) {
    new Downloads($_GET['function']);
} else {
    new Downloads('web');
}
