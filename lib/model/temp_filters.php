<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'temp_filters'
 */
class temp_filters
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $temp_filters_uid = null;
  protected $users_uid = null;
  protected $data = null;


  public function __construct($temp_filters_uid, $users_uid, $data){
    $this->temp_filters_uid = $temp_filters_uid;
    $this->users_uid = $users_uid;
    $this->data = $data;

  }

  public function get_temp_filters_uid(){ return $this->temp_filters_uid; }
  public function get_users_uid(){ return $this->users_uid; }
  public function get_data(){ return $this->data; }


  public function set_temp_filters_uid($arg0){ $this->temp_filters_uid = $arg0; }
  public function set_users_uid($arg0){ $this->users_uid = $arg0; }
  public function set_data($arg0){ $this->data = $arg0; }


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