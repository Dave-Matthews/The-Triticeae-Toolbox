<?php
class my_datasets extends datasets
{
	# auto-generated constructor
	public function __construct($baseClassInstance)
	{
		$this->copy_to($baseClassInstance->copy_from());
		if (is_null($this->datasets_uid))
			$this->experiments = array();
		else
			$this->experiments = my_experiments_peer::get_by_datasets_uid_array(array($this->datasets_uid));
	}

	private $experiments = array();

	public function get_experiments(){return $this->experiments;}
	public function set_experiments(array $experiments){$this->experiments = $experiments;}

	public function save($breeding_programs_uid)
	{
		$dataset_name = (is_null($this->dataset_name)) ? 'NULL' : "'{$this->dataset_name}'";
		$description = (is_null($this->description)) ? 'NULL' : "'{$this->description}'";
		$date = (is_null($this->date)) ? 'NULL' : "'{$this->date}'";

		$sql = null;
		if (! is_null($this->datasets_uid)){
			$sql = "update datasets set	breeding_programs_uid = '$breeding_programs_uid', dataset_name = $dataset_name, description = $description where datasets_uid = '{$this->datasets_uid}' limit 1";
		}else{
			$sql = "insert into datasets set breeding_programs_uid = '$breeding_programs_uid', dataset_name = $dataset_name, description = $description, created_on = NOW()";
		}

		$query = mysql_query($sql) or die(mysql_error());
		$insert_id = mysql_insert_id($query);

		if ($insert_id != 0){
			$this->datasets_uid = $insert_id;
		}

		foreach($this->experiments as $experiment){
			$experiment->save($this->datasets_uid);
		}
	}
}
