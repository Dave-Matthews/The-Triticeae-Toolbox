<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'phenotypes'
 */
class phenotypes
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $phenotype_uid = null;
  protected $unit_uid = null;
  protected $phenotype_category_uid = null;
  protected $phenotypes_name = null;
  protected $short_name = null;
  protected $description = null;
  protected $datatype = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($phenotype_uid, $unit_uid, $phenotype_category_uid, $phenotypes_name, $short_name, $description, $datatype, $updated_on, $created_on){
    $this->phenotype_uid = $phenotype_uid;
    $this->unit_uid = $unit_uid;
    $this->phenotype_category_uid = $phenotype_category_uid;
    $this->phenotypes_name = $phenotypes_name;
    $this->short_name = $short_name;
    $this->description = $description;
    $this->datatype = $datatype;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_phenotype_uid(){ return $this->phenotype_uid; }
  public function get_unit_uid(){ return $this->unit_uid; }
  public function get_phenotype_category_uid(){ return $this->phenotype_category_uid; }
  public function get_phenotypes_name(){ return $this->phenotypes_name; }
  public function get_short_name(){ return $this->short_name; }
  public function get_description(){ return $this->description; }
  public function get_datatype(){ return $this->datatype; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_phenotype_uid($arg0){ $this->phenotype_uid = $arg0; }
  public function set_unit_uid($arg0){ $this->unit_uid = $arg0; }
  public function set_phenotype_category_uid($arg0){ $this->phenotype_category_uid = $arg0; }
  public function set_phenotypes_name($arg0){ $this->phenotypes_name = $arg0; }
  public function set_short_name($arg0){ $this->short_name = $arg0; }
  public function set_description($arg0){ $this->description = $arg0; }
  public function set_datatype($arg0){ $this->datatype = $arg0; }
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