<?php
header("Location: advanced/phenotype.php");
?>

<?php
/*------------------------------------------------------------------------------
 * Advnace Phenotype Search
 *------------------------------------------------------------------------------
 * Allows the user to assemble complex phenotype searches by creating an
 * unlimited number of filters
 *
 * @author Gavin Monroe <gemonroe@istate.edu>
 * @created 9/26/2007
 *------------------------------------------------------------------------------
 */

include("includes/bootstrap.inc");
connect();
include("theme/admin_header.php");
include("pear/PEAR.php");
include("pear/File.php");
define('FILTERDIR','temp/phenotypefilters/');


//include("cookie/cookie.php");
//$mycookie = new MyCookie($_SESSION['username']);

?>

<h1>Advanced Phenotype Search</h1>
<style type="text/css">
<!--
.filter
{
	height: 20px;
	border: 1px solid #5B53A6;
	padding: 5px;
	margin-bottom: 10px;
	background: url('theme/images/tblhdg.png') repeat-x;
}
.filter * { margin: 0; padding: 0; }
.filter span
{
	color: black;
	display:block;
}
-->
</style>
<?php




/**
 *------------------------------------------------------------------------------
 * Main
 *------------------------------------------------------------------------------
 */

$num_active = 0;
$filters = array();
$year_filter = null;
// load the active filters
if (isset($_POST['secretcode']) && $_POST['secretcode'] == 1){
    
    $year_filter = load_year_filter();
    
    if ($_POST['cat'] == "year" && $year_filter == null) {
        $years  = $_POST['year'];
        $years_str = "";
        foreach($years as $year){
            $years_str .= "$year ";
        }
        $year_filter = new PhenotypeFilter('Year', $years_str, "numeric", "", "");
    }else{
        $phenotype_category = getCategoryNameById($_POST['cat']);   // TODO get rid of function call
        $phenotype_name = getPhenotypeNameById($_POST['name'.$_POST['cat']]); // TODO get rid of function call
        $lower_bound = $_POST['lower'];
        $upper_bound = $_POST['upper'];
        array_push($filters, new PhenotypeFilter($phenotype_category, $phenotype_name, "numeric", $lower_bound, $upper_bound));
    }
    $other_filters = load_filters($_POST['to_string']);
    if ($other_filters != FALSE){
        $filters = array_merge($filters, $other_filters);
    }
}else if ($_POST['to_string']) {
    $filters = load_filters($_POST['to_string']);
}
// load a users' favorite filter set
if (isset($_GET['load_fav_filter']) && !empty($_GET['load_fav_filter'])) {
    $filters = load_fav_filter($_GET['load_fav_filter']);
}


?>
<h3>Active Filters</h3>
<div class="section">
<p>If you've added a filter, a list of your active filters will appear below</p>
<?php


if (!empty($filters)):

$num_active = count($filters);

$to_string = filters_to_string($filters);
if ($year_filter != null)
    $year_to_string = $year_filter->toString();

echo "<p>\n";
foreach($filters as &$filter){
    $filter->fetch();
    $filter->display($to_string);
}
if ($year_filter != null)
    $year_filter->display($to_string);
echo "</p>\n";

$temp_filter = array_shift($filters);
$master_filter = $temp_filter;
foreach ($filters as $filter){
    $master_filter = merge($master_filter, $filter);
}
if ($year_filter != null) {
	$master_filter = merge($master_filter, $year_filter);
}
array_unshift($filters, $temp_filter);



// count the number of results
$num_results = count($master_filter->lines);

// get a list of line ids
$id_list = id_list($master_filter->lines);

// serialize the master filter
$serialized = serialize($master_filter);

if(isset($year_to_string))$yts = ','. $year_to_string;

?>
<p>
	<a href="javascript:void(0);" onclick="new Effect.Appear('filter_name', {duration:1.0});">
	   Add to Favorite Filters
    </a>
</p>
<div style="display:none; height:auto; width:auto;" id="filter_name">
	<form action="" method="" onsubmit="
	new Ajax.Updater(
		'dummy',
		'fav_filters.php?tostring=<?=$to_string?><?=$yts?>',
		{
		onComplete:
			function(){
				new Ajax.Updater(
					'favorite_filters',
					'fav_get_filters.php',
					{
						onComplete: 
							function(){
								new Effect.Appear('favorite_filters', {duration:1.0});
								},
						asynchronous:true
					}
					);
				new Effect.Fade(
					'filter_name',
					{duration:1.0}
					);
				},
		asynchronous:true,
		evalScripts:true,
		parameters:Form.serialize(this)
		}
	); return false;">
	Pick a name for this filter set: <input type="text" name="filter_name" /><input type="submit" value="Save!" /><br /><br />
	</form>
</div>
<div id="dummy"></div>
<?php

$disabled = ($num_results < 1) ? " disabled=\"disabled\" " : "";

echo <<< HTML
<form method="POST" action="$_SERVER[PHP_SELF]" onsubmit="new Ajax.Updater('lines', 'as_display_lines.php', {onComplete:function(){new Effect.Appear('lines_container'); $('hide_results').enable(); $('view_results').disable();}, asynchronous:true, parameters:Form.serialize(this)}); return false;">
	<input type="hidden" name="to_string" value="$to_string" />
	<input type="hidden" name="master_filter" value='$serialized' />
	<input id="view_results" $disabled type="submit" name="submit" value="View $num_results result(s)" />
	<input id="hide_results" disabled="disabled" type="button" value="Hide result(s)" onclick="new Effect.Fade('lines_container'); $('hide_results').disable(); $('view_results').enable(); return false;" />
</form>
<div id="lines_container" style="display: none">
	<div id="lines" styel="display: none">
		&nbsp;
	</div>
</div>
HTML;

else :
  echo '<p style="margin-left: 25px"><i>You don\'t have any active filters. Please <b>add a filter</b> using the form below.</i></p>';
endif;

echo "</div>\n";

?>
<h3>Favorite Filters</h3>
<div class="section">
	<p>If you are logged in, a list of your favorite filter sets will appear below</p>
	<div id="favorite_filters" style="width: auto">
		<? include "fav_get_filters.php"; ?>
	</div>
</div>
<?php

do_html_phenoform(null);

//}





/**
 *------------------------------------------------------------------------------
 * Helper Functions
 *------------------------------------------------------------------------------ 
 */


function get_min_max_value($arg)
{
    if (is_string($arg))
    {
        $phenotype_name = $arg;
        $query = mysql_query("SELECT * FROM phenotypes WHERE phenotypes_name = '$phenotype_name'") or die(mysql_error());
        if (mysql_num_rows($query) > 0)
        {
            $result = mysql_fetch_assoc($query);
            $type = $result['datatype'];
            $id = intval($result['phenotype_uid']);
            
            if ($type == null || $type == "NULL")
            {
                $query = mysql_query("SELECT MIN(value+0) AS min, MAX(value+0) AS max FROM phenotype_data WHERE value != 'N/A' AND phenotype_uid = $id");
                if (mysql_num_rows($query) > 0)
                {
                    $result = mysql_fetch_assoc($query);
                    $min = $result['min'];
                    $max = $result['max'];
                    
                    return array($min, $max);
                }
            }
        }
    }
    else if (is_int($arg))
    {
        $id = $arg;
        $query = mysql_query("SELECT * FROM phenotypes WHERE phenotype_uid = '$id'") or die(mysql_error());
        if (mysql_num_rows($query) > 0)
        {
            $result = mysql_fetch_assoc($query);
            $type = $result['datatype'];
            
            if ($type == null || $type == "NULL")
            {
                $query = mysql_query("SELECT MIN(value+0) AS min, MAX(value+0) AS max FROM phenotype_data WHERE phenotype_uid = $id");
                if (mysql_num_rows($query) > 0)
                {
                    $result = mysql_fetch_assoc($query);
                    $min = $result['min'];
                    $max = $result['max'];
                    
                    return array($min, $max);
                }
            }
        }
    }
    return null;
}


function load_year_filter()
{
    if (empty($_POST['year_filter'])) return null;
    $parts = explode("|", $_POST['year_filter']);
    return new PhenotypeFilter($parts[1], $parts[0]);
}

/**
 * Loads a favorite filter set
 * @param $fav_filter_name the name of the favorite filter set to load 
 * @return an array containing the loaded PhenotypeFilters 
 */ 
function load_fav_filter($fav_filter_name){
    if (empty($fav_filter_name)) return FALSE;
    $sql = "SELECT * FROM fav_filters JOIN users ON fav_filters.users_uid = users.users_uid WHERE fav_filters.name = '$fav_filter_name'";
    $query = mysql_query($sql) or die(mysql_error());
    if (mysql_num_rows($query) > 0){
        $result = mysql_fetch_assoc($query);
    }else{
        return FALSE;
    }
    $filter_strings = explode(',', $result['to_string']);
    $myfilters = array();
    foreach($filter_strings as $filter_string){
        $filter_string = explode('|', $filter_string);
        list($phenotype_name, $phenotype_category, $lower_bound, $upper_bound) = $filter_string;
        array_push($myfilters, new PhenotypeFilter($phenotype_category, $phenotype_name, null, $lower_bound, $upper_bound));
    }
    return $myfilters;
}

/**
 * Loads filters given the input string
 * @param $str the string to parse
 * @return an array containing the loaded PhenotypeFilters
 */   
function load_filters($str){
    global $year_filter;
    if(empty($str)) return FALSE;
    $str = explode(',', $str);
    $myfilters = array();
    foreach($str as $filter_string){
        $filter_string = explode('|', $filter_string);
        list($phenotype_name, $phenotype_category, $lower_bound, $upper_bound) = $filter_string;
        
        if (strtolower($phenotype_category) == 'year')
        {
            $year_filter = new PhenotypeFilter($phenotype_name, $phenotype_category);
        }
        else
        {
        array_push($myfilters, new PhenotypeFilter($phenotype_category, $phenotype_name, null, $lower_bound, $upper_bound));
        }
    }
    return $myfilters;
}



/* Stores the active filters to the file system */
function cookie_filters($filters = array()){
	if (empty($filters)) return;
	$mycookie->clear_filters();
	foreach ($filters as $filter){
		$mycookie->add_filter($filter->toString());
	}
	$mycookie->to_file();
}

/* Saves the active filters as a favorite */
function filters_to_string($filters = array()){
	if (empty($filters))
		return;
	$str = '';
	foreach ($filters as $filter){
		$str .= $filter->toString() . ",";
	}
	$str = substr($str, 0, -1);
	return $str;

}


/**
 * Takes an array of lines and returns a comma-seperated string of ids
 */ 
function id_list($lines = array()) {
    if (empty($lines)) return FALSE;
    $retval = "";
    foreach ($lines as $line) {
        $retval .= $line['id'] . ",";
    }
    $retval = substr($ret, 0, -1);
    return $retval;
}


// get a category name form a category id
function getCategoryNameById($id)
{
  $sql = "SELECT phenotype_category_name FROM phenotype_category WHERE phenotype_category_uid=$id LIMIT 1";
  $res = mysql_query($sql) or die(mysql_error()."\n$sql");
  $row = mysql_fetch_row($res);
  return $row[0];
}

// get a phenotype name from a phenotype id
function getPhenotypeNameById($id)
{
  $sql = "SELECT phenotypes_name FROM phenotypes WHERE phenotype_uid=$id LIMIT 1";
  $res = mysql_query($sql) or die(mysql_error()."\n$sql");
  $row = mysql_fetch_row($res);
  return $row[0];
}

function getPhenotypeIdByName($name){
	$sql = "SELECT phenotype_uid FROM phenotypes WHERE phenotypes_name='$name' LIMIT 1";
	$res = mysql_query($sql) or die(mysql_error()."\n$sql");
	$row = mysql_fetch_row($res);
	return $row[0];
}

// output the add filter form
function do_html_phenoform($list_of_phenotypes)
{
    global $filterlist, $to_string, $num_active, $year_filter, $year_to_string;
	$cats = array();
	$num_cats = 0;
	// get the categories
	$sql = "SELECT phenotype_category_uid AS id, phenotype_category_name AS name FROM phenotype_category";
	$res = mysql_query($sql) or die(mysql_error());
	while ($cat = mysql_fetch_assoc($res)){
		$cats[$num_cats] = $cat;
		$num_phenos = 0;
		// get the phenotypes
		$sql2 = "SELECT phenotype_uid AS id, phenotypes_name AS name, description FROM phenotypes WHERE phenotype_category_uid='{$cat['id']}'";
		$res2 = mysql_query($sql2) or die(mysql_error());
		while ($pheno = mysql_fetch_assoc($res2))
		{
			$cats[$num_cats]['phenos'][$num_phenos] = $pheno;
			$num_phenos += 1;
		}
		$num_cats += 1;
	}

	echo <<<HTML
<h3>Add a Filter</h3>
<div class="section">
    
<p>
    The <strong>Advanced Phenotype Search</strong> is a highly customizable way to search for line records. The basic building block of your search is the “filter.” A filter allows you to specify a desired value or range of values for a particular phenotype. You can only create filter one-at-a-time, but you can create as many as you’d like.
</p>
<p style="color: red">
    <strong>Warning:</strong> the <strong>Advanced Phenotype Search</strong> becomes slower as you had more and more filters. Possible solutions to this bug are currently being considered.</a>
</p>
<p>
    <strong>Instructions:</strong>
    <ol>
    <li>Select the desired category under “Category”</li>
    <li>Select the desired phenotype under “Phenotype”</li>
    <li>If you’re looking for an exact value select “equal to” under “Value.” Otherwise, if you’re looking for a range of values select “between” under “Value.”</li>
    <li>Click on the “Add Filter” button</li>
    </ol>
</p>
<p>
    <strong>Note:</strong> You can also specify on or more years as a filter, but this option is not available until after you have added at least one other filter. When creating a year filter, you may select multiple years by holding down the <strong>ctrl</strong> key. A year filter is a special filter, because you may only have one.
</p>
   
<form id="filterbuilderfrm" method="post" action="$_SERVER[PHP_SELF]" onsubmit="return submitfilterbuilderfrm()">

<input type="hidden" value="1" name="secretcode" />
<input type="hidden" name="to_string" value="$to_string" />
<input type="hidden" name="year_filter" value="$year_to_string" />

<table id="filterbuildertbl" cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
<tr>
    <th width="33%">Category</th>
    <th width="34%">Phenotype</th>
    <th width="33%">Value</th>
</tr>
<tr>
    <td valign="top">
    <div id="cat_sel">
    <select size="10" id="filterbuildertblcat" name="cat" onfocus="displaySelect(this.value)" onchange="displaySelect(this.value)">
    <option value="-1">(Select a category)</option>
HTML;

	foreach ($cats as $cat)
	{
		echo "<option value=\"$cat[id]\">$cat[name]</option>";
	}
    if ($num_active > 0 && $year_filter == null)
    {
        echo "<option value=\"year\">Year</option>";
    }
    
    echo <<< HTML
    </select>
    </div>
    </td>
    
    <td valign="top" style="text-align:left">
    <div id="filterbuildertblphenoinstruct" style="width:250px"></div>
HTML;

	foreach ($cats as $cat)
	{
		echo <<<HTML
    <div id="$cat[id]_sel" style="display: none; margin: 0; padding: 0;">
    <select onchange="displayValueIs(this.value)" style="width:250px" size="10" id="filterbuildertblsel$cat[id]" name="name$cat[id]">
    <option value="-1">(Select a phenotype)</option>
HTML;

        $hiddens = "";
		foreach($cat['phenos'] as $pheno)
		{
            $ret = get_min_max_value($pheno['name']);
            if ($ret != null)
            {
                $min = $ret[0];
                $max = $ret[1];
            }
            $hiddens .= "<input type=\"hidden\" name=\"min_{$pheno['id']}\" id=\"min_{$pheno['id']}\" value=\"$min\" />";
            $hiddens .= "<input type=\"hidden\" name=\"max_{$pheno['id']}\" id=\"max_{$pheno['id']}\" value=\"$max\" />";

			echo "<option value=\"{$pheno['id']}\">{$pheno['name']}</option>";
		}

		echo <<<HTML
    </select>
    </div>
    $hiddens
HTML;
	}

    $query = mysql_query("SELECT experiment_year AS year FROM experiments GROUP BY experiment_year ORDER BY experiment_year ASC");
    echo "<div id=\"year_sel\" style=\"display: none\">";
    echo "<select onfocus=\"hideValueIs()\" onchange=\"hideValueIs()\" style=\"width: 250px\" size=\"10\" id=\"filterbuildertblselyear\" name=\"year[]\" multiple=\"multiple\">\n";
	while($arr = mysql_fetch_array($query)){
        echo "<option value=\"{$arr['year']}\">{$arr['year']}</option>\n";
    }        
	echo "</select></div>\n";

	echo <<< ECHO
            </td>
	        <td>
	        <center>
            <p id="valueis_msg"></p>
            <div id="valueis" style="display: none">
		       
	            Where the value is
	            <select id="filterbuildertblvalueis" onfocus="doValueIs()" onchange="doValueIs();">
	              <option value="-1">
	                (Select an option)
	              </option>
	              <option value="0">
	                equal to
	              </option>
	              <option value="1">
	                between
	              </option>
	            </select><br/>
	            <br/>
	            <div id="filterbuildertblequalto" style="display:none">
	              <table border="0" cellspacing="0" cellpadding="0">
	                <tr>
	                  <td>
	                    <input name="lower" type="text" onchange="updateLowerUpper(this)"></input>
	                    <!--<input name="upper" type="hidden" id="filterbuildertblupperhidden"></input>-->
	                  </td>
	                </tr>
	              </table>
	            </div>
	            <div id="filterbuildertblbetween" style="display:none">
	              <table border="0" cellspacing="0" cellpadding="0">
	                <tr>
	                  <td>
	                    <input name="lower" type="text" id="filterbuildertbllower"></input><br/>
	                    and<br/>
	                    <input name="upper" type="text" id="filterbuildertblupper"></input>
	                  </td>
	                </tr>
	              </table>
	            </div>
						</div></center>
          </td>
      	</tr>
      </table><br/>
      <input type="submit" name="filterbuilderfrmsubmit" value="Add Filter"></input>
    
  </form>
</div>
<script type="text/javascript">
    var prev_sel = null;
    function displaySelect(catid) {
        if (prev_sel != null){
            prev_sel.hide();
        }

        if (catid == "year")
            hideValueIs();
            
        if (catid != '-1')
        {
            to_show = $(catid + "_sel");
            to_show.show();
            prev_sel = to_show;
        }
        else
        {
            $('valueis_msg').update(' ');
            $('valueis').hide();
        }
    }
    function doValueIs() {
        to_compare = $('filterbuildertblvalueis').getValue();
        if ( to_compare == "0" ) {
            $('filterbuildertblbetween').hide();
            $('filterbuildertblequalto').show();
        } else if ( to_compare == "1" ) {
            $('filterbuildertblequalto').hide();
            $('filterbuildertblbetween').show();
        } else {
            $('filterbuildertblequalto').hide();
            $('filterbuildertblbetween').hide();
        }
    }
    function displayValueIs(pheno_id){
        if (pheno_id == '-1')
        {
            $('valueis_msg').update(' ');
            $('valueis').hide();
            return;
        }
        min = $("min_" + pheno_id).value;
        max = $("max_" + pheno_id).value;
        if(min != '' && max != '')
        {
            $('valueis_msg').update('Data Range: ' + min + ' - ' + max); 
            $('valueis').show();
        }
        else
        {
            $('valueis_msg').update('<span style="color:red">There\'s no data available for this phenotype.</span>');
            $('valueis').hide();
        }
    }
    function hideValueIs(){
        $('valueis').hide();
    }
    function updateLowerUpper(o) {
        $('filterbuildertbllower').value=o.value;
        $('filterbuildertblupper').value=o.value;
    }
</script>
ECHO;
}

// Merges 2 filters and returns a new filter
function merge(PhenotypeFilter $filter1, PhenotypeFilter $filter2) {

    $isYear = $filter1->isYear();

    if ($isYear){
        $lines1 = $filter2->lines;
        $lines2 = $filter1->lines;
        $temp = $filter1;
        $filter1 = $filter2;
        $filter2 = $temp;
    } else {
        $lines1 = $filter1->lines;
        $lines2 = $filter2->lines;
    } 
    
    $isYear = $filter2->isYear();
    
    // combine the category and phenotype names
    $pcat = $filter1->phenotype_category . '|' . $filter2->phenotype_category;    
    $pname = $filter1->phenotype_name . '|' . $filter2->phenotype_name;
    $punit = $filter1->lines[0]['unit_name'] . '|' . $filter2->lines[0]['unit_name'];
    
    $filter = new PhenotypeFilter($pcat, $pname);
    if ($isYear) {
        foreach ($lines1 as $line) {
            if (strpos($filter2->phenotype_name, "{$line['year']}") !== FALSE){
        	   array_push($filter->lines, $line);
        	}
        }
    }else{
        // get line ids from one filter
        $ids = array();
        $vals = array();
        foreach ($lines1 as &$line) {
                array_push($ids, $line['id']);
                $vals[$line['id']] = $line['value'];   
        }
    
        // check for matches in the other filter
        foreach ($lines2 as $i => $line){
            if(in_array($line['id'], $ids)) {
                $lines2[$i]['phenotype_category'] = $pcat;
                $lines2[$i]['phenotype_name'] = $pname;
                $lines2[$i]['value'] = $vals[$line['id']] . '|' . $line['value'];
                $lines2[$i]['unit_name'] = $punit;
            } else {
                unset($lines2[$i]);	// remove non-matching line
            }
        }
        $filter->lines = $lines2;
    }
    return $filter;
}





////////////////////////////////////////////////////////////////////////////////
// Phenotype Filter Class
////////////////////////////////////////////////////////////////////////////////

class PhenotypeFilter
{
	var $phenotype_category; //string
	var $phenotype_name;     //string
	var $type;               //string
	var $lower_bound;        //int
	var $upper_bound;        //int
	var $lines = array();              //array
	var $filename = null;

	public function __construct(
			$phenotype_category = null,
			$phenotype_name = null,
			$type = null,
			$lower_bound = null,
			$upper_bound = null
		)
    {
        $this->phenotype_category = $phenotype_category;
        $this->phenotype_name = $phenotype_name;
        $this->type = $type;
        if ($type = "numeric" && $lower_bound == null && $upper_bound == null)
        	$this->type = null;
        $this->lower_bound = $lower_bound;
        $this->upper_bound = $upper_bound;
    }
	
	public function toString()
	{
        if (strtolower($this->phenotype_category) == 'year'){
            return "{$this->phenotype_name}|{$this->phenotype_category}||";
        }
        return "{$this->phenotype_name}|{$this->phenotype_category}|{$this->lower_bound}|{$this->upper_bound}";
	}

	public function display($to_string)
	{
        $phenotype_name = $this->phenotype_name;
        if (strtolower($this->phenotype_category) == "year"){
            if (substr($this->phenotype_name, -2) == " ")
                $phenotype_name = substr($this->phenotype_name, 0, -1);
            $bound = "";
        } else if ($this->lower_bound == $this->upper_bound){
            $bound = "equal to <u><b>$this->lower_bound</b></u>";
		} else{
            $bound = "between <u><b>$this->lower_bound</b></u> and <u><b> $this->upper_bound </b></u>";
        }
        
        if (substr($to_string, -1) != ",")
            $to_string .= ",";
        $to_string = str_replace($this->toString()."," , "", $to_string);
		if (substr($to_string, -1) == ",")
            $to_string = substr($to_string, 0, -1);
		
		echo <<< HTML
            <div class="filter">
            <span style="width: auto; display: block">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background: 0; border: 0; margin: 0; padding: 0;">
            <tr>
            <td style="border: 0; margin: 0; padding: 0; text-align: left; width: 10%" width="10%">
            <form action="$_SERVER[PHP_SELF]" method="POST">
            <input type="hidden" name="to_string" value="$to_string" />
            <input type="submit" name="submit" value="Delete" />
            </form>
            </td>
            <td style="border: 0; margin: 0; padding: 0; text-align: left; width: 40%" width="40%">
            $this->phenotype_category >> <u><b>$phenotype_name</b></u>
            </td>
            <td style="border: 0; margin: 0; padding: 0; text-align: left">
            $bound
            </td>
            </tr>
            </table>
            </span>
            </div>
HTML;
	}

	public function fetch()
	{
		if (strtolower($this->phenotype_category) == 'year') return;
        $phenotype_id = getPhenotypeIdByName($this->phenotype_name);        	
		$sql = <<< SQL
            SELECT
                line_records.line_record_uid AS id,
                line_records.line_record_name AS name,
                value,
                unit_name,
                datasets.dataset_name,
                experiments.experiment_name,
                experiments.experiment_year AS year,
                phenotypes.phenotypes_name
            FROM
                line_records
            JOIN (
                datasets, experiments, tht_base, phenotype_data, phenotypes, units
            )
            ON (
                line_records.line_record_uid = tht_base.line_record_uid
                AND tht_base.tht_base_uid = phenotype_data.tht_base_uid
                AND experiments.experiment_uid = tht_base.experiment_uid
                AND datasets.datasets_uid = experiments.datasets_uid
                AND phenotype_data.phenotype_uid = phenotypes.phenotype_uid
                AND phenotypes.unit_uid = units.unit_uid
            )
            WHERE
                phenotypes.phenotype_uid = '$phenotype_id'
                AND value != 'N/A' 
                AND value+0 BETWEEN {$this->lower_bound} AND {$this->upper_bound}
            ORDER BY line_records.line_record_uid ASC
SQL;
		$res = mysql_query($sql) or die(mysql_error()."\n\n$sql");
		$this->lines = array();
		while ($line = mysql_fetch_assoc($res))
		{
			array_push($this->lines, $line);
		}
		return count($this->lines) > 0;
	}
	
	public function isYear()
	{
        return (strtolower($this->phenotype_category) == "year");
    }
}



////////////////////////////////////////////////////////////////////////////////
// Footer
////////////////////////////////////////////////////////////////////////////////
?>
</div>
<?php
include ('theme/footer.php');
?>
