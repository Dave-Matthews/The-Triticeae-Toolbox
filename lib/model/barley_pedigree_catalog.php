<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'barley_pedigree_catalog'
 */
class barley_pedigree_catalog
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $barley_pedigree_catalog_uid = null;
  protected $barley_pedigree_catalog_name = null;
  protected $vurv_num = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($barley_pedigree_catalog_uid, $barley_pedigree_catalog_name, $vurv_num, $updated_on, $created_on){
    $this->barley_pedigree_catalog_uid = $barley_pedigree_catalog_uid;
    $this->barley_pedigree_catalog_name = $barley_pedigree_catalog_name;
    $this->vurv_num = $vurv_num;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_barley_pedigree_catalog_uid(){ return $this->barley_pedigree_catalog_uid; }
  public function get_barley_pedigree_catalog_name(){ return $this->barley_pedigree_catalog_name; }
  public function get_vurv_num(){ return $this->vurv_num; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_barley_pedigree_catalog_uid($arg0){ $this->barley_pedigree_catalog_uid = $arg0; }
  public function set_barley_pedigree_catalog_name($arg0){ $this->barley_pedigree_catalog_name = $arg0; }
  public function set_vurv_num($arg0){ $this->vurv_num = $arg0; }
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