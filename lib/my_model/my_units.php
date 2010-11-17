<?php
class my_units extends units
{
	# auto-generated constructor
	public function __construct($baseClassInstance)
	{
		$this->copy_to($baseClassInstance->copy_from());
		if (!is_null($this->unit_uid))
			$this->phenotypes = my_phenotypes_peer::get_by_unit_uid_array(array($this->unit_uid));
	}

	# your code here
	private $phenotypes = array();

	public function get_phenotypes()
	{
		return $this->phenotypes;
	}
	
	public function set_phenotypes(array $phenotypes)
	{
		$this->phenotypes = $phenotypes;
	}
	
	public function add_phenotype($phenotype)
	{
		if (is_null($phenotype)) return false;
		if ($this->containsPhenotypeByName($phenotype->get_phenotypes_name())) return false;
		$this->phenotypes[] = $phenotype;
		return true;
	}
	
	private function containsPhenotypeByName($name)
	{
		if (is_null($name)) return FALSE;
		if (is_null($this->phenotypes) || empty($this->phenotypes)) return FALSE;
		foreach($this->phenotypes as $phenotype)
		{
			if (strcasecmp($phenotype->get_phenotypes_name(), $name) == 0)
			return TRUE;
		}
		return FALSE;
	}
	
	public function weakEquals($other)
	{
		if (is_null($other)) return false;
		if (get_class($other) != get_class($this)) return false;
		if (strcasecmp($this->unit_name, $other->get_unit_name()) != 0) return false;
		return true;
	}
}
