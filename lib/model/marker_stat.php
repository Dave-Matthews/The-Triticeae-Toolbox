<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'marker_stat'
 */
class marker_stat
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $marker_stat_uid = null;
  protected $datasets_uid = null;
  protected $marker_uid = null;
  protected $aa_freq = null;
  protected $ab_freq = null;
  protected $bb_freq = null;
  protected $gentrain_score = null;
  protected $note = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($marker_stat_uid, $datasets_uid, $marker_uid, $aa_freq, $ab_freq, $bb_freq, $gentrain_score, $note, $updated_on, $created_on){
    $this->marker_stat_uid = $marker_stat_uid;
    $this->datasets_uid = $datasets_uid;
    $this->marker_uid = $marker_uid;
    $this->aa_freq = $aa_freq;
    $this->ab_freq = $ab_freq;
    $this->bb_freq = $bb_freq;
    $this->gentrain_score = $gentrain_score;
    $this->note = $note;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_marker_stat_uid(){ return $this->marker_stat_uid; }
  public function get_datasets_uid(){ return $this->datasets_uid; }
  public function get_marker_uid(){ return $this->marker_uid; }
  public function get_aa_freq(){ return $this->aa_freq; }
  public function get_ab_freq(){ return $this->ab_freq; }
  public function get_bb_freq(){ return $this->bb_freq; }
  public function get_gentrain_score(){ return $this->gentrain_score; }
  public function get_note(){ return $this->note; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_marker_stat_uid($arg0){ $this->marker_stat_uid = $arg0; }
  public function set_datasets_uid($arg0){ $this->datasets_uid = $arg0; }
  public function set_marker_uid($arg0){ $this->marker_uid = $arg0; }
  public function set_aa_freq($arg0){ $this->aa_freq = $arg0; }
  public function set_ab_freq($arg0){ $this->ab_freq = $arg0; }
  public function set_bb_freq($arg0){ $this->bb_freq = $arg0; }
  public function set_gentrain_score($arg0){ $this->gentrain_score = $arg0; }
  public function set_note($arg0){ $this->note = $arg0; }
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