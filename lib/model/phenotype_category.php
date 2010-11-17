<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'phenotype_category'
 */
class phenotype_category
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $phenotype_category_uid = null;
  protected $phenotype_category_name = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($phenotype_category_uid, $phenotype_category_name, $updated_on, $created_on){
    $this->phenotype_category_uid = $phenotype_category_uid;
    $this->phenotype_category_name = $phenotype_category_name;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_phenotype_category_uid(){ return $this->phenotype_category_uid; }
  public function get_phenotype_category_name(){ return $this->phenotype_category_name; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_phenotype_category_uid($arg0){ $this->phenotype_category_uid = $arg0; }
  public function set_phenotype_category_name($arg0){ $this->phenotype_category_name = $arg0; }
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