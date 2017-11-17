<?php

namespace T3;

set_time_limit(0);
require 'config.php';
$pageTitle = "GWAS Results";
require_once $config['root_dir'].'includes/bootstrap.inc';
require_once $config['root_dir'].'qtl/qtl_report_class.php';

// connect to database
$mysqli = connecti();

new Downloads($_GET['function']);
