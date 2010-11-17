<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'tht_base'
 */
class tht_base
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $tht_base_uid = null;
  protected $line_record_uid = null;
  protected $experiment_uid = null;
  protected $tht_base_name = null;
  protected $number = null;
  protected $institution_id = null;
  protected $plant_passport = null;
  protected $donor_code = null;
  protected $donor_number = null;
  protected $acqdate = null;
  protected $collnumb = null;
  protected $colldate = null;
  protected $collcode = null;
  protected $duplsite = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($tht_base_uid, $line_record_uid, $experiment_uid, $tht_base_name, $number, $institution_id, $plant_passport, $donor_code, $donor_number, $acqdate, $collnumb, $colldate, $collcode, $duplsite, $updated_on, $created_on){
    $this->tht_base_uid = $tht_base_uid;
    $this->line_record_uid = $line_record_uid;
    $this->experiment_uid = $experiment_uid;
    $this->tht_base_name = $tht_base_name;
    $this->number = $number;
    $this->institution_id = $institution_id;
    $this->plant_passport = $plant_passport;
    $this->donor_code = $donor_code;
    $this->donor_number = $donor_number;
    $this->acqdate = $acqdate;
    $this->collnumb = $collnumb;
    $this->colldate = $colldate;
    $this->collcode = $collcode;
    $this->duplsite = $duplsite;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_tht_base_uid(){ return $this->tht_base_uid; }
  public function get_line_record_uid(){ return $this->line_record_uid; }
  public function get_experiment_uid(){ return $this->experiment_uid; }
  public function get_tht_base_name(){ return $this->tht_base_name; }
  public function get_number(){ return $this->number; }
  public function get_institution_id(){ return $this->institution_id; }
  public function get_plant_passport(){ return $this->plant_passport; }
  public function get_donor_code(){ return $this->donor_code; }
  public function get_donor_number(){ return $this->donor_number; }
  public function get_acqdate(){ return $this->acqdate; }
  public function get_collnumb(){ return $this->collnumb; }
  public function get_colldate(){ return $this->colldate; }
  public function get_collcode(){ return $this->collcode; }
  public function get_duplsite(){ return $this->duplsite; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_tht_base_uid($arg0){ $this->tht_base_uid = $arg0; }
  public function set_line_record_uid($arg0){ $this->line_record_uid = $arg0; }
  public function set_experiment_uid($arg0){ $this->experiment_uid = $arg0; }
  public function set_tht_base_name($arg0){ $this->tht_base_name = $arg0; }
  public function set_number($arg0){ $this->number = $arg0; }
  public function set_institution_id($arg0){ $this->institution_id = $arg0; }
  public function set_plant_passport($arg0){ $this->plant_passport = $arg0; }
  public function set_donor_code($arg0){ $this->donor_code = $arg0; }
  public function set_donor_number($arg0){ $this->donor_number = $arg0; }
  public function set_acqdate($arg0){ $this->acqdate = $arg0; }
  public function set_collnumb($arg0){ $this->collnumb = $arg0; }
  public function set_colldate($arg0){ $this->colldate = $arg0; }
  public function set_collcode($arg0){ $this->collcode = $arg0; }
  public function set_duplsite($arg0){ $this->duplsite = $arg0; }
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