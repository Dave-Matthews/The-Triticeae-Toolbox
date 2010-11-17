<?php

class my_line_records extends line_records
{
	# auto-generated constructor
	public function __construct($baseClassInstance)
	{
		$this->copy_to($baseClassInstance->copy_from());
		$this->phenotype_datas = array();
	}

	private $phenotype_datas;
	public function get_phenotype_datas(){return $this->phenotype_datas;}
	
	/**
	 * Associates a phenotype_data object with this line
	 */
	public function add_phenotype_data(&$phenotype_data)
	{
		$this->phenotype_datas[] = $phenotype_data;
		return true;
	}
	public function set_phenotype_datas(&$phenotype_datas){$this->phenotype_datas=$phenotype_datas;}

}

