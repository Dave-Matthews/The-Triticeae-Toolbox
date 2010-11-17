<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'experiments'
 */
class experiments
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $experiment_uid = null;
  protected $experiment_type_uid = null;
  protected $datasets_uid = null;
  protected $experiment_name = null;
  protected $experiment_year = null;
  protected $planting_date = null;
  protected $seeding_rate = null;
  protected $harvest_date = null;
  protected $experiment_design = null;
  protected $number_replications = null;
  protected $plot_size = null;
  protected $harvest_area = null;
  protected $irrigation = null;
  protected $collect_site_name = null;
  protected $longitude = null;
  protected $latitude = null;
  protected $other_remarks = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($experiment_uid, $experiment_type_uid, $datasets_uid, $experiment_name, $experiment_year, $planting_date, $seeding_rate, $harvest_date, $experiment_design, $number_replications, $plot_size, $harvest_area, $irrigation, $collect_site_name, $longitude, $latitude, $other_remarks, $updated_on, $created_on){
    $this->experiment_uid = $experiment_uid;
    $this->experiment_type_uid = $experiment_type_uid;
    $this->datasets_uid = $datasets_uid;
    $this->experiment_name = $experiment_name;
    $this->experiment_year = $experiment_year;
    $this->planting_date = $planting_date;
    $this->seeding_rate = $seeding_rate;
    $this->harvest_date = $harvest_date;
    $this->experiment_design = $experiment_design;
    $this->number_replications = $number_replications;
    $this->plot_size = $plot_size;
    $this->harvest_area = $harvest_area;
    $this->irrigation = $irrigation;
    $this->collect_site_name = $collect_site_name;
    $this->longitude = $longitude;
    $this->latitude = $latitude;
    $this->other_remarks = $other_remarks;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_experiment_uid(){ return $this->experiment_uid; }
  public function get_experiment_type_uid(){ return $this->experiment_type_uid; }
  public function get_datasets_uid(){ return $this->datasets_uid; }
  public function get_experiment_name(){ return $this->experiment_name; }
  public function get_experiment_year(){ return $this->experiment_year; }
  public function get_planting_date(){ return $this->planting_date; }
  public function get_seeding_rate(){ return $this->seeding_rate; }
  public function get_harvest_date(){ return $this->harvest_date; }
  public function get_experiment_design(){ return $this->experiment_design; }
  public function get_number_replications(){ return $this->number_replications; }
  public function get_plot_size(){ return $this->plot_size; }
  public function get_harvest_area(){ return $this->harvest_area; }
  public function get_irrigation(){ return $this->irrigation; }
  public function get_collect_site_name(){ return $this->collect_site_name; }
  public function get_longitude(){ return $this->longitude; }
  public function get_latitude(){ return $this->latitude; }
  public function get_other_remarks(){ return $this->other_remarks; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_experiment_uid($arg0){ $this->experiment_uid = $arg0; }
  public function set_experiment_type_uid($arg0){ $this->experiment_type_uid = $arg0; }
  public function set_datasets_uid($arg0){ $this->datasets_uid = $arg0; }
  public function set_experiment_name($arg0){ $this->experiment_name = $arg0; }
  public function set_experiment_year($arg0){ $this->experiment_year = $arg0; }
  public function set_planting_date($arg0){ $this->planting_date = $arg0; }
  public function set_seeding_rate($arg0){ $this->seeding_rate = $arg0; }
  public function set_harvest_date($arg0){ $this->harvest_date = $arg0; }
  public function set_experiment_design($arg0){ $this->experiment_design = $arg0; }
  public function set_number_replications($arg0){ $this->number_replications = $arg0; }
  public function set_plot_size($arg0){ $this->plot_size = $arg0; }
  public function set_harvest_area($arg0){ $this->harvest_area = $arg0; }
  public function set_irrigation($arg0){ $this->irrigation = $arg0; }
  public function set_collect_site_name($arg0){ $this->collect_site_name = $arg0; }
  public function set_longitude($arg0){ $this->longitude = $arg0; }
  public function set_latitude($arg0){ $this->latitude = $arg0; }
  public function set_other_remarks($arg0){ $this->other_remarks = $arg0; }
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