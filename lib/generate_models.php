<?php
/**
 * Automatically generates a primative but powerful
 * object model for the current database
 *
 * @author Gavin Monroe <gemonroe@iastate.edu>
 */
?>
<pre>
<?php

// working directories
define('MODELDIR', dirname(__FILE__).'/lib/model/');
define('MYMODELDIR', dirname(__FILE__).'/lib/my_model/');
define('PEERDIR', dirname(__FILE__).'/lib/peer/');
define('MYPEERDIR', dirname(__FILE__).'/lib/my_peer/');
define('INCLUDESDIR', dirname(__FILE__).'/includes/');

// connect to mysql
require_once('config.php');
include($config['root_dir'].'includes/bootstrap.inc');


connect();

/******************************************************************************/



$include_peers = "";

// get tables
$query_outer = mysql_query('show tables from '.$db_name, $link);
while ($table = mysql_fetch_row($query_outer)) {
  $table_name = $table[0];
  // get coulmns
  $vars = "";
  $getters = "";
  $setters = "";
  $column_names = array();
  $construct_args = array();
  $construct_body = "";
  $constructor_args = array();

  $peer_getters = "";
  $query_inner = mysql_query('show columns from '.$table_name, $link);
  while ($column = mysql_fetch_row($query_inner)) {
    $column_names[] = $column_name = $column[0];
    $construct_args[] = '$'.$column[0];
  	$vars .=    "  protected \$$column_name = null;\n";
  	$construct_body .= "    \$this->{$column_name} = \${$column_name};\n";
    $getters .= "  public function get_$column_name(){ return \$this->$column_name; }\n";
    $setters .= "  public function set_$column_name(\$arg0){ \$this->$column_name = \$arg0; }\n";

    $constructor_args[] = "\$row['{$column_name}']";

    //$constr .= "      \$temp->set_{$column_name}(\$row['$column_name']);\n";
    //$constr2 .= "    \$temp->set_{$column_name}(\$row['$column_name']);\n";
  }
  $constructor_args_string = implode(', ', $constructor_args);

  foreach($column_names as $column_name) {



    $peer_getters .= <<< EOF

  # auto-generated function
  public static function get_by_{$column_name}(\$arg0) {
    \$sql = self::\$base_sql.' where $column_name = \''.\$arg0.'\' limit 1';
    \$query = mysql_query(\$sql);
    if (mysql_num_rows(\$query) <= 0) return null;
    \$row = mysql_fetch_assoc(\$query);
    \$modelname = substr(__CLASS__, 0, -5);
    \$temp =& new \$modelname($constructor_args_string);
    return \$temp;
  }

  # auto-generated function
  public static function get_by_{$column_name}_array(array \$arg0) {
    if (empty(\$arg0)) return null;
    \$sql = self::\$base_sql.' where $column_name in ('.implode(',', \$arg0).')';
    \$query = mysql_query(\$sql);
    \$results = null;
    while (\$row = mysql_fetch_assoc(\$query)) {
      \$modelname = substr(__CLASS__, 0, -5);
      \$temp =& new \$modelname($constructor_args_string);
      \$results[] = \$temp;
    }
    return \$results;
  }

EOF;
  }


  $sql_fields_string = implode(', ', $column_names);
  $base_sql = "  protected static \$base_sql = 'select $sql_fields_string from $table_name';\n";
  $get_all = <<< EOF
  // auto-generated method
  // get all records from db
  public static function get_all() {
    \$results = array();
    \$query = mysql_query(self::\$base_sql);
    if (mysql_num_rows(\$query) <= 0) return \$results;
    while (\$row = mysql_fetch_assoc(\$query)) {
      \$modelname = substr(__CLASS__, 0, -5);
      \$results[] =& new \$modelname($constructor_args_string);
    }
    return \$results;
  }
EOF;

  $modeldir = MODELDIR;
  $peerdir = PEERDIR;
  $my_modeldir = MYMODELDIR;
  $my_peerdir = MYPEERDIR;
  $includes .= "include '{$modeldir}{$table_name}.php';\n";
  $includes .= "include '{$my_modeldir}my_{$table_name}.php';\n";
  $includes .= "include '{$peerdir}{$table_name}_peer.php';\n";
  $includes .= "include '{$my_peerdir}my_{$table_name}_peer.php';\n";

/******************************************************************************/
  $do_overwrite = true;
  $model_pathname = MODELDIR.$table_name.'.php';
  $model_contents = "";
//  if (file_exists($model_pathname)) {
//    $h = fopen($model_pathname, 'r');
//    if (filesize($model_pathname) > 0){
//      $model_contents = fread($h, filesize($model_pathname));
//      fclose($h);
//      if (preg_match('/\/\* begin-auto-gen \*\/.*\/\* end-auto-gen \*\//s', $model_contents)){
//        $do_overwrite = false;
//        $model_contents = preg_replace('/\/\* begin-auto-gen \*\/.*\/\* end-auto-gen \*\//s', '/* begin-auto-gen */'."\n".$vars."\n".$getters."\n".$setters."\n".'/* end-auto-gen */', $model_contents);
//      }
//    }
//  }
  if ($do_overwrite){
    $construct_args = implode(', ', $construct_args);

    $model_contents = <<< EOF
<?php
/**
 * Auto Generated Class
 * Represents a row from the table '{$table_name}'
 */
class {$table_name}
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
{$vars}

  public function __construct($construct_args){
{$construct_body}
  }

{$getters}

{$setters}

  public function copy_from()
  {
    return get_object_vars(\$this);
  }

  protected function copy_to(\$var_arr)
  {
    \$vars = get_class_vars(__CLASS__);
    foreach(\$vars as \$varname => \$value){
      \$this->\$varname = \$var_arr[\$varname];
    }
  }


  /* end-auto-gen */
}
EOF;
  }
  $h = fopen($model_pathname, 'w');
  fwrite($h, $model_contents);
  fclose($h);

  $my_model_pathname = MYMODELDIR.'my_'.$table_name.'.php';
  if (!file_exists($my_model_pathname))
  {
    $my_model_contents = <<<EOF
<?php
class my_{$table_name} extends {$table_name}
{
  # auto-generated constructor
  public function __construct(\$baseClassInstance)
  {
     \$this->copy_to(\$baseClassInstance->copy_from());
  }

  # your code here
}

EOF;
    $h = fopen($my_model_pathname, 'w');
    fwrite($h, $my_model_contents);
    fclose($h);
  }


/******************************************************************************/
  $do_overwrite = true;
  $peer_pathname = PEERDIR.$table_name.'_peer.php';
  $peer_contents = "";
//  if (file_exists($peer_pathname)) {
//    $h = fopen($peer_pathname, 'r');
//    if (filesize($peer_pathname) > 0){
//      $peer_contents = fread($h, filesize($peer_pathname));
//      fclose($h);
//      if (preg_match('/\/\* begin-auto-gen \*\/.*\/\* end-auto-gen \*\//s', $peer_contents)){
//        $do_overwrite = false;
//        $peer_contents = preg_replace('/\/\* begin-auto-gen \*\/.*\/\* end-auto-gen \*\//s', '/* begin-auto-gen */'."\n".$base_sql."\n".$get_all."\n".$peer_getters."\n".'/* end-auto-gen */', $peer_contents);
//      }
//    }
//  }
  if ($do_overwrite){
    $peer_contents = <<< EOF
<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table '{$table_name}'
 */
class {$table_name}_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  {$base_sql}

  {$get_all}

  {$peer_getters}
  /* end-auto-gen */
}
EOF;
  }

  $h = fopen($peer_pathname, 'w');
  fwrite($h, $peer_contents);
  fclose($h);

  $my_peer_pathname = MYPEERDIR.'my_'.$table_name.'_peer.php';
  if (!file_exists($my_peer_pathname))
  {
    $my_peer_contents = <<<EOF
<?php
class my_{$table_name}_peer extends {$table_name}_peer
{
  # your code here
}

EOF;
    $h = fopen($my_peer_pathname, 'w');
    fwrite($h, $my_peer_contents);
    fclose($h);
  }


  echo "+ {$table_name}.php\n+ {$table_name}_peer.php\n";

}
mysql_close($link);

/******************************************************************************/
$do_overwrite = true;
$model_inc_pathname = INCLUDESDIR.'model.inc';
if (file_exists($model_inc_pathname)){
  // open model.inc and get its contents
  $h = fopen($model_inc_pathname, 'r');
  if (filesize($model_inc_pathname)>0){
    $model_inc_contents = fread($h, filesize($model_inc_pathname));
    fclose($h);
    if (preg_match('/\/\* begin-auto-gen \*\/.*\/\* end-auto-gen \*\//s', $model_inc_contents)){
      $do_overwrite = false;
      $model_inc_contents = preg_replace('/\/\* begin-auto-gen \*\/.*\/\* end-auto-gen \*\//s', '/* begin-auto-gen */'."\n".$includes."\n".'/* end-auto-gen */', $model_inc_contents);
    }
  }
}
if ($do_overwrite){
  $model_inc_contents = <<< EOF
<?php
/**
 * IMPORTANT: Code with begin-auto-gen and end-auto-gen will be overwritten
 * every time the generate_models.php file is executed
 */

/* begin-auto-gen */
$includes
/* end-auto-gen */

EOF;
  $h = fopen($model_inc_pathname, 'w');
  fwrite($h, $model_inc_contents);
  fclose($h);
}
