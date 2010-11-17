<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'filter_sets'
 */
class filter_sets
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $filter_set_uid = null;
  protected $users_uid = null;
  protected $name = null;


  public function __construct($filter_set_uid, $users_uid, $name){
    $this->filter_set_uid = $filter_set_uid;
    $this->users_uid = $users_uid;
    $this->name = $name;

  }

  public function get_filter_set_uid(){ return $this->filter_set_uid; }
  public function get_users_uid(){ return $this->users_uid; }
  public function get_name(){ return $this->name; }


  public function set_filter_set_uid($arg0){ $this->filter_set_uid = $arg0; }
  public function set_users_uid($arg0){ $this->users_uid = $arg0; }
  public function set_name($arg0){ $this->name = $arg0; }


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