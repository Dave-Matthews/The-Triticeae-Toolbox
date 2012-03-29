<?php 
session_start();
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
require_once 'Spreadsheet/Excel/Writer.php';
connect();

new Pedigree($_GET['function']);

class Pedigree {
  private $delimiter = "\t";
  // Using the class's constructor to decide which action to perform
  public function __construct($function = null) {
    switch($function) {
    case 'typeLineExcel':
      $this->type_Line_Excel();  /* Export to excel */
      break;
    default:
      $this->typeLine();  /* Display in browser */
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
    $this->type_LineInformation();
    echo "<h3> <a href='pedigree/line_selection.php'> New Line Search</a></h3>";
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }

  private function type_LineInformation() {
    // If we clicked on the button for Lines Found, retrieve that cookie instead.
    if ($_GET['lf'] == "yes") 
      $linelist = $_SESSION['linesfound'];
    else 
      $linelist = $_SESSION['selected_lines'];
?>

<script type="text/javascript">

var line = new Array();
"<?php 
    $i=0;
    foreach ($linelist as $lineuid) {
?>"
	line["<?php echo $i ?>"] = "<?php echo $lineuid;
	$i++;		
    }
?>"
var sellineids = line;
		
function load_excel() {
    excel_str1 = sellineids;
    arry_length = (sellineids.length);
    var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeLineExcel'+ '&mxls1=' + excel_str1 + '&mxls2=' + arry_length;
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
    th.marker {background: #5b53a6; color: #fff; padding: 5px 0; border: 0; border-color: #fff}
    td.marker {padding: 5px 0; border: 0 !important;}
</style>

<div style="width: 840px;">
  <table >
    <tr> 
      <th class="marker" style="width: 80px; text-align: left"> &nbsp;&nbsp;Check <br/>
	<input type="radio" name="btn1" value="" onclick="javascript:exclude_all();"/>All<br>
	<input type="radio" name="btn1" value="" onclick="javascript:exclude_none();"/>None</th>
      <th style="width: 380px;" class="marker"> Line Name </th>
      <th style="width: 100px;" class="marker"> Breeding Program </th>
      <th style="width: 90px;" class="marker"> Hard-<br>ness </th>
      <th style="width: 90px;" class="marker"> Color </th>
      <th style="width: 90px;" class="marker"> Growth Habit </th>
      <th style="width: 240px;" class="marker"> Synonyms </th>
      <th style="width: 310px;" class="marker"> Pedigree </th>
      <th style="width: 100px;" class="marker"> Data<br>Available </th>
    </tr>
  </table>
 </div>

<div style="padding: 0; width: 838px; height: 400px; overflow: scroll; border: 1px solid #5b53a6; clear: both">
<table style="table-layout:fixed; width: 830px">	

<?php
    foreach ($linelist as $lineuid) {
      $result=mysql_query("select line_record_name, breeding_program_code, hardness, color, growth_habit, pedigree_string from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
      $syn_result=mysql_query("select line_synonym_name from line_synonyms where line_record_uid=$lineuid") or die("No Synonym\n");
      $syn_names=""; $sn = "";
      while ($syn_row = mysql_fetch_assoc($syn_result)) 
	$syn_names[] = $syn_row['line_synonym_name'];
      if (is_array($syn_names))
	$sn = implode(', ', $syn_names);
      while ($row=mysql_fetch_assoc($result)) {
?>
	<tr>
        <td style="width: 57px;" class="marker">
	  <input type="checkbox" checked name="btn1" value="<?php echo $lineuid ?>" id="exbx_<?php echo $lineuid ?>" onchange="sm(this, <?php echo $lineuid ?>);" class="exbx"/>&nbsp;&nbsp;&nbsp;
        <input type="hidden" id="muids" name="muids" value="<?php echo $lineuid ?>" />
        </td>
        <td style="width: 172px; text-align: center" class="marker">
        <?php $line_name = $row['line_record_name'];
	echo "<a href='pedigree/show_pedigree.php?line=$line_name'>$line_name</a>" 
	  ?>
        </td>
        <td style="width: 72px; text-align: center" class="marker">
        <?php echo $row['breeding_program_code'] ?>
        </td>
        <td style="width: 56px; text-align: center" class="marker">
        <?php echo $row['hardness'] ?>
        </td>
        <td style="width: 56px; text-align: center" class="marker">
        <?php echo $row['color'] ?>
        </td>
        <td style="width: 60px; text-align: center" class="marker">
        <?php echo $row['growth_habit'] ?>
        </td>
        <td style="width: 130px; text-align: center" class="marker">
        <?php echo $sn ?>
        </td>
        <td style="width: 155px; text-align: center; word-wrap: break-word" class="marker">
        <?php
        $line_name =  $row['line_record_name'];
	//Don't need this crude approach, CSS "word-wrap: break-word" works to prevent IE8's ugliness.
        //$pedigree_string = wordwrap($row['pedigree_string'], 20, "<br>", true);
	$pedigree_string = $row['pedigree_string'];
        echo "<a href='pedigree/pedigree_tree.php?line_name= $line_name '>  $pedigree_string </a> " ?>
        </td>
        <td style="width: 65px; text-align: center" class="marker">
        <?php $phenotype = lineHasPhenotypeData($lineuid);
	$genotype = lineHasGenotypeData($lineuid);
	if($phenotype AND $genotype) echo "Phenotype<br>Genotype";
	if($phenotype AND !$genotype) echo "Phenotype";
	if($genotype AND !$phenotype) echo "Genotype";
	if(!$phenotype AND !$genotype) echo "None";
	 ?>
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

  if (!empty($_GET['mxls1'])) {
    $sample = $_GET['mxls1'];
  }
  else {
    // If we clicked on the button for Lines Found, retrieve that cookie instead.
    if ($_GET['lf'] == "yes") 
      $linelist = $_SESSION['linesfound'];
    else 
      $linelist = $_SESSION['selected_lines'];
    $sample = implode(",", $linelist);
  }
  $tok = strtok($sample, ",");
 
    $workbook = new Spreadsheet_Excel_Writer();
    $format_header =& $workbook->addFormat();
    $format_header->setBold();
    $format_header->setSize(9);
    /* $format_header->setTextWrap(); */
    /* $format_header->setAlign('center'); */
    /* $format_header->setColor('red'); */
    /* $format_header->setBgColor('blue'); */
    /* $format_header->setItalic(); */
    $format_row =& $workbook->addFormat();
    $format_row->setSize(9);
    /* $format_row->setAlign('center'); */
    /* $format_pedigree_row =& $workbook->addFormat(); */
    /* $format_pedigree_row->setAlign('left'); */

    $worksheet =& $workbook->addWorksheet();
    // Freeze row 1 and column 1 from scrolling.
    $worksheet->freezePanes(array(1, 1));
    // Set columns 0 to 3 wider.
    $worksheet->setColumn(0,3,15);
    $worksheet->write(0, 0, "Name", $format_header);
    $worksheet->write(0, 1, "GRIN", $format_header);
    $worksheet->write(0, 2, "Synonyms", $format_header);
    $worksheet->write(0, 3, "Pedigree", $format_header);
    $worksheet->setColumn(4,10,7);
    $worksheet->write(0, 4, "Program", $format_header);
    $worksheet->write(0, 5, "Hardness", $format_header);
    $worksheet->write(0, 6, "Color", $format_header);
    $worksheet->write(0, 7, "Growth Habit", $format_header);
    $worksheet->write(0, 8, "Awned", $format_header);
    $worksheet->write(0, 9, "Chaff", $format_header);
    $worksheet->write(0, 10, "Height", $format_header);
    $worksheet->setColumn(11,12,20);
    $worksheet->write(0, 11, "Description", $format_header);
    $worksheet->write(0, 12, "Data Available", $format_header);

    $i = 1;
    # start by opening a query string

    while ($tok !== false) {
        $lineuid = (int)$tok;
        $result=mysql_query("select line_record_name, breeding_program_code, 
           hardness, color, growth_habit, awned, chaff, height, description, pedigree_string
           from line_records where line_record_uid=\"$lineuid\" ") or die("invalid line uid\n");
        $tok = strtok(",");
	
	while ($row = mysql_fetch_assoc($result)) {
            $worksheet->write($i, 0, "$row[line_record_name]",$format_row);
            $worksheet->write($i, 3, "$row[pedigree_string]",$format_row);
            $worksheet->write($i, 4, "$row[breeding_program_code]",$format_row);
            $worksheet->write($i, 5, "$row[hardness]",$format_row);
            $worksheet->write($i, 6, "$row[color]",$format_row);
            $worksheet->write($i, 7, "$row[growth_habit]",$format_row);
            $worksheet->write($i, 8, "$row[awned]",$format_row);
            $worksheet->write($i, 9, "$row[chaff]",$format_row);
            $worksheet->write($i, 10, "$row[height]",$format_row);
            $worksheet->write($i, 11, "$row[description]",$format_row);
        }
	$grin_result=mysql_query("select barley_ref_number from barley_pedigree_catalog_ref 
           where line_record_uid=$lineuid") or die(mysql_error());
	$grin_names=""; $gr = "";
	while ($grin_row = mysql_fetch_assoc($grin_result)) 
	  $grin_names[] = $grin_row['barley_ref_number'];
	if (is_array($grin_names))
	  $gr = implode(', ', $grin_names);
	$worksheet->write($i, 1, "$gr",$format_row);

	$syn_result=mysql_query("select line_synonym_name from line_synonyms 
            where line_record_uid=$lineuid") or die(mysql_error());
	$syn_names=""; $sn="";
	while ($syn_row = mysql_fetch_assoc($syn_result)) 
	  $syn_names[] = $syn_row['line_synonym_name'];
	if (is_array($syn_names))
	  $sn = implode(', ', $syn_names);
	$worksheet->write($i, 2, "$sn",$format_row);
	// Data Available:
	$phenotype = lineHasPhenotypeData($lineuid);
	$genotype = lineHasGenotypeData($lineuid);
	if($phenotype AND $genotype) $hasdata = "Phenotype, Genotype";
	if($phenotype AND !$genotype) $hasdata = "Phenotype only";
	if($genotype AND !$phenotype) $hasdata = "Genotype only";
	if(!$phenotype AND !$genotype) $hasdata = "None";
	$worksheet->write($i, 12, $hasdata,$format_row);

        $i++;
    }
    $workbook->send('Line_Details.xls');
    $workbook->close();
    }
} /* End of class Pedigree */
?>
