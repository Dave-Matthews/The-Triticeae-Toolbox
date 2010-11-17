<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'filters'
 */
class filters
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $filter_uid = null;
  protected $users_uid = null;
  protected $filter_set_uid = null;
  protected $data = null;


  public function __construct($filter_uid, $users_uid, $filter_set_uid, $data){
    $this->filter_uid = $filter_uid;
    $this->users_uid = $users_uid;
    $this->filter_set_uid = $filter_set_uid;
    $this->data = $data;

  }

  public function get_filter_uid(){ return $this->filter_uid; }
  public function get_users_uid(){ return $this->users_uid; }
  public function get_filter_set_uid(){ return $this->filter_set_uid; }
  public function get_data(){ return $this->data; }


  public function set_filter_uid($arg0){ $this->filter_uid = $arg0; }
  public function set_users_uid($arg0){ $this->users_uid = $arg0; }
  public function set_filter_set_uid($arg0){ $this->filter_set_uid = $arg0; }
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