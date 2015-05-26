<?php
// DEM apr2015: This function is correct in includes/common_import.inc, 
//              and not correct here.
/* 	function get_lineuid ($line) { */
/*                // find line name list and group it into th proper experiment */
/*                 // If the name does not work, also check versions with spaces removed */
/*                 // and spaces replaced by underscores */
/*                 $line_nosp = str_replace(" ","",$line); */
/*                 $line_us = str_replace("_","",$line); */
/*                 $line_hyp = str_replace("-","",$line); */
/*                 $line_sql = mysql_query("SELECT line_record_uid AS lruid */
/*                     FROM line_records */
/*                     WHERE line_record_name = '$line' */
/*                         OR line_record_name = '$line_nosp' */
/*                         OR line_record_name = '$line_us' */
/*                         OR line_record_name = '$line_hyp'") ; */
/*                 if(mysql_num_rows($line_sql)==0 ) */
/*                 { */
/*                    ////echo "Line ".$line." ".$line_us.$line_hyp.$line_nosp.$line_sql." \n"; */
/*                     $line_sql = mysql_query("SELECT line_record_uid AS lruid */
/*                         FROM line_synonyms */
/*                         WHERE line_synonym_name = '$line' */
/*                             OR line_synonym_name = '$line_nosp' */
/*                             OR line_synonym_name = '$line_us' */
/*                             OR line_synonym_name = '$line_hyp'"); */
/*                     if (mysql_num_rows($line_sql)==0)  { */
/*                        // echo "Line ".$line." is not in the line record or synonym table\n"; */
/*                         return FALSE; */
/*                     } */
/*                 } */
/*                 /\* do not add duplicates *\/ */
/*                 $result = array(); */
/*                 while ($row = mysql_fetch_array($line_sql, MYSQL_ASSOC)) { */
/*                     if (!in_array($row["lruid"],$result)) { */
/*                       $result[] = $row["lruid"];  */
/*                     }  */
/*                 } */
/*             return $result; */
/* } */


/**
     * Takes a needle and haystack (just like in_array()) and does a wildcard search on it's values.
     *
     * @param    string        $string        Needle to find
     * @param    array        $array        Haystack to look through
     * @result    array                    Returns the elements that the $string was found in
     */
    function find ($string, $array = array ())
    {       
        foreach ($array as $key => $value) {
            unset ($array[$key]);
            if (strpos($value, $string) !== false) {
                $array[$key] = $key;
            }
        }       
        return $array;
    }


?>
