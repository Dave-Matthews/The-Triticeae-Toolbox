<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'session_variables'
 */
class session_variables
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $session_variables_uid = null;
  protected $user_name = null;
  protected $session_variables_name = null;
  protected $serialized_values = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($session_variables_uid, $user_name, $session_variables_name, $serialized_values, $updated_on, $created_on){
    $this->session_variables_uid = $session_variables_uid;
    $this->user_name = $user_name;
    $this->session_variables_name = $session_variables_name;
    $this->serialized_values = $serialized_values;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_session_variables_uid(){ return $this->session_variables_uid; }
  public function get_user_name(){ return $this->user_name; }
  public function get_session_variables_name(){ return $this->session_variables_name; }
  public function get_serialized_values(){ return $this->serialized_values; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_session_variables_uid($arg0){ $this->session_variables_uid = $arg0; }
  public function set_user_name($arg0){ $this->user_name = $arg0; }
  public function set_session_variables_name($arg0){ $this->session_variables_name = $arg0; }
  public function set_serialized_values($arg0){ $this->serialized_values = $arg0; }
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