<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'line_records'
 */
class line_records
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $line_record_uid = null;
  protected $barley_pedigree_catalog_uid = null;
  protected $taxa_uid = null;
  protected $line_record_name = null;
  protected $synonym = null;
  protected $other_number = null;
  protected $variety = null;
  protected $pedigree_string = null;
  protected $barley_type = null;
  protected $origin = null;
  protected $row_type = null;
  protected $primary_end_use = null;
  protected $record_status = null;
  protected $breed_year = null;
  protected $note = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($line_record_uid, $barley_pedigree_catalog_uid, $taxa_uid, $line_record_name, $synonym, $other_number, $variety, $pedigree_string, $barley_type, $origin, $row_type, $primary_end_use, $record_status, $breed_year, $note, $updated_on, $created_on){
    $this->line_record_uid = $line_record_uid;
    $this->barley_pedigree_catalog_uid = $barley_pedigree_catalog_uid;
    $this->taxa_uid = $taxa_uid;
    $this->line_record_name = $line_record_name;
    $this->synonym = $synonym;
    $this->other_number = $other_number;
    $this->variety = $variety;
    $this->pedigree_string = $pedigree_string;
    $this->barley_type = $barley_type;
    $this->origin = $origin;
    $this->row_type = $row_type;
    $this->primary_end_use = $primary_end_use;
    $this->record_status = $record_status;
    $this->breed_year = $breed_year;
    $this->note = $note;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_line_record_uid(){ return $this->line_record_uid; }
  public function get_barley_pedigree_catalog_uid(){ return $this->barley_pedigree_catalog_uid; }
  public function get_taxa_uid(){ return $this->taxa_uid; }
  public function get_line_record_name(){ return $this->line_record_name; }
  public function get_synonym(){ return $this->synonym; }
  public function get_other_number(){ return $this->other_number; }
  public function get_variety(){ return $this->variety; }
  public function get_pedigree_string(){ return $this->pedigree_string; }
  public function get_barley_type(){ return $this->barley_type; }
  public function get_origin(){ return $this->origin; }
  public function get_row_type(){ return $this->row_type; }
  public function get_primary_end_use(){ return $this->primary_end_use; }
  public function get_record_status(){ return $this->record_status; }
  public function get_breed_year(){ return $this->breed_year; }
  public function get_note(){ return $this->note; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_line_record_uid($arg0){ $this->line_record_uid = $arg0; }
  public function set_barley_pedigree_catalog_uid($arg0){ $this->barley_pedigree_catalog_uid = $arg0; }
  public function set_taxa_uid($arg0){ $this->taxa_uid = $arg0; }
  public function set_line_record_name($arg0){ $this->line_record_name = $arg0; }
  public function set_synonym($arg0){ $this->synonym = $arg0; }
  public function set_other_number($arg0){ $this->other_number = $arg0; }
  public function set_variety($arg0){ $this->variety = $arg0; }
  public function set_pedigree_string($arg0){ $this->pedigree_string = $arg0; }
  public function set_barley_type($arg0){ $this->barley_type = $arg0; }
  public function set_origin($arg0){ $this->origin = $arg0; }
  public function set_row_type($arg0){ $this->row_type = $arg0; }
  public function set_primary_end_use($arg0){ $this->primary_end_use = $arg0; }
  public function set_record_status($arg0){ $this->record_status = $arg0; }
  public function set_breed_year($arg0){ $this->breed_year = $arg0; }
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