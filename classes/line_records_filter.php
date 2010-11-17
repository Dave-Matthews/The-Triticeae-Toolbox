<?php
// +---------------------------------------------------------------------------+
// | Line Record Filter                                                        |
// +---------------------------------------------------------------------------+
// | Authors:  Gavin Monroe <gemonroe@iastate.edu>                             |
// | Created:  4/14/2008                                                       |
// +---------------------------------------------------------------------------+
class LineRecordsFilter extends Filter
{
	
	private $allowed_sub_tables = array('line_records');
	
	public function __construct($identifier,
								$table_name,
								array $field_names = null,
								array $bounds = null)
	{
		parent::__construct($identifier, $table_name, $field_names, $bounds);
		$this->table_name = 'line_records';
	}
	
	//---
	//	Sets the specified filter as the child of this filter
	//---
	public function sub_filter(Filter $filter)
	{
		if (!in_array($filter->table_name, $this->allowed_sub_tables))
		{
			return false;
		}
		parent::sub_filter($filter);
		return true;
	}
	
	//---
	//	Returns the sql statements representing this filter and its children
	//---
	public function get_sql()
	{
		$where = $this->get_where();
		if ($this->sub_filter == null)
		{
			return "
				SELECT *\n
				FROM {$this->table_name} main\n
				WHERE $where\n
			";
		}
		else
		{
			$sub_filter_sql = $this->sub_filter->get_sql();
			$sub_filter_tbl = $this->sub_filter->table_name();
			return "
				SELECT *\n
				FROM ($sub_filter_sql) main\n
				WHERE $where\n
			";
		}
	}
	
	//---
	//	Returns the where part of this filter's sql statement
	//---
	public function get_where()
	{
		if (is_null($this->field_names))
		{
			return '1'; // WHERE 1
		}
		
		$first_field = array_shift($this->field_names);
		$first_bound = array_shift($this->bounds);
		$where = 'main.' . $first_field . ' ' . $first_bound;
		foreach ($this->field_names as $key => $cur_field)
		{
			$cur_bound = $this->bounds[$key];
			$where .= ' AND main.' . $cur_field . ' ' . $cur_bound;
		}
		return $where;
	}
	
}
