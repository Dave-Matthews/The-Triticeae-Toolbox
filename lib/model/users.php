<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'users'
 */
class users
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $users_uid = null;
  protected $user_types_uid = null;
  protected $institutions_uid = null;
  protected $users_name = null;
  protected $pass = null;
  protected $name = null;
  protected $email = null;
  protected $lastaccess = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($users_uid, $user_types_uid, $institutions_uid, $users_name, $pass, $name, $email, $lastaccess, $updated_on, $created_on){
    $this->users_uid = $users_uid;
    $this->user_types_uid = $user_types_uid;
    $this->institutions_uid = $institutions_uid;
    $this->users_name = $users_name;
    $this->pass = $pass;
    $this->name = $name;
    $this->email = $email;
    $this->lastaccess = $lastaccess;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_users_uid(){ return $this->users_uid; }
  public function get_user_types_uid(){ return $this->user_types_uid; }
  public function get_institutions_uid(){ return $this->institutions_uid; }
  public function get_users_name(){ return $this->users_name; }
  public function get_pass(){ return $this->pass; }
  public function get_name(){ return $this->name; }
  public function get_email(){ return $this->email; }
  public function get_lastaccess(){ return $this->lastaccess; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_users_uid($arg0){ $this->users_uid = $arg0; }
  public function set_user_types_uid($arg0){ $this->user_types_uid = $arg0; }
  public function set_institutions_uid($arg0){ $this->institutions_uid = $arg0; }
  public function set_users_name($arg0){ $this->users_name = $arg0; }
  public function set_pass($arg0){ $this->pass = $arg0; }
  public function set_name($arg0){ $this->name = $arg0; }
  public function set_email($arg0){ $this->email = $arg0; }
  public function set_lastaccess($arg0){ $this->lastaccess = $arg0; }
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

  public function __toString(){
  	return $this->users_name;
  }
}