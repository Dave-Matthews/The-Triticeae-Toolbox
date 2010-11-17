<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'institutions'
 */
class institutions
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $institutions_uid = null;
  protected $institutions_name = null;
  protected $institution_code = null;
  protected $institute_acronym = null;
  protected $institute_address = null;
  protected $phone = null;
  protected $email = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($institutions_uid, $institutions_name, $institution_code, $institute_acronym, $institute_address, $phone, $email, $updated_on, $created_on){
    $this->institutions_uid = $institutions_uid;
    $this->institutions_name = $institutions_name;
    $this->institution_code = $institution_code;
    $this->institute_acronym = $institute_acronym;
    $this->institute_address = $institute_address;
    $this->phone = $phone;
    $this->email = $email;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_institutions_uid(){ return $this->institutions_uid; }
  public function get_institutions_name(){ return $this->institutions_name; }
  public function get_institution_code(){ return $this->institution_code; }
  public function get_institute_acronym(){ return $this->institute_acronym; }
  public function get_institute_address(){ return $this->institute_address; }
  public function get_phone(){ return $this->phone; }
  public function get_email(){ return $this->email; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_institutions_uid($arg0){ $this->institutions_uid = $arg0; }
  public function set_institutions_name($arg0){ $this->institutions_name = $arg0; }
  public function set_institution_code($arg0){ $this->institution_code = $arg0; }
  public function set_institute_acronym($arg0){ $this->institute_acronym = $arg0; }
  public function set_institute_address($arg0){ $this->institute_address = $arg0; }
  public function set_phone($arg0){ $this->phone = $arg0; }
  public function set_email($arg0){ $this->email = $arg0; }
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