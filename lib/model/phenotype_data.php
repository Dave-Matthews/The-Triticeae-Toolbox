<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'phenotype_data'
 */
class phenotype_data
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $phenotype_data_uid = null;
  protected $phenotype_uid = null;
  protected $tht_base_uid = null;
  protected $phenotype_data_name = null;
  protected $value = null;
  protected $recording_date = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($phenotype_data_uid, $phenotype_uid, $tht_base_uid, $phenotype_data_name, $value, $recording_date, $updated_on, $created_on){
    $this->phenotype_data_uid = $phenotype_data_uid;
    $this->phenotype_uid = $phenotype_uid;
    $this->tht_base_uid = $tht_base_uid;
    $this->phenotype_data_name = $phenotype_data_name;
    $this->value = $value;
    $this->recording_date = $recording_date;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_phenotype_data_uid(){ return $this->phenotype_data_uid; }
  public function get_phenotype_uid(){ return $this->phenotype_uid; }
  public function get_tht_base_uid(){ return $this->tht_base_uid; }
  public function get_phenotype_data_name(){ return $this->phenotype_data_name; }
  public function get_value(){ return $this->value; }
  public function get_recording_date(){ return $this->recording_date; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_phenotype_data_uid($arg0){ $this->phenotype_data_uid = $arg0; }
  public function set_phenotype_uid($arg0){ $this->phenotype_uid = $arg0; }
  public function set_tht_base_uid($arg0){ $this->tht_base_uid = $arg0; }
  public function set_phenotype_data_name($arg0){ $this->phenotype_data_name = $arg0; }
  public function set_value($arg0){ $this->value = $arg0; }
  public function set_recording_date($arg0){ $this->recording_date = $arg0; }
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