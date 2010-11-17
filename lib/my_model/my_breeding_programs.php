<?php
class my_breeding_programs extends breeding_programs
{
	# auto-generated constructor
	public function __construct($baseClassInstance)
	{
		$this->copy_to($baseClassInstance->copy_from());
		$this->datasets = datasets_peer::get_by_breeding_programs_uid_array(array($this->breeding_programs_uid));
	}

	# your code here
	/**
	* An array of datasets associated with this breeding program
	*
	* @var array
	*/
	private $datasets = array();

	/**
	* Returns the datasets associated with this breeding program
	*
	* @return datasets the datasets associated with this breeding program
	*/
	public function get_datasets()
	{
		return $this->datasets;
	}

	/**
	* Associates an array of datasets with this breeding program
	*
	* @param datasets $datasets the datasets to associate with this breeding program
	*/
	public function set_datasets($datasets)
	{
		$this->datasets = $datasets;
	}

	/**
	* Stores/Updates this line in the database
	*
	* @return boolean true on success
	*/
	public function save()
	{
		$institutions_uid = (is_null($this->institutions_uid)) ? 'NULL' : "'$this->institutions_uid'";
		$breeding_programs_name = (is_null($this->breeding_programs_name)) ? 'NULL' : "'$this->breeding_programs_name'";
		$description = (is_null($this->description)) ? 'NULL' : "'$this->description'";

		if (! is_null($this->$breeding_programs_uid)){
			$sql = "update breeding_programs set institutions_uid = $institutions_uid, breeding_programs_name = $breeding_programs_name, description = $description where breeding_programs_uid = '{$this->$breeding_programs_uid}' limit 1";
		}else{
			$sql = "insert into	breeding_programs set institutions_uid = $institutions_uid, breeding_programs_name = $breeding_programs_name, description = $description, created_on = NOW()";
		}

		$query = mysql_query($sql) or die(mysql_error());
		$insert_id = mysql_insert_id($query);

		if ($insert_id != 0){
			$this->breeding_programs_uid = $insert_id;
		}

		foreach ($this->datasets as &$dataset){
			$dataset->save($this->breeding_programs_uid);
		}
	}
}
?>