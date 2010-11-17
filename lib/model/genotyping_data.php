<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'genotyping_data'
 */
class genotyping_data
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $genotyping_data_uid = null;
  protected $tht_base_uid = null;
  protected $marker_uid = null;
  protected $genotyping_status_uid = null;
  protected $genotyping_data_name = null;
  protected $masked = null;
  protected $set_number = null;
  protected $primer_forward_id = null;
  protected $primer_reverse_id = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($genotyping_data_uid, $tht_base_uid, $marker_uid, $genotyping_status_uid, $genotyping_data_name, $masked, $set_number, $primer_forward_id, $primer_reverse_id, $updated_on, $created_on){
    $this->genotyping_data_uid = $genotyping_data_uid;
    $this->tht_base_uid = $tht_base_uid;
    $this->marker_uid = $marker_uid;
    $this->genotyping_status_uid = $genotyping_status_uid;
    $this->genotyping_data_name = $genotyping_data_name;
    $this->masked = $masked;
    $this->set_number = $set_number;
    $this->primer_forward_id = $primer_forward_id;
    $this->primer_reverse_id = $primer_reverse_id;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_genotyping_data_uid(){ return $this->genotyping_data_uid; }
  public function get_tht_base_uid(){ return $this->tht_base_uid; }
  public function get_marker_uid(){ return $this->marker_uid; }
  public function get_genotyping_status_uid(){ return $this->genotyping_status_uid; }
  public function get_genotyping_data_name(){ return $this->genotyping_data_name; }
  public function get_masked(){ return $this->masked; }
  public function get_set_number(){ return $this->set_number; }
  public function get_primer_forward_id(){ return $this->primer_forward_id; }
  public function get_primer_reverse_id(){ return $this->primer_reverse_id; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_genotyping_data_uid($arg0){ $this->genotyping_data_uid = $arg0; }
  public function set_tht_base_uid($arg0){ $this->tht_base_uid = $arg0; }
  public function set_marker_uid($arg0){ $this->marker_uid = $arg0; }
  public function set_genotyping_status_uid($arg0){ $this->genotyping_status_uid = $arg0; }
  public function set_genotyping_data_name($arg0){ $this->genotyping_data_name = $arg0; }
  public function set_masked($arg0){ $this->masked = $arg0; }
  public function set_set_number($arg0){ $this->set_number = $arg0; }
  public function set_primer_forward_id($arg0){ $this->primer_forward_id = $arg0; }
  public function set_primer_reverse_id($arg0){ $this->primer_reverse_id = $arg0; }
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