<?php
/**
 * Represents a filter on the 'phenotype_data' table
 *
 * @author Gavin Monroe
 */
class PhenotypeFilter extends Filter
{
    // sub filter restrictions
    private $allowed_sub_tables = array('experiments', 'phenotypes', 'line_records', 'phenotype_data');
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
        $this->table_name = 'phenotype_data';
    }

    public function get_sql()
    {
        if ($this->sub_filter == null) // this filter doesn't have a sub filter
        {
            $where = $this->get_where();
            $sql = <<<SQL
                SELECT
                    {$this->as_mappings['line_records']}.line_record_uid,
                    {$this->as_mappings['line_records']}.line_record_name,
                    {$this->as_mappings['experiments']}.experiment_name,
                    {$this->as_mappings['experiments']}.experiment_year,
					{$this->as_mappings['experiments']}.number_replications,
                    {$this->as_mappings['units']}.unit_name AS unit_name_{$this->identifier},
                    {$this->as_mappings['phenotypes']}.phenotype_uid AS phenotype_uid_{$this->identifier},
                    {$this->as_mappings['phenotypes']}.phenotypes_name AS name_{$this->identifier},
                    main.tht_base_uid,
                    main.value AS value_{$this->identifier}
                FROM phenotype_data main
                JOIN (tht_base {$this->as_mappings['tht_base']},
                    experiments {$this->as_mappings['experiments']},
                    phenotypes {$this->as_mappings['phenotypes']},
                    units {$this->as_mappings['units']},
                    line_records {$this->as_mappings['line_records']})
                ON(
                    {$this->as_mappings['tht_base']}.tht_base_uid = main.tht_base_uid
                    AND {$this->as_mappings['experiments']}.experiment_uid = {$this->as_mappings['tht_base']}.experiment_uid
                    AND {$this->as_mappings['phenotypes']}.phenotype_uid = main.phenotype_uid
                    AND {$this->as_mappings['units']}.unit_uid = {$this->as_mappings['phenotypes']}.unit_uid
                    AND {$this->as_mappings['tht_base']}.line_record_uid = {$this->as_mappings['line_records']}.line_record_uid
                )
                WHERE $where
SQL;
            return $sql;
        }
        else // this filter has a sub filter
        {
			$where = $this->get_where();
            $sub_filter_sql = $this->sub_filter->get_sql();
            $sub_filter_tbl = $this->sub_filter->table_name();

            $join = "
				tht_base {$this->as_mappings['tht_base']},\n
             	experiments {$this->as_mappings['experiments']},\n
             	phenotypes {$this->as_mappings['phenotypes']},\n
             	units {$this->as_mappings['units']},\n
             	line_records {$this->as_mappings['line_records']}\n";

			$sql = "SELECT ";

			if ($sub_filter_tbl == 'phenotype_data')
			{
				$join .= "
					, phenotype_data pd\n";
        	}
			else
			{
        		$sql .= "
					{$this->as_mappings['line_records']}.line_record_uid,\n
                    {$this->as_mappings['line_records']}.line_record_name,\n
                    {$this->as_mappings['experiments']}.experiment_name,\n
                    {$this->as_mappings['experiments']}.experiment_year,\n
					{$this->as_mappings['experiments']}.number_replications,\n
                    main.tht_base_uid,\n";
        	}

        	$join = str_replace($sub_filter_tbl, '('.$sub_filter_sql.')', $join);
            //$join = preg_replace('/'.$sub_filter_tbl.'/i', '('.$sub_filter_sql.')', $join);

            if ($sub_filter_tbl == 'phenotype_data'){
                $extra_on = 'AND '.$this->as_mappings[$sub_filter_tbl].'.tht_base_uid = main.tht_base_uid';
                $extra_select  = ','.$this->as_mappings[$sub_filter_tbl].'.*';
            }




            $sql .= <<<SQL

                    {$this->as_mappings['units']}.unit_name AS unit_name_{$this->identifier},
                    {$this->as_mappings['phenotypes']}.phenotype_uid AS phenotype_uid_{$this->identifier},
                    {$this->as_mappings['phenotypes']}.phenotypes_name AS name_{$this->identifier},
                    main.value AS value_{$this->identifier}
                    $extra_select
                FROM {$this->table_name} main
                JOIN ($join)
                ON(
                    {$this->as_mappings['tht_base']}.tht_base_uid = main.tht_base_uid
                    AND {$this->as_mappings['experiments']}.experiment_uid = {$this->as_mappings['tht_base']}.experiment_uid
                    AND {$this->as_mappings['phenotypes']}.phenotype_uid = main.phenotype_uid
                    AND {$this->as_mappings['units']}.unit_uid = {$this->as_mappings['phenotypes']}.unit_uid
                    AND {$this->as_mappings['tht_base']}.line_record_uid = {$this->as_mappings['line_records']}.line_record_uid
                    $extra_on
                )
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
        $cast ="";
        if (strstr($first_bound, 'BETWEEN'))
        	$cast = "+0";

        $where = 'main.' . $first_field .$cast. ' ' . $first_bound;
        foreach($this->field_names as $key => $current_field)
        {
            $current_bound = $this->bounds[$key];
            $cast ="";
        	if (strstr($current_bound, 'BETWEEN'))
        		$cast = "+0";
            $where .= ' AND main.' . $current_field .$cast. ' ' . $current_bound;
        }
        return $where;
    }
}