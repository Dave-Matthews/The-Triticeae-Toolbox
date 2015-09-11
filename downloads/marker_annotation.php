<?php
/**
 * Download marker annotations
 *
 */

namespace T3;

require_once 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();

require 'marker_annotation_class.php';
new Downloads($_GET['function']);
