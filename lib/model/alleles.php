<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'alleles'
 */
class alleles
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $allele_uid = null;
  protected $genotyping_data_uid = null;
  protected $value = null;
  protected $peak = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($allele_uid, $genotyping_data_uid, $value, $peak, $updated_on, $created_on){
    $this->allele_uid = $allele_uid;
    $this->genotyping_data_uid = $genotyping_data_uid;
    $this->value = $value;
    $this->peak = $peak;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_allele_uid(){ return $this->allele_uid; }
  public function get_genotyping_data_uid(){ return $this->genotyping_data_uid; }
  public function get_value(){ return $this->value; }
  public function get_peak(){ return $this->peak; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_allele_uid($arg0){ $this->allele_uid = $arg0; }
  public function set_genotyping_data_uid($arg0){ $this->genotyping_data_uid = $arg0; }
  public function set_value($arg0){ $this->value = $arg0; }
  public function set_peak($arg0){ $this->peak = $arg0; }
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