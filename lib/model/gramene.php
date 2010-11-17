<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'gramene'
 */
class gramene
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $gramene_uid = null;
  protected $phenotype_uid = null;
  protected $term = null;
  protected $definition = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($gramene_uid, $phenotype_uid, $term, $definition, $updated_on, $created_on){
    $this->gramene_uid = $gramene_uid;
    $this->phenotype_uid = $phenotype_uid;
    $this->term = $term;
    $this->definition = $definition;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_gramene_uid(){ return $this->gramene_uid; }
  public function get_phenotype_uid(){ return $this->phenotype_uid; }
  public function get_term(){ return $this->term; }
  public function get_definition(){ return $this->definition; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_gramene_uid($arg0){ $this->gramene_uid = $arg0; }
  public function set_phenotype_uid($arg0){ $this->phenotype_uid = $arg0; }
  public function set_term($arg0){ $this->term = $arg0; }
  public function set_definition($arg0){ $this->definition = $arg0; }
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