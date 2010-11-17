<?php
class my_experiments extends experiments
{
	# auto-generated constructor
 	public function __construct($baseClassInstance){
		$this->copy_to($baseClassInstance->copy_from());
		$this->lines = array();
		$this->tht_bases = array();
		if(!is_null($this->experiment_uid)){
			$this->tht_bases = my_tht_base_peer::get_by_experiment_uid_array(array($this->experiment_uid));
		}
		if(!is_null( $this->tht_bases ) && !empty($this->tht_bases)){
			$line_record_uid = null;
			foreach($this->tht_bases as &$tht_base){
				$line_record_uid = $tht_base->get_line_record_uid();
				$this->lines[$tht_base->get_tht_base_uid()] = my_line_records_peer::get_by_line_record_uid($line_record_uid);
			}
		}
	}

 	# your code here
	private $tht_bases = null;
 	private $lines = null;	// lines associated with this experiment
	/**
	 * Returns the lines associated with this experiment
	 */
 	public function get_lines(){return $this->lines;}

	/**
	 * Associates a line with this experiment
	 */
 	public function add_line($line, $allowDuplicates = false){
 		if (!$allowDuplicates && !$this->containsLineByName($line)){
 			$this->lines[] = $line;
 			return true;
 		}
 		return false;
 	}

	/**
	 * Associates the specified lines with this experiment overwritting the previous association
	 */
 	public function set_lines(&$lines){
		$this->lines = $lines;
	}

	/**
	 * Returns true if this experiment contains a line with the same name as the specified line
	 */
	public function containsLineByName(&$otherLine){
		if ($otherLine == null) return false;
		if (is_null($this->lines) || empty($this->lines)) return false;
		foreach($this->lines as &$line){
			if ( ! is_null($line) && $line->get_line_record_name() == $otherLine->get_line_record_name() )
				return true;
		}
		return false;
	}

	/**
	 * Saves this object and all of its related children
	 *
	 * @param integer $datasets_uid the parent dataset
	 */
	public function save($datasets_uid){

		$experimentName = (is_null($this->experiment_name)) ? 'NULL' : "'{$this->experiment_name}'";

		$sql = null;
		if(!is_null($this->experiment_uid)){
			$sql = "update experiments set experiment_name = $experimentName where experiment_uid = '{$this->experiment_uid}' limit 1";
		}else{
			$sql = "insert into experiments experiment_name = $experimentName, created_on = NOW()";
		}

		$query = mysql_query($sql) or die(mysql_error());
		$insert_id = mysql_insert_id($query);

		if ($insert_id != 0)
			$this->experiment_uid = $insert_id;
	}

	/*public function attach_phenotype_data(){
		foreach($this->tht_bases as &$tht_base){
			$phenotype_datas = my_phenotype_data_peer::get_by_tht_base_uid_array(array($tht_base->get_tht_base_uid()));
			if (isset($this->lines[$tht_base->get_tht_base_uid()])) {
				$this->lines[$tht_base->get_tht_base_uid()]->set_phenotype_data($phenotype_datas);
			}
		}
	}*/

}
