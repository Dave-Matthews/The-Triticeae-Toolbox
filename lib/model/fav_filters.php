<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'fav_filters'
 */
class fav_filters
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $fav_filters_uid = null;
  protected $users_uid = null;
  protected $to_string = null;
  protected $name = null;


  public function __construct($fav_filters_uid, $users_uid, $to_string, $name){
    $this->fav_filters_uid = $fav_filters_uid;
    $this->users_uid = $users_uid;
    $this->to_string = $to_string;
    $this->name = $name;

  }

  public function get_fav_filters_uid(){ return $this->fav_filters_uid; }
  public function get_users_uid(){ return $this->users_uid; }
  public function get_to_string(){ return $this->to_string; }
  public function get_name(){ return $this->name; }


  public function set_fav_filters_uid($arg0){ $this->fav_filters_uid = $arg0; }
  public function set_users_uid($arg0){ $this->users_uid = $arg0; }
  public function set_to_string($arg0){ $this->to_string = $arg0; }
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