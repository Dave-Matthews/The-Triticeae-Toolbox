<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'datasets_experiments'
 */
class datasets_experiments
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $datasets_experiments_uid = null;
  protected $experiment_uid = null;
  protected $datasets_uid = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($datasets_experiments_uid, $experiment_uid, $datasets_uid, $updated_on, $created_on){
    $this->datasets_experiments_uid = $datasets_experiments_uid;
    $this->experiment_uid = $experiment_uid;
    $this->datasets_uid = $datasets_uid;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_datasets_experiments_uid(){ return $this->datasets_experiments_uid; }
  public function get_experiment_uid(){ return $this->experiment_uid; }
  public function get_datasets_uid(){ return $this->datasets_uid; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_datasets_experiments_uid($arg0){ $this->datasets_experiments_uid = $arg0; }
  public function set_experiment_uid($arg0){ $this->experiment_uid = $arg0; }
  public function set_datasets_uid($arg0){ $this->datasets_uid = $arg0; }
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