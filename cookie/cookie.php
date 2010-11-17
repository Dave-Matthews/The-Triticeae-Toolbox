<?php

define('TEMP_DIR', 'user_cookies/');
define('FILE_EXT', '.tmp');

/**
 * A class for remembering selected items
 */
class MyCookie {

	var $mylines;
	var $mymarkers;
	var $myfilters;
	var $mygeneral;
	
	var $username; /* used to determine the file name */
	
	function MyCookie($username){
		$this->username = $username;
		$this->clear();
		$this->from_file();
	}
	
	function gen_where($ofwhat, $fieldname){
		if (empty($ofwhat)){
			return "1";
		}
		$this->trim_comma_all();
	    $var = "my$ofwhat";
		return "$fieldname IN ({$this->$var})";
	}
	
    function to_file(){
        $pathname = TEMP_DIR . $this->username . FILE_EXT;
        $handle = fopen($pathname, 'w+');
        $this->trim_comma_all();
        fwrite($handle, "mylines=" . $this->mylines . "\n");
        fwrite($handle, "myfilters=" . $this->myfilters . "\n");
        fwrite($handle, "mygeneral=" . $this->mygeneral . "\n");
        fclose($handle);
    }
	
	function from_file(){
        $pathname = TEMP_DIR . $this->username . FILE_EXT;
        if (file_exists($pathname)) { 
            $handle = fopen($pathname, 'r');
            if (! ($handle === FALSE)){
                while (!feof($handle)){
                    $this->parse_line(fgets($handle));
                }
            }
            fclose($handle);
        }
	}
	
	function parse_line($str){
		if(!empty($str)){
			$parts = explode("\n", $str);
            $parts = explode('=', $parts[0]);
			$this->$parts[0] = $parts[1];
		}
	}
	
	function trim_comma(&$str){
		while (substr($str, -1) == ','){
			$str = substr($str, 0, -1);
		}
	}
	
	function ensure_comma(&$str){
        $this->trim_comma($str);
        $str .= ',';
    }
	
	function trim_comma_all(){
		$this->trim_comma($this->mylines);
		$this->trim_comma($this->mymarkers);
		$this->trim_comma($this->myfilters);
		$this->trim_comma($this->mygeneral);
	}
	
	function clear(){
		$this->mylines = '';
		$this->mymarkers = '';
		$this->clear_filters();
		$this->mygeneral = '';
	}

	function add_line($id){
        $this->ensure_comma($this->mylines);
        if (strpos($this->mylines, " $id,") === FALSE){
            if ($this->mylines==',') {
            	$this->mylines = '';
            }
            $this->mylines .= " $id,";
        }
	}
	
	function add_general($id){
        $this->ensure_comma($this->mygeneral);
        if (strpos($this->mygeneral, " $id,") === FALSE){
            if ($this->mygeneral==',') {
            	$this->mygeneral = '';
            }
            $this->mygeneral .= " $id,";
        }
	}
	
	function remove_general($id){
        $this->ensure_comma($this->mygeneral);
        $pos = strpos($this->mygeneral, " $id,");
        if (strpos($this->mygeneral, " $id,") === FALSE){
            return FALSE;
        }
        $this->mygeneral = str_replace(" $id," , '', $this->mygeneral);
        return TRUE;
    }
	
	function contains_line($id){
        $this->ensure_comma($this->mylines);
        return (! (strpos($this->mylines, " $id,") === FALSE) );
    }
    
    function contains_general($id){
        $this->ensure_comma($this->mygeneral);
        return (! (strpos($this->mygeneral, " $id,") === FALSE) );
    }
	
	function add_marker($id){
		if (strpos($this->mymarkers, $id) === FALSE)
            $this->mymarkers .= "$id,";
	}
	
	function add_filter($filter_string){
		if (strpos($this->myfilters, $filter_string) == FALSE)
			$this->myfilters .= "$filter_string,";
	}
	
	/* Clear functions */
	function clear_filters(){
		$this->myfilters = '';
	}
}

?>
