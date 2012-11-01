<?php 
/**
 * Pedigree Info
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <cbirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/pedigree/pedigree_info.php
 * 
 */

session_start();
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
include '../lib/PHPExcel/Classes/PHPExcel/IOFactory.php';

connect();

$method = $_SERVER['REQUEST_METHOD'];
if ($method == "GET") {
  new Pedigree($_GET['function']);
} elseif ($method == "POST") {
  new Pedigree($_POST['function']);
}

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

// Read PHP array $linelist into Javascript array line[].
var line = new Array();
<?php
      for ($i=0; $i<count($linelist); $i++) { ?>
        line[<?php echo $i ?>] = <?php echo $linelist[$i] ?> 
<?php 
      } 
?> 
var sellineids = line;
		
function load_excel() {
    excel_str1 = sellineids;
    arry_length = (sellineids.length);
    var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeLineExcel'+ '&mxls1=' + excel_str1 + '&mxls2=' + arry_length;
    // Opens the url in the same window
     window.open(url, "_self");
}
//this works better with large data sets
function load_excel2() {
    var myForm = document.createElement("form");
    var p = new Array();
    p["function"] = "typeLineExcel";
    p["mxls1"] = sellineids;
    p["mxls2"] = (sellineids.length);
    
    myForm.method="post";
    myForm.action = '<?php $_SERVER[PHP_SELF];?>';
    for (var k in p) {
        var myInput = document.createElement("input") ;
        myInput.setAttribute("type", "hidden");
        myInput.setAttribute("name", k);
        myInput.setAttribute("value", p[k]);
        myForm.appendChild(myInput);
     }
     document.body.appendChild(myForm) ;
     myForm.submit() ;
     document.body.removeChild(myForm) ;
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

<div style="width: 940px;">
  <table >
    <tr> 
      <th class="marker" style="width: 80px; text-align: left"> &nbsp;&nbsp;Check <br/>
	<input type="radio" name="btn1" value="" onclick="javascript:exclude_all();"/>All<br>
	<input type="radio" name="btn1" value="" onclick="javascript:exclude_none();"/>None</th>
      <th style="width: 380px;" class="marker"> Line Name </th>
      <th style="width: 120px;" class="marker"> Species </th>
      <th style="width: 100px;" class="marker"> Breeding Program </th>
      <th style="width: 90px;" class="marker"> Hard-<br>ness </th>
      <th style="width: 90px;" class="marker"> Color </th>
      <th style="width: 90px;" class="marker"> Growth Habit </th>
      <th style="width: 240px;" class="marker"> Synonyms </th>
      <th style="width: 310px;" class="marker"> Pedigree </th>
      <th style="width: 150px;" class="marker"> Data<br>Available </th>
    </tr>
  </table>
 </div>

<div style="padding: 0; width: 938px; height: 400px; overflow: scroll; border: 1px solid #5b53a6; clear: both">
<table style="table-layout:fixed; width: 920px">	

<?php
    foreach ($linelist as $lineuid) {
      $result=mysql_query("select line_record_name, species, breeding_program_code, hardness, color, growth_habit, pedigree_string from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
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
        <?php echo $row['species'] ?>
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

<br/><br/><input type="button" value="Download Line Data (.xls)" onclick="javascript:load_excel2();"/>

<?php
} /* End of function type_LineInformation*/
  
private function type_Line_Excel() {

  if (!empty($_POST['mxls1'])) {
    $sample = $_POST['mxls1'];
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

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0); 

    $style_header = array(
        'font' => array(
            'bold' => true,
        ),
    );
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
    $objPHPExcel->getActiveSheet()->freezePane('A2');
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style_header);
 
    // Freeze row 1 and column 1 from scrolling.
    // Set columns 0 to 3 wider.
 
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Name');
    $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'GRIN');
    $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Synonyms');
    $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Pedigree');
    $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Program');
    $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Hardness');
    $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Color');
    $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Growth Habit');
    $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Awned');
    $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Chaff');
    $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Height');
    $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Description');
    $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Data Available');
    $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Species');
    
    $i = 2;
    # start by opening a query string

    while ($tok !== false) {
        $lineuid = (int)$tok;
        $result=mysql_query("select line_record_name, breeding_program_code, 
           hardness, color, growth_habit, awned, chaff, height, description, pedigree_string, species
           from line_records where line_record_uid=\"$lineuid\" ") or die("invalid line uid\n");
        $tok = strtok(",");
	
	while ($row = mysql_fetch_assoc($result)) {
            $objPHPExcel->getActiveSheet()->SetCellValue("A$i", "$row[line_record_name]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("D$i", "$row[pedigree_string]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("E$i", "$row[breeding_program_code]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("F$i", "$row[hardness]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("G$i", "$row[color]",format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("H$i", "$row[growth_habit]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("I$i", "$row[awned]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("J$i", "$row[chaff]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("K$i", "$row[height]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("L$i", "$row[description]",$format_row);
            $objPHPExcel->getActiveSheet()->SetCellValue("N$i", "$row[species]",$format_row);
        }
	$grin_result=mysql_query("select barley_ref_number from barley_pedigree_catalog_ref 
           where line_record_uid=$lineuid") or die(mysql_error());
	$grin_names=""; $gr = "";
	while ($grin_row = mysql_fetch_assoc($grin_result)) 
	  $grin_names[] = $grin_row['barley_ref_number'];
	if (is_array($grin_names))
	  $gr = implode(', ', $grin_names);
          $objPHPExcel->getActiveSheet()->SetCellValue("B$i", "$gr",$format_row);

	$syn_result=mysql_query("select line_synonym_name from line_synonyms 
            where line_record_uid=$lineuid") or die(mysql_error());
	$syn_names=""; $sn="";
	while ($syn_row = mysql_fetch_assoc($syn_result)) 
	  $syn_names[] = $syn_row['line_synonym_name'];
	if (is_array($syn_names))
	  $sn = implode(', ', $syn_names);
          $objPHPExcel->getActiveSheet()->SetCellValue("C$i", "$sn",$format_row);
	// Data Available:
	$phenotype = lineHasPhenotypeData($lineuid);
	$genotype = lineHasGenotypeData($lineuid);
	if($phenotype AND $genotype) $hasdata = "Phenotype, Genotype";
	if($phenotype AND !$genotype) $hasdata = "Phenotype only";
	if($genotype AND !$phenotype) $hasdata = "Genotype only";
	if(!$phenotype AND !$genotype) $hasdata = "None";
        $objPHPExcel->getActiveSheet()->SetCellValue("M$i", "$hasdata",$format_row);

        $i++;
    }
    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Line_Details.xls"');
    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
    $objWriter->save('php://output');
    $objPHPExcel->disconnectWorksheets();
    unset($objPHPExcel);
    }
} /* End of class Pedigree */
?>
