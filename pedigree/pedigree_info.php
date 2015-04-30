<?php
/**
 * Pedigree Info
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <cbirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
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
	
  // The wrapper action for the type1 download. Handles outputting the header
  // and footer and calls the first real action of the type1 download.
  private function typeLine() {
    global $config;
    include($config['root_dir'].'theme/normal_header.php');
    echo " <h2> Line Information</h2>";
    $this->type_LineInformation();
    echo "<h3> <a href='pedigree/line_properties.php'> New Line Search</a></h3>";
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }

  private function type_LineInformation() {
    // If we clicked on the button for Lines Found, retrieve that cookie instead.
    if ($_GET['lf'] == "yes") {
      $linelist = $_SESSION['linesfound'];
      // Flag for the Download Line Data button to use:
      $lf = "&lf=yes";
    }
    else 
      $linelist = $_SESSION['selected_lines'];
    // Find which Properties this set of lines has any values for.
    $ourprops = array(); 
    foreach ($linelist as $lineuid) {
      $propresult = mysql_query("select property_uid
	 from line_properties lp, property_values pv
	 where lp.property_value_uid = pv.property_values_uid
	 and lp.line_record_uid = $lineuid");
      while ($pr = mysql_fetch_assoc($propresult)) 
	if (!in_array($pr['property_uid'], $ourprops)) 
	  $ourprops[] = $pr['property_uid'];  // array of uids
    }
?>

<script type="text/javascript">
function load_excel() {
      var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeLineExcel<?php echo $lf;?>';
      // Opens the url in the same window
      window.open(url, "_self");
}
</script>

<style type="text/css">
    th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
    table {background: none; border-collapse: collapse}
    td {border: 1px solid #eee !important;}
    table.marker {background: none; border-collapse: collapse}
    th.marker {background: #5b53a6; color: #fff; padding: 5px 0; border: 0; border-color: #fff}
    td.marker {padding: 5px 0; border: 0 !important;}
    h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<div style="width: 940px;">
  <table style="table-layout:fixed; width: 940px">
    <tr> 
      <th style="width: 80px;" class="marker">Name</th>
      <th style="width: 50px;" class="marker">GRIN</th>
      <th style="width: 80px;" class="marker">Synonym</th>
      <th style="width: 40px;" class="marker">Breeding Program</th>
      <th style="width: 80px;" class="marker">Pedigree</th>
      <th style="width: 40px;" class="marker">Gener ation</th>
      <th style="width: 80px;" class="marker">Comment</th>
<?php 
 foreach ($ourprops as $pr) {
      $prname = mysql_grab("select name from properties where properties_uid = $pr");
      echo "<th style='width: 50px; word-wrap: break-word' class=marker>".$prname."</th>";
    }
?>
      <th style="width: 80px;" class="marker">Data<br>Available</th>
    </tr>
  </table>
 </div>

<div style="padding: 0; height: 400px; overflow-y: scroll; border: 1px solid #5b53a6; clear: both">
<table style="table-layout:fixed; width: 940px">	
<?php
    // Get the data for each line.
    foreach ($linelist as $lineuid) {
      $result=mysql_query("select line_record_name, breeding_program_code, pedigree_string, generation, description from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
      $syn_result=mysql_query("select line_synonym_name from line_synonyms where line_record_uid=$lineuid") or die("No Synonym\n");
      $syn_names=""; $sn = "";
      while ($syn_row = mysql_fetch_assoc($syn_result)) 
	$syn_names[] = $syn_row['line_synonym_name'];
      if (is_array($syn_names))
	$sn = implode(', ', $syn_names);

      $grin_result=mysql_query("select barley_ref_number from barley_pedigree_catalog_ref 
           where line_record_uid=$lineuid") or die(mysql_error());
      $grin_names=""; $gr = "";
      while ($grin_row = mysql_fetch_assoc($grin_result)) 
	$grin_names[] = $grin_row['barley_ref_number'];
      if (is_array($grin_names))
	$gr = implode(', ', $grin_names);

      while ($row=mysql_fetch_assoc($result)) {
?>
	<tr>
        <td style="width: 80px; text-align: center" class="marker">
        <?php $line_name = $row['line_record_name'];
	   $GETable_name = str_replace('#', '%23', $line_name);
	echo "<a href='pedigree/show_pedigree.php?line=$GETable_name'>$line_name</a>" ?>
        <td style="width: 50px; text-align: center" class="marker">
        <?php echo $gr ?>
        <td style="width: 80px; text-align: center" class="marker">
        <?php echo $sn ?>
        <td style="width: 40px; text-align: center" class="marker">
        <?php echo $row['breeding_program_code'] ?>
        <td style="width: 80px; text-align: center; word-wrap: break-word" class="marker">
        <?php
        echo $row['pedigree_string'] ?>
        <td style="width: 40px; text-align: center" class="marker">
        <?php echo $row['generation'] ?>
        <td style="width: 80px; text-align: center" class="marker">
        <?php echo $row['description'] ?>
	  <?php
	  foreach ($ourprops as $pr) {
	  $propval = mysql_grab("select value
	     from line_properties lp, property_values pv
	     where lp.property_value_uid = pv.property_values_uid
	     and pv.property_uid = $pr
	     and lp.line_record_uid = $lineuid");
	  echo "<td style='width: 50px;text-align: center' class='marker'>".$propval."</td>";
	}
	?>
        <td style="width: 80px; text-align: center" class="marker">
        <?php $phenotype = lineHasPhenotypeData($lineuid);
	$genotype = lineHasGenotypeData($lineuid);
	if($phenotype AND $genotype) echo "Phenotype<br>Genotype";
	if($phenotype AND !$genotype) echo "Phenotype";
	if($genotype AND !$phenotype) echo "Genotype";
	if(!$phenotype AND !$genotype) echo "None";
	 ?>
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
  // If we clicked on the button for Lines Found, retrieve that cookie instead.
  if ($_GET['lf'] == "yes") 
    $linelist = $_SESSION['linesfound'];
  else 
    $linelist = $_SESSION['selected_lines'];
  $sample = implode(",", $linelist);
  $linelist = explode(",", $sample);
  // Get the Genetic Characters known for this set of lines.
  $ourprops = array();
  foreach ($linelist as $lineuid) {
    $propresult = mysql_query("select property_uid                                                   
         from line_properties lp, property_values pv                                                   
         where lp.property_value_uid = pv.property_values_uid                                          
         and lp.line_record_uid = $lineuid") or die(mysql_error());
    while ($pr = mysql_fetch_assoc($propresult))
      if (!in_array($pr['property_uid'], $ourprops))
	$ourprops[] = $pr['property_uid'];  // array of uids                                         
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
  // Freeze row 1 and column 1 from scrolling. Set some columns wider.
  /* $objPHPExcel->getActiveSheet()->freezePane('A2'); */
  $objPHPExcel->getActiveSheet()->freezePane('B2');
  $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
  $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
  $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
  $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
  $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
  $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
  $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style_header);
  $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Name');
  $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'GRIN');
  $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Synonym');
  $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Program');
  $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Pedigree');
  $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Generation');
  $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Comment');
  // Add columns for line Properties.
  // Oops, columns will go way past Z.
  /* $firstprop = ord(I);  // First property column is "I". */
  $firstprop = 7; // First property column is 7.
  for ($i = 0; $i < count($ourprops); $i++) {
    /* $colname = chr($firstprop + $i); */
    $col = $firstprop + $i;
    $prname = mysql_grab("select name from properties where properties_uid = $ourprops[$i]");
    /* $objPHPExcel->getActiveSheet()->getColumnDimension($colname)->setWidth(7); */
    /* $objPHPExcel->getActiveSheet()->SetCellValue($colname.'1', "$prname"); */
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, "$prname");
  }

  $i = 2;
  # start by opening a query string
    while ($tok !== false) {
      $lineuid = (int)$tok;
      $result=mysql_query("select line_record_name, breeding_program_code, pedigree_string, generation, 
           description from line_records where line_record_uid=\"$lineuid\" ") or die(mysql_error());
      $tok = strtok(",");
      while ($row = mysql_fetch_assoc($result)) {
	$objPHPExcel->getActiveSheet()->SetCellValue("A$i", "$row[line_record_name]",$format_row);
	$objPHPExcel->getActiveSheet()->SetCellValue("D$i", "$row[breeding_program_code]",$format_row);
	$objPHPExcel->getActiveSheet()->SetCellValue("E$i", "$row[pedigree_string]",$format_row);
	$objPHPExcel->getActiveSheet()->SetCellValue("F$i", "$row[generation]",$format_row);
	$objPHPExcel->getActiveSheet()->SetCellValue("G$i", "$row[description]",$format_row);
      }

      // GRIN Accession
      $grin_result=mysql_query("select barley_ref_number from barley_pedigree_catalog_ref 
           where line_record_uid=$lineuid") or die(mysql_error());
      $grin_names=""; $gr = "";
      while ($grin_row = mysql_fetch_assoc($grin_result)) 
	$grin_names[] = $grin_row['barley_ref_number'];
      if (is_array($grin_names))
	$gr = implode(', ', $grin_names);
      $objPHPExcel->getActiveSheet()->SetCellValue("B$i", "$gr",$format_row);

      // Synonyms
      $syn_result=mysql_query("select line_synonym_name from line_synonyms 
            where line_record_uid=$lineuid") or die(mysql_error());
      $syn_names=""; $sn="";
      while ($syn_row = mysql_fetch_assoc($syn_result)) 
	$syn_names[] = $syn_row['line_synonym_name'];
      if (is_array($syn_names))
	$sn = implode(', ', $syn_names);
      $objPHPExcel->getActiveSheet()->SetCellValue("C$i", "$sn",$format_row);

      // Properties
      for ($j = 0; $j < count($ourprops); $j++) {
	$propval = mysql_grab("select value                                                                                      
             from line_properties lp, property_values pv
             where lp.property_value_uid = pv.property_values_uid
             and pv.property_uid = $ourprops[$j]
             and lp.line_record_uid = $lineuid");
	/* $colname = chr($firstprop + $j); */
	/* $objPHPExcel->getActiveSheet()->SetCellValue($colname.$i, "$propval",$format_row); */
	$col = $firstprop + $j;
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $i, "$propval", $format_row);
      }
      $i++;
    } // end of while ($tok !== false)

  header('Content-type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="Line_Details.xls"');
  $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
  $objWriter->save('php://output');
  $objPHPExcel->disconnectWorksheets();
  unset($objPHPExcel);
}

} /* End of class Pedigree */
