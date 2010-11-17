<?php
/* phenotype_filter.php
 * @author Gavin Monroe <gemonroe@iastate.edu
 *********************************************/

/* Defines a filter on the 'phenotype_data' table
 *************************************************/
class ExperimentFilter extends Filter
{
    /* Subfilter restrictions
     **************************/
    private $allowed_sub_tables = array('experiments');
    public function sub_filter(Filter $filter)
    {
        if (!in_array($filter->table_name(), $this->allowed_sub_tables))
            return false;
        else{
            parent::sub_filter($filter);
            return true;
        }
    }
    
    public function __construct($identifier, $table_name, array $field_names = null, array $bounds = null)
    {
        parent::__construct($identifier, $table_name, $field_names, $bounds);
        $this->table_name = 'experiments';
    }
    
    public function get_sql()
    {
        if ($this->sub_filter == null)
        {
            $where = $this->get_where();
            $sql = <<<SQL
                SELECT
                    *
                FROM
                    {$this->table_name} main
                WHERE $where
SQL;
            return $sql;
        }
        else
        {
            $where = $this->get_where();
            $sub_filter_sql = $this->sub_filter->get_sql();
            $sub_filter_tbl = $this->sub_filter->table_name();
            
            
            $sql = <<<SQL
                SELECT
                    *
                FROM 
                    ($sub_filter_sql) main
                WHERE $where      
SQL;
            return $sql;
        }
    }
    
    public function get_where()
    {
        if (is_null($this->field_names))
            return "1";
        
        $first_field = array_shift($this->field_names);
        $first_bound = array_shift($this->bounds);
        $where = 'main.' . $first_field . ' ' . $first_bound;
        foreach($this->field_names as $key => $current_field)
        {
            $current_bound = $this->bounds[$key];
            $where .= ' AND main.' . $current_field . ' ' . $current_bound;
        }
        return $where;  
    }
}