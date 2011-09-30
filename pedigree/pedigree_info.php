<?php 
session_start();
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
require_once 'Spreadsheet/Excel/Writer.php';

connect();

new Pedigree($_GET['function']);

class Pedigree
{
    private $delimiter = "\t";
 
    //
    // Using the class's constructor to decide which action to perform
    public function __construct($function = null) {
        switch($function) {
	
            case 'typeLineExcel':
				$this->type_Line_Excel();  /* Exporting to excel*/
				break;
			
            default:
				$this->typeLine();
				break;
		}
    }
	
    //
    // The wrapper action for the type1 download. Handles outputting the header
    // and footer and calls the first real action of the type1 download.
    private function typeLine() {
		global $config;
		include($config['root_dir'].'theme/normal_header.php');
		echo " <h2> Line Information</h2>";
        echo "<h3> <a href='pedigree/line_selection.php'> New Line Search</a></h3>";
		$this->type_LineInformation();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
    }

    private function type_LineInformation() {
?>

<script type="text/javascript">

var line = new Array();
"<?php 
    $i=0;
    foreach ($_SESSION['selected_lines'] as $lineuid) {
?>"
	line["<?php echo $i ?>"] = "<?php echo $lineuid;
	$i++;		
    }
?>"
var sellineids = new Array();
var lineuids = line.length;

function load_excel() {
  var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeLineExcel';
  // Opens the url in the same window
  window.open(url, "_self");
}
		
// select/deselect
function sm(exbx, id) {
    if (exbx.checked == true)
        sellineids.push(id);
    else
        sellineids = sellineids.without(id);
}
            
// Select All
function exclude_all() {
    for (var i=0; i<line.length; ++i) {
        $('exbx_'+line[i]).checked = true;
    }
    sellineids = line; // all
}
            
// select none
function exclude_none() {
    for (var i=0; i<line.length; ++i) {
        $('exbx_'+line[i]).checked = false;
    }
    sellineids = new Array(); // empty
}

</script>


<style type="text/css">
    th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
    table {background: none; border-collapse: collapse}
    td {border: 1px solid #eee !important;}
    h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<style type="text/css">
    table.marker {background: none; border-collapse: collapse}
    th.marker {background: #5b53a6; color: #fff; padding: 5px 0; border: 0;}
    td.marker {padding: 5px 0; border: 0 !important;}
</style>

<div style="width: 840px;">
<table >
	<tr> 
		<th style="width: 80px;" class="marker"> Check <br/><input type="radio" name="btn1" value="" onclick="javascript:exclude_all();"/>
																		All<br>
																		<input type="radio" name="btn1" value="" onclick="javascript:exclude_none();"/>None</th>
                    
		<th style="width: 130px;" class="marker"> Line Name </th>
		<th style="width: 50px;" class="marker"> BP Code </th>
		<th style="width: 100px;" class="marker"> Growth Habit </th>
		<th style="width: 40px;" class="marker"> Row Type </th>
		<th style="width: 80px;" class="marker"> Primary End Use </th>
		<th style="width: 100px;" class="marker"> Hull </th>
		<th style="width:140px;" class="marker"> Synonym Name </th>
		<th style="width: 310px;" class="marker"> Pedigree String </th>
		<th style="width: 220px;" class="marker"> Experiment Data Available </th>
	</tr>
 </table>
 </div>
 	
<div style="padding: 0; width: 840px; height: 400px; overflow: scroll; border: 1px solid #5b53a6; clear: both">
<table>	
<?php
	
    foreach ($_SESSION['selected_lines'] as $lineuid) {
        $result=mysql_query("select line_record_name, breeding_program_code, growth_habit, row_type, hull, primary_end_use, pedigree_string from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
        $syn_result=mysql_query("select line_synonym_name from line_synonyms where line_record_uid=$lineuid") or die("No Synonym\n");
  
        $syn_names="";
        while ($syn_row = mysql_fetch_assoc($syn_result)) {
            $syn_names[] = $syn_row['line_synonym_name'];
	}
	
	while ($row=mysql_fetch_assoc($result)) {
		
?>
    <tr>
        <td style="width: 80px;" class="marker">
        <input type="checkbox" checked name="btn1" value="<?php echo $lineuid ?>" id="exbx_<?php echo $lineuid ?>" onchange="sm(this, <?php echo $lineuid ?>);" class="exbx"/>
        <input type="hidden" id="muids" name="muids" value="<?php echo $lineuid ?>" />
        </td>
  
        <td style="width: 180px;" class="marker">
        <?php $line_name = $row['line_record_name'];
	echo "<a href='pedigree/show_pedigree.php?line=$line_name'>$line_name</a>" ?>
        </td>

        <td style="width: 120px;" class="marker">
        <?php echo $row['breeding_program_code'] ?>
        </td>

        <td style="width: 200px;" class="marker">
        <?php echo $row['growth_habit'] ?>
        </td>

        <td style="width: 100px;" class="marker">
        <?php echo $row['row_type'] ?>
        </td>

        <td style="width: 120px;" class="marker">
        <?php echo $row['primary_end_use'] ?>
        </td>

        <td style="width: 150px;" class="marker">
        <?php echo $row['hull'] ?>
        </td>

        <td style="width: 250px;" class="marker">
        <?php
        for ($i = 0; $i < count($syn_names); $i++) {
            echo $syn_names[$i].","."<br/>";
        } ?>
        </td>

        <td style="width: 150px;" class="marker">
        <?php
        $line_name =  $row['line_record_name'];
        $pedigree_string = $row['pedigree_string'];
        echo "<a href='pedigree/pedigree_tree.php?line_name= $line_name '>  $pedigree_string </a> " ?>
        </td>

        <td style="width: 150px;" class="marker">
        <?php $phenotype = lineHasPhenotypeData($lineuid);
	$genotype = lineHasGenotypeData($lineuid);
	
	if($phenotype AND $genotype) {
            echo "Phenotype & Genotype";
	}
	if($phenotype AND !$genotype) {
	echo "Phenotype";
	}
	if($genotype AND !$phenotype) {
	echo "Genotype";
	}
	if(!$phenotype AND !$genotype) {
	echo "NA";
	} ?>
        </td>
  </tr>
<?php
        } //end while
    }// end foreach
?>
</table>
</div>

<br/><br/><input type="button" value="Download Line Data (.xls)" onclick="javascript:load_excel();"/>

<?php
} /* End of function type_LineInformation*/
  
private function type_Line_Excel() {

  $sample = implode(",", $_SESSION['selected_lines']);
  $tok = strtok($sample, ",");
 
  $workbook = new Spreadsheet_Excel_Writer();
  $format_header =& $workbook->addFormat();
  $format_header->setBold();
  $format_header->setAlign('center');
  $format_header->setColor('red');
  $format_header->setBgColor('blue');

  $format_header->setItalic();

  $worksheet =& $workbook->addWorksheet();
  $worksheet->write(0, 0, "Line Name", $format_header);
  $worksheet->write(0, 1, "BP Code", $format_header);
  $worksheet->write(0, 2, "Growth Habit", $format_header);
  $worksheet->write(0, 3, "Row Type", $format_header);
  $worksheet->write(0, 4, "Primary End Use", $format_header);
  $worksheet->write(0, 5, "Hull", $format_header);
  $worksheet->write(0, 6, "Pedigree String", $format_header);
  $worksheet->write(0, 7, "Experiment Data Available", $format_header);

  $i = 1;
# start by opening a query string

  while ($tok !== false) {
    $lineuid = (int)$tok;
    $result=mysql_query("select line_record_name, breeding_program_code, growth_habit, row_type, hull, primary_end_use, pedigree_string from line_records where line_record_uid=\"$lineuid\" ") or die("invalid line uid\n");
    $tok = strtok(",");
	
    while ($row = mysql_fetch_assoc($result)) {
      $format_row =& $workbook->addFormat();
      $format_row->setAlign('center');
 
      $format_pedigree_row =& $workbook->addFormat();
      $format_pedigree_row->setAlign('left');
 		
      $worksheet->write($i, 0, "$row[line_record_name]",$format_row);
      $worksheet->write($i, 1, "$row[breeding_program_code]",$format_row);
      $worksheet->write($i, 2, "$row[growth_habit]",$format_row);
      $worksheet->write($i, 3, "$row[row_type]",$format_row);
      $worksheet->write($i, 4, "$row[primary_end_use]",$format_row);
      $worksheet->write($i, 5, "$row[hull]",$format_row);
      $worksheet->write($i, 6, "$row[pedigree_string]",$format_pedigree_row);
      $worksheet->write($i, 7, "NA",$format_row);
    }
    $i++;
  }
  $workbook->send('Line_Details.xls');
  $workbook->close();
}
      } /* End of class*/
	?>
