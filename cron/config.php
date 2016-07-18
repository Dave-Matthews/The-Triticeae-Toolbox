<?php
$root = "//" . $_SERVER['HTTP_HOST'];
$root .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
$config['base_url'] = "$root";
$config['base_url_ssl'] = "$root";
$config['root_dir'] = dirname(__FILE__).'/';

