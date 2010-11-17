<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'pedigree_relations'
 */
class pedigree_relations
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $pedigree_relation_uid = null;
  protected $line_record_uid = null;
  protected $parent_id = null;
  protected $relation = null;
  protected $contribution = null;
  protected $selfing = null;
  protected $comments = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($pedigree_relation_uid, $line_record_uid, $parent_id, $relation, $contribution, $selfing, $comments, $updated_on, $created_on){
    $this->pedigree_relation_uid = $pedigree_relation_uid;
    $this->line_record_uid = $line_record_uid;
    $this->parent_id = $parent_id;
    $this->relation = $relation;
    $this->contribution = $contribution;
    $this->selfing = $selfing;
    $this->comments = $comments;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_pedigree_relation_uid(){ return $this->pedigree_relation_uid; }
  public function get_line_record_uid(){ return $this->line_record_uid; }
  public function get_parent_id(){ return $this->parent_id; }
  public function get_relation(){ return $this->relation; }
  public function get_contribution(){ return $this->contribution; }
  public function get_selfing(){ return $this->selfing; }
  public function get_comments(){ return $this->comments; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_pedigree_relation_uid($arg0){ $this->pedigree_relation_uid = $arg0; }
  public function set_line_record_uid($arg0){ $this->line_record_uid = $arg0; }
  public function set_parent_id($arg0){ $this->parent_id = $arg0; }
  public function set_relation($arg0){ $this->relation = $arg0; }
  public function set_contribution($arg0){ $this->contribution = $arg0; }
  public function set_selfing($arg0){ $this->selfing = $arg0; }
  public function set_comments($arg0){ $this->comments = $arg0; }
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