<?php session_start(); ?>
<html>
<body>
<?php

/*
//A php script to dynamically read data related to a particular experiment from the database and to 
//display it in a nice table format. Utilizes the the tableclass Class by Manuel Lemos to display the 
//table.
// test for mer

//Author: Kartic Ramesh
*/


require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
$trial_code=$_GET['trial_code'];
//echo $trial_code."<br>";

connect();
//-----------------------------------------------------------------------------------
$sql_auth="SELECT data_public_flag FROM experiments WHERE trial_code='$trial_code'";
$res_auth=mysql_query($sql_auth) or die(mysql_error());
$row_auth=mysql_fetch_array($res_auth);
$data_public_flag=$row_auth['data_public_flag'];


$sql="SELECT experiment_uid FROM experiments WHERE trial_code='$trial_code'";
$result=mysql_query($sql);
$row=mysql_fetch_array($result);
$experiment_uid=$row['experiment_uid'];
//echo $experiment_uid."<br>";

$sql="SELECT tht_base_uid,line_record_uid FROM tht_base WHERE datasets_experiments_uid='$experiment_uid'";
$result_thtbase=mysql_query($sql) or die(mysql_error());

$row_thtbase=mysql_fetch_assoc($result_thtbase);
$thtbaseuid=$row_thtbase['tht_base_uid'];
$linerecorduid=$row_thtbase['line_record_uid'];
//echo $thtbaseuid."<br>";

$titles=array('Line Name'); //stores the titles for the display table with units
$phenotype_data_name=array('Line Name');

$sql1="SELECT DISTINCT phenotype_data_name FROM phenotype_data WHERE tht_base_uid='$thtbaseuid'";
$result1=mysql_query($sql1);
while($row1=mysql_fetch_array($result1))
{
$temp=$row1['phenotype_data_name'];


$sql2="SELECT unit_uid FROM phenotypes WHERE phenotypes_name='$temp'";
$result2=mysql_query($sql2) or die (mysql_error());
$row2=mysql_fetch_array($result2);
$unit_uid=$row2['unit_uid'];

$sql3="SELECT unit_name FROM units WHERE unit_uid='$unit_uid'";
$result3=mysql_query($sql3) or die(mysql_error());
$row3=mysql_fetch_array($result3);
$unit_name=$row3['unit_name'];

$titles[]=ucwords($temp)." ($unit_name)";
$phenotype_data_name[]=$temp;
//echo $row1['phenotype_data_name']."<br>";
}

$all_rows=array(); //2D array that will hold the values in table format to be displayed
$single_row=array(); //1D array which will hold each row values in the table format to be displayed

$myFile = "data.csv";//auto generate a csv file with the queried data
$fh = fopen($myFile, 'w');
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData=$titles[0];
for($i=1;$i<count($titles);$i++) //write the column headers to the file
{
$stringData=$stringData.", $titles[$i]";
}
$stringData=$stringData."\n";
fwrite($fh, $stringData);

//---------------------------------------------------------------------------------------------------------------
//first row in the table is added
$sql_lnruid="SELECT line_record_name FROM line_records WHERE line_record_uid='$linerecorduid'";
$result_lnruid=mysql_query($sql_lnruid) or die(mysql_error());
$row_lnruid=mysql_fetch_assoc($result_lnruid);
$lnrname=$row_lnruid['line_record_name'];
$single_row[0]=$lnrname;
$stringData=$single_row[0];

//the for loop successively adds the different column values in the particular row
for($i=1;$i<count($phenotype_data_name);$i++)
{
$pname=$phenotype_data_name[$i];


$sql_val="SELECT value FROM phenotype_data WHERE tht_base_uid='$thtbaseuid' AND phenotype_data_name='$pname'";
$result_val=mysql_query($sql_val);

$row_val=mysql_fetch_array($result_val);
$val=$row_val['value'];
if($pname=='Grain Yield')
{}
else
{
if($val!="") $val=number_format($val,2);
else $val="N/A";}
$single_row[$i]=$val;
$stringData=$stringData.", $val";
}
$stringData=$stringData."\n";
fwrite($fh, $stringData); //successively add the generated rows to the file
$all_rows[]=$single_row; //a complete row is added to the all_rows array 

//---------------------------------------------------------------------------------------------------------------
//remaining 2 to n rows are added 
while($row_thtbase=mysql_fetch_assoc($result_thtbase))
{
//echo $row_thtbase['tht_base_uid']."	".$row_thtbase['line_record_uid']."<br>";
$thtbaseuid=$row_thtbase['tht_base_uid'];
$linerecorduid=$row_thtbase['line_record_uid'];
//echo $linerecorduid;

$sql_lnruid="SELECT line_record_name FROM line_records WHERE line_record_uid='$linerecorduid'";
$result_lnruid=mysql_query($sql_lnruid) or die(mysql_error());
$row_lnruid=mysql_fetch_assoc($result_lnruid);
$lnrname=$row_lnruid['line_record_name'];
$single_row[0]=$lnrname;
$stringData=$single_row[0];

for($i=1;$i<count($phenotype_data_name);$i++)
{
$pname=$phenotype_data_name[$i];
$sql_val="SELECT value FROM phenotype_data WHERE tht_base_uid='$thtbaseuid' AND phenotype_data_name='$pname'";
$result_val=mysql_query($sql_val);
$row_val=mysql_fetch_assoc($result_val);
$val=$row_val['value'];
if($pname=='Grain Yield')
{}
else
{
if($val!="") $val=number_format($val,2);
else $val="N/A";}
$single_row[$i]=$val;
$stringData=$stringData.", $val";
}
$stringData=$stringData."\n";
fwrite($fh, $stringData);
$all_rows[]=$single_row;
}

$total_rows=count($all_rows); //used to determine the number of rows to be displayed in the result page

$display_name=ucwords($trial_code); //used to display a beautiful name as the page header
echo "<h1>".$display_name."</h1>";


$query="SELECT * FROM phenotype_experiment_info WHERE experiment_uid='$experiment_uid'"; //used to display the annotation details 
$result_pei=mysql_query($query) or die(mysql_error());
$row_pei=mysql_fetch_array($result_pei);
echo "<table>";

echo "<tr> <td>Planting Date</td><td>".$row_pei['planting_date']."</td></tr>";
echo "<tr> <td>Seeding Rate</td><td>".$row_pei['seeding_rate']."</td></tr>";
echo "<tr> <td>Harvest Date</td><td>".$row_pei['harvest_date']."</td></tr>";
echo "<tr> <td>Experiment Design</td><td>".$row_pei['experiment_design']."</td></tr>";
echo "<tr> <td>Plot Size</td><td>".$row_pei['plot_size']."</td></tr>";
echo "<tr> <td>Harvest Area</td><td>".$row_pei['harvest_area']."</td></tr>";
echo "<tr> <td>Irrigation</td><td>".$row_pei['irrigation']."</td></tr>";
echo "<tr> <td>Latitude / Longitude</td><td>".$row_pei['latitude_longitude']."</td></tr>";
echo "<tr> <td>Number of Replications</td><td>".$row_pei['number_replications']."</td></tr>";
echo "</table>";


//------------------------------------------------------------------------------------------------------------------------
//A modified version of the test_table_class.php by Manuel Lemos which extends tableclass.php also by the same author.
//Author of modified version: Kartic Ramesh
//------------------------------------------------------------------------------------------------------------------------
require('tableclass.php');

/*
 *  A sub-class of the table class to customize how to retrieve the data
 *  to be presented
 */

class my_table_class extends table_class
{
	/*
	 *  Initial first page
	 */
	var $page = 0;

	/*
	 *  Limit of rows to show per page besides the header row
	 */
	var $rowsperpage = 10;

	/*
	 *  Turn table border on or off
	 */
	var $border = 1;

	/*
	 *  Color to highlight rows when the users drags the mouse over them
	 */
	var $highlightrowcolor = 'cyan'; //#00CCCC

	/*
	 *  Background color of the header row
	 */
	var $headersrowbackgroundcolor = '#CCCCCC';

	/*
	 *  Background color of the odd numbered rows
	 */
	var $oddrowsbackgroundcolor = 'white'; //#EEEE00

	/*
	 *  Background color of the even numbered rows
	 */
	var $evenrowsbackgroundcolor = 'white'; //#CCCC00

	/*
	 *  Array of values to be displayed
	 */
	var $values = array();

	/*
	 *  Titles of the header columns
	 */
	var $titles = array();

	/*
	 *  This function defines the contents of each table cell
	 */
	Function fetchcolumn(&$columndata)
	{
		$column = $columndata['column'];
		if($column >= count($this->titles))
			return 0;

		/*
		 *  Is it the header row?
		 */
		$row = $columndata['row'];
		if($row==0)
		{
			/*
			 *  Display the table header titles
			 */
			$columndata['data'] = $this->titles[$column];
			$columndata['header']=1;
		}
		else
		{
			/*
			 *  Display the table cells with data from the values array
			 */
			$value=Key($this->values);
			switch($column)
			{
				case 0:
					$columndata['data'] = $this->values[$value][0];
					$columndata['align'] = 'right';
					break;

				case 1:
					$columndata['data'] = $this->values[$value][1];
					break;

				case 2:
					$columndata['data'] = $this->values[$value][2];
					break;

				case 3:
					$columndata['data'] = $this->values[$value][3];
					break;
				
				case 4:
					$columndata['data'] = $this->values[$value][4];
					break;
					
				case 5:
					$columndata['data'] = $this->values[$value][5];
					break;
					
				case 6:
					$columndata['data'] = $this->values[$value][6];
					break;
					
				case 7:
					$columndata['data'] = $this->values[$value][7];
					break;
					
				case 8:
					$columndata['data'] = $this->values[$value][8];
					break;
					
				case 9:
					$columndata['data'] = $this->values[$value][9];
					break;
					
				case 10:
					$columndata['data'] = $this->values[$value][10];
					break;
					
				case 11:
					$columndata['data'] = $this->values[$value][11];
					break;
					
				case 12:
					$columndata['data'] = $this->values[$value][12];
					break;
					
				case 13:
					$columndata['data'] = $this->values[$value][13];
					break;
					
				case 14:
					$columndata['data'] = $this->values[$value][14];
					break;
				
				case 15:
					$columndata['data'] = $this->values[$value][15];
					break;
					
				case 16:
					$columndata['data'] = $this->values[$value][16];
					break;
				
				case 17:
					$columndata['data'] = $this->values[$value][17];
					break;
					
				case 18:
					$columndata['data'] = $this->values[$value][18];
					break;
					
				case 19:
					$columndata['data'] = $this->values[$value][19];
					$columndata['align'] = 'right';
					break;
			}	//additional cases added to make sure we display all the phenotypes possible for any given line 
		}
		return(1);
	}

	/*
	 *  Function that defines each table row
	 */
	Function fetchrow(&$rowdata)
	{
		/*
		 *  Only allow displaying up to the limit number of rows
		 */
		$row = $rowdata['row'];
		if($row > min($this->rowsperpage, count($this->values) - $this->page * $this->rowsperpage))
			return(0);

		/*
		 * Set the highlight and background color according to the row number 
		 */
		$rowdata['backgroundcolor']=(($row == 0) ? $this->headersrowbackgroundcolor : ((intval($row % 2) == 0) ? $this->evenrowsbackgroundcolor : $this->oddrowsbackgroundcolor));
		$rowdata['id']=($this->rowidprefix.strval($row));
		$rowdata['highlightcolor']=(($row != 0) ? $this->highlightrowcolor : '');
		switch($row)
		{
			case 0:
				/*
				 *  Seek to the first position of the array values to display
				 */
				Reset($this->values);
				$first = $this->page * $this->rowsperpage;
				for($p = 0; $p < $first; ++$p)
					Next($this->values);
				break;
			case 1:
				break;
			default:
				/*
				 *  Seek to the next position of the array values to display
				 */
				Next($this->values);
				break;
		}
		return(1);
	}
};

?><html>
<body>

<hr />
<?php

	/*
	 *  Array of data to display in the table
	 */
	

	$table = new my_table_class;

	/*
	 *  Prefix for the table row identifiers
	 */
	$table->rowidprefix = 'currency';

	/*
	 *  Titles of the table columns
	 */
	$table->titles = $titles; //titles of the header row modified to show the phenotypes measured in that particular experiment
	/*array(
		$p1,
		'Symbol',
		'World zone',
		'Name',
		'Current value'
	);

	/*
	 *  Limit of navigation links to show
	 */
	$table->listpages=3;

	/*
	 *  Title of the first page link
	 */
	$table->firstprefix="<< First";

	/*
	 *  Title of the previous page link
	 */
	$table->previousprefix="< Previous";

	/*
	 *  Title of the next page link
	 */
	$table->nextsuffix="Next >";

	/*
	 *  Title of the last page link
	 */
	$table->lastsuffix="Last >>";

	/*
	 *  Show the row range in the first and last page links
	 */
	$table->rangeinfirstlast=0;

	/*
	 *  Show the row range in the previous and next page links
	 */
	$table->rangeinpreviousnext=0;

	/*
	 *  Limit number of rows to display per page
	 */
	$table->rowsperpage = $total_rows; //$total_rows is the count of the array list all_rows

	/*
	 *  Set the array of values to display in the table
	 */
	$table->values = $all_rows; //$all_rows is the 2D array (table form) which stores all the data that we need to display

	/*
	 *  Set the total number of rows to display in all pages
	 *  so the class can generate pagination links
	 */
	$table->totalrows = count($all_rows);

	/*
	 *  Set the number of the current page to display
	 */
	$maximum_pages = intval($table->totalrows / $table->rowsperpage);
	if(IsSet($_GET['page'])
	&& ($page = intval($_GET['page'])) >=0
	&& $page <= $maximum_pages)
		$table->page = $page;

	/*
	 *  Display the whole table at once
	 */
	echo $table->outputtable();

echo "<form action='data.csv'";
echo "<input type='submit' value='Download'></input>";
echo "</form>";




/*for($j=0;$j<count($all_rows);$j++)
{
for($
}*/

fclose($fh);


//-----------------------------------------------------------------------------------







$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
</body>
</html>