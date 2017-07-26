<?php
$root = str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
$pos1 = strripos($root, '/', -2);
$parent_dir = substr($root, 0, $pos1+1);//realpath("$root../");
$root = '//'.$_SERVER['HTTP_HOST']/*.'/'*/.strtolower(str_replace('//'.$_SERVER['HTTP_HOST'], '', $parent_dir));
$config['base_url'] = $root;//.'/';
$config['root_dir'] = realpath(dirname(__FILE__).'/../').'/';
$ensemblLink = "http://plants.ensembl.org/Triticum_aestivum";
