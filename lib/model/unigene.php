<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'unigene'
 */
class unigene
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $unigene_uid = null;
  protected $unigene_name = null;
  protected $access_id = null;
  protected $synonyms = null;
  protected $gene_class = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($unigene_uid, $unigene_name, $access_id, $synonyms, $gene_class, $updated_on, $created_on){
    $this->unigene_uid = $unigene_uid;
    $this->unigene_name = $unigene_name;
    $this->access_id = $access_id;
    $this->synonyms = $synonyms;
    $this->gene_class = $gene_class;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_unigene_uid(){ return $this->unigene_uid; }
  public function get_unigene_name(){ return $this->unigene_name; }
  public function get_access_id(){ return $this->access_id; }
  public function get_synonyms(){ return $this->synonyms; }
  public function get_gene_class(){ return $this->gene_class; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_unigene_uid($arg0){ $this->unigene_uid = $arg0; }
  public function set_unigene_name($arg0){ $this->unigene_name = $arg0; }
  public function set_access_id($arg0){ $this->access_id = $arg0; }
  public function set_synonyms($arg0){ $this->synonyms = $arg0; }
  public function set_gene_class($arg0){ $this->gene_class = $arg0; }
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