<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'taxa'
 */
class taxa
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $taxa_uid = null;
  protected $taxa_name = null;
  protected $scientific_name = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($taxa_uid, $taxa_name, $scientific_name, $updated_on, $created_on){
    $this->taxa_uid = $taxa_uid;
    $this->taxa_name = $taxa_name;
    $this->scientific_name = $scientific_name;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_taxa_uid(){ return $this->taxa_uid; }
  public function get_taxa_name(){ return $this->taxa_name; }
  public function get_scientific_name(){ return $this->scientific_name; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_taxa_uid($arg0){ $this->taxa_uid = $arg0; }
  public function set_taxa_name($arg0){ $this->taxa_name = $arg0; }
  public function set_scientific_name($arg0){ $this->scientific_name = $arg0; }
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