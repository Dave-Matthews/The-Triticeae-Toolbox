<?php

class SQL_Limiter
{
    var $cfg = array();
    
    function SQL_Limiter($table = "")
    {
        if (empty($table)) die('Invalid parameter');
        $this->reset();
        $this->cfg['table'] = $table;
    }
    
    function reset()
    {
        $this->cfg['select'] = "*";
        $this->cfg['where'] = "1";  
    }
    
    function select($str)
    {
        if (!is_string($str)) die('Invalid parameter');
        $this->cfg['select'] = $str;
    }
    
    function where($str)
    {
        if (!is_string($str)) die('Invalid parameter');
        $this->cfg['where'] = $str;
    }
    
    function do_sql()
    {
        if (isset($this->cfg['sql']))
            return $this->cfg['sql'];
        $sql = "SELECT " . $this->cfg['select'] . " FROM " . $this->cfg['table'] . " WHERE " . $this->cfg['where'];
        return $sql;    
    }
}

?>
