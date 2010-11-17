<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'markers'
 */
class markers
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $marker_uid = null;
  protected $unigene_uid = null;
  protected $marker_type_uid = null;
  protected $marker_name = null;
  protected $linkage_group = null;
  protected $access_id = null;
  protected $alias = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($marker_uid, $unigene_uid, $marker_type_uid, $marker_name, $linkage_group, $access_id, $alias, $updated_on, $created_on){
    $this->marker_uid = $marker_uid;
    $this->unigene_uid = $unigene_uid;
    $this->marker_type_uid = $marker_type_uid;
    $this->marker_name = $marker_name;
    $this->linkage_group = $linkage_group;
    $this->access_id = $access_id;
    $this->alias = $alias;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_marker_uid(){ return $this->marker_uid; }
  public function get_unigene_uid(){ return $this->unigene_uid; }
  public function get_marker_type_uid(){ return $this->marker_type_uid; }
  public function get_marker_name(){ return $this->marker_name; }
  public function get_linkage_group(){ return $this->linkage_group; }
  public function get_access_id(){ return $this->access_id; }
  public function get_alias(){ return $this->alias; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_marker_uid($arg0){ $this->marker_uid = $arg0; }
  public function set_unigene_uid($arg0){ $this->unigene_uid = $arg0; }
  public function set_marker_type_uid($arg0){ $this->marker_type_uid = $arg0; }
  public function set_marker_name($arg0){ $this->marker_name = $arg0; }
  public function set_linkage_group($arg0){ $this->linkage_group = $arg0; }
  public function set_access_id($arg0){ $this->access_id = $arg0; }
  public function set_alias($arg0){ $this->alias = $arg0; }
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