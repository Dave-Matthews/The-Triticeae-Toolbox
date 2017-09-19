<?php
$root = str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
$pos1 = strripos($root, '/', -2);
$parent_dir = substr($root, 0, $pos1+1);//realpath("$root../");
$root = '//'.$_SERVER['HTTP_HOST']/*.'/'*/.strtolower(str_replace('//'.$_SERVER['HTTP_HOST'], '', $parent_dir));
$config['base_url'] = $root;//.'/';
$config['root_dir'] = realpath(dirname(__FILE__).'/../').'/';
$browserLink['IWGSC.36'] = "http://plants.ensembl.org/Triticum_aestivum/Location/View?r=";
$varLink['IWGSC.36'] = "http://plants.ensembl.org/Triticum_aestivum/Gene/Variation_Gene/Table";
$browserLink['RefSeq_v1'] = "https://triticeaetoolbox.org/jbrowse/?data=wheat2016&loc=";
$varLink['RefSeq_v1'] = "https://triticeaetoolbox.org/wheat/genotyping/variations_gene.php";
$ensemblLinkVEP = "http://plants.ensembl.org/Triticum_aestivum/Tools/VEP?db=core";
$polymarkerLink = "http://polymarker.tgac.ac.uk";
