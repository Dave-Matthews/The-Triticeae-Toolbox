<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'phenotype_descstat'
 */
class phenotype_descstat
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $phenotype_descstat_uid = null;
  protected $phenotype_uid = null;
  protected $mean_val = null;
  protected $max_val = null;
  protected $min_val = null;
  protected $sample_size = null;
  protected $std = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($phenotype_descstat_uid, $phenotype_uid, $mean_val, $max_val, $min_val, $sample_size, $std, $updated_on, $created_on){
    $this->phenotype_descstat_uid = $phenotype_descstat_uid;
    $this->phenotype_uid = $phenotype_uid;
    $this->mean_val = $mean_val;
    $this->max_val = $max_val;
    $this->min_val = $min_val;
    $this->sample_size = $sample_size;
    $this->std = $std;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_phenotype_descstat_uid(){ return $this->phenotype_descstat_uid; }
  public function get_phenotype_uid(){ return $this->phenotype_uid; }
  public function get_mean_val(){ return $this->mean_val; }
  public function get_max_val(){ return $this->max_val; }
  public function get_min_val(){ return $this->min_val; }
  public function get_sample_size(){ return $this->sample_size; }
  public function get_std(){ return $this->std; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_phenotype_descstat_uid($arg0){ $this->phenotype_descstat_uid = $arg0; }
  public function set_phenotype_uid($arg0){ $this->phenotype_uid = $arg0; }
  public function set_mean_val($arg0){ $this->mean_val = $arg0; }
  public function set_max_val($arg0){ $this->max_val = $arg0; }
  public function set_min_val($arg0){ $this->min_val = $arg0; }
  public function set_sample_size($arg0){ $this->sample_size = $arg0; }
  public function set_std($arg0){ $this->std = $arg0; }
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