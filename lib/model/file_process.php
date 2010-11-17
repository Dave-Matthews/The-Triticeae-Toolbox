<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'file_process'
 */
class file_process
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $file_process_uid = null;
  protected $file_name = null;
  protected $def_file_name = null;
  protected $dir_destination = null;
  protected $file_desc = null;
  protected $dataset_name = null;
  protected $process_program = null;
  protected $target_tables = null;
  protected $users_name = null;
  protected $process_result = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($file_process_uid, $file_name, $def_file_name, $dir_destination, $file_desc, $dataset_name, $process_program, $target_tables, $users_name, $process_result, $updated_on, $created_on){
    $this->file_process_uid = $file_process_uid;
    $this->file_name = $file_name;
    $this->def_file_name = $def_file_name;
    $this->dir_destination = $dir_destination;
    $this->file_desc = $file_desc;
    $this->dataset_name = $dataset_name;
    $this->process_program = $process_program;
    $this->target_tables = $target_tables;
    $this->users_name = $users_name;
    $this->process_result = $process_result;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_file_process_uid(){ return $this->file_process_uid; }
  public function get_file_name(){ return $this->file_name; }
  public function get_def_file_name(){ return $this->def_file_name; }
  public function get_dir_destination(){ return $this->dir_destination; }
  public function get_file_desc(){ return $this->file_desc; }
  public function get_dataset_name(){ return $this->dataset_name; }
  public function get_process_program(){ return $this->process_program; }
  public function get_target_tables(){ return $this->target_tables; }
  public function get_users_name(){ return $this->users_name; }
  public function get_process_result(){ return $this->process_result; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_file_process_uid($arg0){ $this->file_process_uid = $arg0; }
  public function set_file_name($arg0){ $this->file_name = $arg0; }
  public function set_def_file_name($arg0){ $this->def_file_name = $arg0; }
  public function set_dir_destination($arg0){ $this->dir_destination = $arg0; }
  public function set_file_desc($arg0){ $this->file_desc = $arg0; }
  public function set_dataset_name($arg0){ $this->dataset_name = $arg0; }
  public function set_process_program($arg0){ $this->process_program = $arg0; }
  public function set_target_tables($arg0){ $this->target_tables = $arg0; }
  public function set_users_name($arg0){ $this->users_name = $arg0; }
  public function set_process_result($arg0){ $this->process_result = $arg0; }
  public function set_updated_on($arg0){ $this->updated_on = $arg0; }
  public function set_created_on($arg0){ $this->created_on = $arg0; }


  public function copy_from()
  {
    return get_object_vars($this);
  }

  protected function copy_to($var_arr)
  {
    $vars = get_class_vars(__CLASS__);
    foreach($vars as $varname => $value){
      $this->$varname = $var_arr[$varname];
    }
  }


  /* end-auto-gen */
}