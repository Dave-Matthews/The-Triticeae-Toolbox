<?php
/**
 * select lines by haplotype
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/andvanced_search.php
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

new Haplotype($_POST['function']);

/** Using a PHP class to implement the "Download Gateway" feature
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
class Haplotype
{
    public function __construct($function = null)
    {
        switch ($function) {
            case 'type1':
                $this->type1();
                break;
            case 'step1':
                $this->dispHaplo();
                break;
            case 'step2':
                $this->step2();
                break;
            case 'step3':
                $this->step3();
                break;
            case 'selLines':
                $this->step2_combine();
                break;
            default:
                $this->disp_framework();
                break;
            }
    }

 function combinations($num_markers, $marker_idx, $marker_list, $cross, $cross_c) {
   global $dispMissing;
   global $mysqli;
   $sub = $num_markers - 1; /* which column of marker_idx to increment */
   $i = 0;
   while ($i < $num_markers) {
     $markers[$marker_list[$i]] = 1;
     $i++;
   }
   foreach($_POST as $k=>$v) {
     if(strpos(strtolower($k), "marker") !== FALSE) {
       $check_list[$k] = 1;
     }
   }
   if (isset($_POST['dispMissing'])) {
     $dispMissing = 1;
   } else {
     $dispMissing = 0;
   }
   $marker_instr=" E.marker_uid in (".implode("," , array_keys($markers)).")";
   $in_these_lines = str_replace("line_records.", "E.", $in_these_lines);
   $query_str="select E.line_record_name, E.line_record_uid, E.marker_uid, E.alleles from allele_cache as E where
   $marker_instr $in_these_lines";
   //print $query_str;
   $result=mysqli_query($mysqli, $query_str) or die(mysqli_error());
   $lines = array();
   $line_uids=array();
   $line_names=array();
   while ($row=mysqli_fetch_assoc($result)) {
     $linename=$row['line_record_name'];
     $lineuid=$row['line_record_uid'];
     $mkruid=$row['marker_uid'];
     $alleleval=$row['alleles'];
     $line_uids[$linename]=$lineuid;
     $line_names[$lineuid]=$linename;
     if (! isset($lines[$linename])) $lines[$linename]=array();
     if (! isset($lines[$linename][$mkruid])) $lines[$linename][$mkruid]=$alleleval;
   }

   $i = 0;
   $markers = array();
   if ($num_markers > 1) { $loop1 = 4; } else { $loop1 = 1; }
   while ($i < $loop1) {
     $j = 0;
     $marker_idx[$sub] = 0;
     while ($j < 4) {
       $k = 0;
       $tmp1 = "";
       while ($k < $num_markers) {
         $alleles = $marker_idx[$k];
         if ($tmp1 == "") {
           $tmp1 = $marker_list[$k] . "_" . $cross[$k][$alleles];
           $tmp2 = $cross_c[$k][$alleles];
           $tmp3 = $cross[$k][$alleles];
         } else {
           $tmp1 = $tmp1 . "_" . $marker_list[$k] . "_" . $cross[$k][$alleles];
           $tmp2 = $tmp2 . "<td>" . $cross_c[$k][$alleles];
           $tmp3 = $tmp3 . $cross[$k][$alleles];
         }
         $markers[$marker_list[$k]] = $cross[$k][$alleles];
         $k++;
       }
       $unique = "marker_" . $tmp3;
       if (isset($check_list[$unique])) {
         $checked = "checked";
       } else {
         $checked = "";
       }

       $selLines=array();
       foreach ($lines as $lnm=>$lmks) {
         $flag=0;
         foreach ($markers as $mkr=>$val) {
           if (strtolower($lmks[$mkr])==strtolower($val)) {
           } else {
             //print strtolower($lmks[$mkr])."***".strtolower($val)."<br>";
             $flag++;
           }
         }
         if ($flag==0) {
           //print $lnm."<br>";
           array_push($selLines, $line_uids[$lnm]);
         }
       }
       $count_lines = count($selLines);
       if (count($selLines) > 0) {
         if ($dispMissing || (!preg_match('/--/',$tmp2))) {
           echo "<tr><td><input type='checkbox' name='marker_$tmp3' value = '$tmp1' $checked><td>$tmp2<td>" . count($selLines) . "\n";
         }
       }
       $marker_idx[$sub] = $marker_idx[$sub] + 1;
       $j++;
     }
     $marker_idx[$sub-1] = $marker_idx[$sub-1] + 1;
     $i++;
   }
 }

 function disp_framework() {
  global $config;
  include($config['root_dir'].'theme/normal_header.php');
  ?>
  <div id="title">
  <?php
  $this->check_session();
  ?>
  </div>
  <div id="step1" class="box">
  <?php 
  $this->dispHaplo();
  ?>
  </div>
  <div id="step2"></div></div>
  <?php 
  include($config['root_dir'].'theme/footer.php');
 }

 function check_session() {
		$tmp = count($_SESSION['clicked_buttons']);
		if ($tmp > 5) {
		  echo "$tmp markers selected<br>\n";
		  echo "Error - Please select no more than 5 markers<br>";
		  echo "<p><a href=genotyping/marker_selection.php>Select markers</a></p>";
		  return;
		}
		if (isset($_POST['dispMissing'])) {
		  $dispMissing = 1;
		} else {
		  $dispMissing = 0;
		}
 }

 /* for GBS data convert calls to ACTG */
 function dispHaplo() {
   global $mysqli;
   if (isset($_POST['dispMissing'])) {
     $dispMissing = 1;
   } else {
     $dispMissing = 0;
   }
   ?><form action="haplotype_search.php" method="post" name="myForm">
		<script type="text/javascript" src="downloads/downloads.js"></script>
	
		<h2>View Haplotypes</h2>

		<h3>Select haplotype combination</h3>
		    <div class="boxContent">
			<table id="haplotypeSelTab" class="tableclass1" cellpadding=0
			cellspacing=0>
			<thead>
			<tr>
			<th>Marker</th>
			<?php
			//clicked_buttons = the uids of the markers in the
			//current selection list.
			if(isset($_SESSION['clicked_buttons']) && count($_SESSION['clicked_buttons']) > 0) {
			 $i = 0;
			 $cross = array();
			 $crossc = array();
			 foreach($_SESSION['clicked_buttons'] as $marker) {

			  // Show Marker Name
                          $sql = "SELECT marker_name, A_allele, B_allele, marker_type_name FROM markers, marker_types
                          WHERE markers.marker_type_uid = marker_types.marker_type_uid
                          and marker_uid = ?";
                          if ($stmt = mysqli_prepare($mysqli, $sql)) {
                                                      mysqli_stmt_bind_param($stmt, "i", $marker);
                                                      mysqli_stmt_execute($stmt);
                                                      mysqli_stmt_bind_result($stmt, $marker_name, $a_allele, $b_allele, $mkrtyp);
                                                      mysqli_stmt_fetch($stmt);
						      echo "<th>$marker_name</th>";
                                                      $marker_ab=$a_allele . $b_allele;
                                                      mysqli_stmt_close($stmt);
                          }
			  // Show Alleles corresponding to the marker.
                          $sql = "SELECT DISTINCT allele_1, allele_2
                                                    FROM alleles, genotyping_data
                                                    WHERE genotyping_data.marker_uid = ? 
                                                    AND genotyping_data.genotyping_data_uid = alleles.genotyping_data_uid
                                                    ORDER BY allele_1 ASC";
                          if ($stmt = mysqli_prepare($mysqli, $sql)) {
                              mysqli_stmt_bind_param($stmt, "i", $marker);
                              mysqli_stmt_execute($stmt);
                              mysqli_stmt_bind_result($stmt, $allele_1, $allele_2); 
                              $j = 0;
                              while (mysqli_stmt_fetch($stmt)) {
			          $alleles = $allele_1.$allele_2;
                                  $alleles_c = $alleles;
                                  if (($mkrtyp == "GBS") || ($mkrtyp == "DArT Marker")) {
                                      if ($alleles=='AA') {
                                          $alleles_c = substr($marker_ab,0,1) . substr($marker_ab,0,1);
                                      } elseif ($alleles=='BB') {
                                          $alleles_c = substr($marker_ab,1,1) . substr($marker_ab,1,1);
                                      } elseif ($alleles=='AB') {
                                          $alleles_c = substr($marker_ab,0,1) . substr($marker_ab,1,1);
                                      } elseif ($alleles=='BA') {
                                          $alleles_c = substr($marker_ab,1,1) . substr($marker_ab,0,1);
                                      }
                                  }
				  $marker_list[$i] = $marker;
				  $marker_idx[$i] = 0;
				  $cross[$i][$j] = $alleles;
				  $crossc[$i][$j] = $alleles_c;
				  $j++;
                              }
                              mysqli_stmt_close($stmt);
                              $num_alleles[$i] = $j;
			      $i++;
			  } else {
			     echo "No Data Available $row[marker_name]";
			  }
			  echo "</td>\n";
		 }
						 $num_markers = $i;
						 if ($num_markers > 0) {
						  // calculate the number of times to call combinations function
						  $i = 0;
						  $total = 1;
						  while ($i < ($num_markers - 2)) {
						   $total = $total * 4;
						   $i++;
						  }
						  echo "<th>Number Lines\n";
						  $i = 0;
						  $current = $num_markers - 1;
						  $current2 = $num_markers - 3;
						  //echo "total $total";
						  while ($i < $total) {
						   $this->combinations($num_markers,$marker_idx,$marker_list,$cross,$crossc);
						   $marker_idx[$current2]++;
						   $j = $current2;
						   while ($j > 0) {
						    if ($marker_idx[$j] == 4) {
						     $marker_idx[$j] = 0;
						     $marker_idx[$j-1]++;
						    }
						    $j--;
						   }
						   $i++;
						  }
						 } else {
						  echo "<th>The current marker selection does not have genotyping data";
						 }
						 ?>
				
				
				</tbody>
			</table>
		<?php
		if ($dispMissing) {
		 echo "<input type='submit' name='hideMissing' value='Hide missing'> Hide haplotypes with missing data";
		} else {
		 echo "<input type='submit' name='dispMissing' value='Show missing'> Show haplotypes with missing data";
		}
		?>
		<p>
			<input type="button" name="haplotype" value="Save line selection"
				onclick="javascript: haplotype_step2();" /> Combine selected
			haplotype with currently selected lines
		</p></form>
		<?php
						}
						else {
						 echo "<td>No markers selected";
						 echo "</table><p><a href=genotyping/marker_selection.php>Select markers</a></p>";
						}
     echo "</div>";
 }

 function step2() {
   global $mysqli;
   print "<h2>Results of Select Haplotypes</h2>";
   $marker_query = "";
   foreach($_SESSION['clicked_buttons'] as $marker) {
     if (preg_match('/[A-Za-z0-9]+/',$marker)) {
       if ($marker_query == "") {
         $marker_query = $marker;
       } else {
         $marker_query = $marker_query . ",$marker";
       }
     }
   }
   //$marker_instr=" and D.marker_uid in (".implode("," , array_keys($markers)).")";
   $marker_instr=" and D.marker_uid in (".$marker_query.")";
   $in_these_lines = str_replace("line_records.", "A.", $in_these_lines);
   $query_str="select A.line_record_name, A.line_record_uid, D.marker_uid, E.allele_1, E.allele_2
   from line_records as A, tht_base as B, genotyping_data as C, markers as D, alleles as E
   where A.line_record_uid=B.line_record_uid and B.tht_base_uid=C.tht_base_uid and
   C.marker_uid=D.marker_uid and C.genotyping_data_uid=E.genotyping_data_uid
   $marker_instr $in_these_lines";
   //print $query_str;
   $result=mysqli_query($mysqli, $query_str) or die(mysqli_error());
   //print "Number of rows = ". mysql_num_rows($result) . "\n";
   $lines = array();
   $line_uids=array();
   $line_names=array();
   while ($row=mysqli_fetch_assoc($result)) {
     $linename=$row['line_record_name'];
     $lineuid=$row['line_record_uid'];
     $mkruid=$row['marker_uid'];
     $alleleval=$row['allele_1'].$row['allele_2'];
     $line_uids[$linename]=$lineuid;
     $line_names[$lineuid]=$linename;
     if (! isset($lines[$linename])) $lines[$linename]=array();
     if (! isset($lines[$linename][$mkruid])) $lines[$linename][$mkruid]=$alleleval;
   }
   $selLines=array();

   foreach($_POST as $k=>$v) {
     //print "$k $v<br>";
     if (strpos(strtolower($k), "marker") !== FALSE) {
       $tm = explode("_", $v);
       $i = 0;
       while ($i < count($tm)) {
         $markers[$tm[$i]] = $tm[$i+1];
         $i = $i + 2;
       }
       foreach ($lines as $lnm=>$lmks) {
         $flag=0;
         foreach ($markers as $mkr=>$val) {
           if (strtolower($lmks[$mkr])==strtolower($val)) {
           } else {
             $flag++;
           }
         }
         if ($flag==0) {
           //print $lnm."<br>";
           array_push($selLines, $line_uids[$lnm]);
         }
       }
     } else {
       continue;
     }
   }
   if (count($selLines) > 0) {
     if (count($_SESSION['selected_lines']) == 0) {
       $selected_lines = array();
       foreach($selLines as $line_uid) {
         if (!in_array($line_uid, $selected_lines)) {
           array_push($selected_lines, $line_uid);
         }
         $_SESSION['selected_lines'] = $selected_lines;
       }
       print "Currently selected Lines " . count($selLines) . "<br>\n";
       print "Currently Selected Markers: " . count($_SESSION['clicked_buttons']) . "<br><br>\n";
		//* <form action="pedigree/pedigree_markers.php" method="post">
		//*	<input type="submit"
		//*		value="Display Haplotype Data for Selected Lines and Markers">
		//* </form>
		//*<?php */
     } else {
       print "<table><tr><td>Currently Selected Lines:" . count($_SESSION['selected_lines']) . "<td>";
       print "Lines found:" . count($selLines) . "\n";
       print "<tr><td>";
       ?>
       <select name="lines" multiple="multiple" style="height: 12em;">
       <?php
       $selected_lines = $_SESSION['selected_lines'];
            foreach ($selected_lines as $line) {
              $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
              $res = mysqli_query($mysqli, $sql) or die(mysqli_error());
              $row = mysqli_fetch_assoc($res);
              $temp = $row['id'];
              $lines_list[$temp] = 1;
              echo "<option disabled=\"disabled\" value=\"" . $row['id'] . "\">" . $row['name'] . "</option>\n";
            }
       ?>
            </select>
            <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
            <?php
       foreach ($selLines as $line) {
         $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
              $res = mysqli_query($mysqli, $sql) or die(mysqli_error());
              $row = mysqli_fetch_assoc($res);
              $temp = $row['id'];
              $lines_list[$temp] = 1;
              ?>        
              <option disabled="disabled" value="<?php echo $row['id'] ?>">
              <?php echo $row['name'] ?>
              </option> 
              <?php     
            } 
       ?>          
       </select> 
      
       </table><br>
       <form name='lines' id='selectLines' action='haplotype_search.php' method='post'>
       <?php 
       $lines_str = implode(",",$selLines);
       ?>
		<input type="hidden" name="selLines" value="<?php echo $lines_str?>">
		<p>
			Combine with <font color=blue>currently selected lines</font>:<br> <input
				type="radio" name="selectWithin" value="Replace" checked>Replace<br>
			<input type="radio" name="selectWithin" value="Add">Add (OR)<br>
		        <input type="radio" name="selectWithin" value="Yes">Intersect (AND)<br>
		        <!--input type="submit" value="Combine" style='color: blue'-->
                        <input type="button" name="haplotype" value="Submit" onclick="javascript: haplotype_step2_combine()">
	
	</form>
	<?php
     }
   } else {
     echo "<p>Sorry, no records found<p>";
     $this->dispHaplo();
     //* print_r($_POST); */
   } 
 }
 
 function step2_combine() {
   $lines_str = $_POST['selLines'];
   $selLines = explode(",",$lines_str);
   //* print "Currently selected Lines " . count($selLines) . "<br>\n";
   //* print "Currently Selected Markers: " . count($_SESSION['clicked_buttons']) . "<br><br>\n"; */
   //*             <form action="pedigree/pedigree_markers.php" method="post">
   //*                     <input type="submit"
   //*                             value="Display Haplotype Data for Selected Lines and Markers">
   //*             </form>
   //*     <?php 
   //*     $selLines = $_POST['selLines']; */
           if ($_POST['selectWithin'] == "Replace") {
             $selected_lines = array();
             foreach($selLines as $line_uid) {
               array_push($selected_lines, $line_uid);
             }
             $_SESSION['selected_lines'] = $selected_lines;
           } elseif ($_POST['selectWithin'] == "Yes")
             $_SESSION['selected_lines'] = array_intersect($_SESSION['selected_lines'], $selLines);
           else {  // Add.
             $selected_lines = $_SESSION['selected_lines'];
             if (!isset($selected_lines))
             $selected_lines = array();
             foreach($selLines as $line_uid) {
               if (!in_array($line_uid, $selected_lines))
                 array_push($selected_lines, $line_uid);
               }
               $_SESSION['selected_lines'] = $selected_lines;
             }
             print "Currently Selected Lines: " . count($_SESSION['selected_lines']) . "<br>\n";
             print "Currently Selected Markers: " . count($_SESSION['clicked_buttons']) . "<br><br>\n"; 
 }
 
 /*
  * identify lines with particular phenotype values 
  */
 function phenoSearch() {
   global $mysqli;
   // Find all lines associated with the given phenotype data.
   print "<h2>Results of Search by Phenotype</h2>";
   $phenotype = $_POST['phenotype'];
   $selLines = $_POST['selLines'];
   $in_these_lines = "AND line_records.line_record_uid IN ( $selLines ) ";
   if (! isset($phenotype) || strlen($phenotype)<1) {
     print "<p><a href=\"".$_SERVER['PHP_SELF']."\">Go Back</a></p>";
     error(1,"Phenotype not set");
     die();
   }
   if(isset($_POST['na_value']) && $_POST['na_value'] != "") {	// no range specified, single value
     $value = $_POST['na_value'] == "" ? " " : $_POST['na_value'];
     $search_str="SELECT line_records.line_record_uid, line_record_name FROM line_records, tht_base, phenotype_data
     WHERE value REGEXP '$value'
     AND line_records.line_record_uid = tht_base.line_record_uid
     AND tht_base.tht_base_uid = phenotype_data.tht_base_uid
     AND phenotype_data.phenotype_uid = '$phenotype' $in_these_lines";
   
     //echo "<pre>$search_str\n\n\n\n\n\n</pre>";
   
     $search = mysqli_query($mysqli, $search_str) or die(mysqli_error());
     $compare = "na_value=$value";
   }
   else {
     $_SESSION['phenotype'] = $phenotype;
     $first = $_POST['first_value'] == "" ? getMaxMinPhenotype("min", $phenotype) : $_POST['first_value'];
     $last = $_POST['last_value'] == "" ? getMaxMinPhenotype("max", $phenotype) : $_POST['last_value'];
     $search_str="SELECT line_records.line_record_uid, line_record_name FROM line_records, tht_base, phenotype_data
     WHERE value BETWEEN $first AND $last
     AND line_records.line_record_uid = tht_base.line_record_uid
     AND tht_base.tht_base_uid = phenotype_data.tht_base_uid
     AND phenotype_data.phenotype_uid = '$phenotype' $in_these_lines";
   
     //echo "<pre>$search_str\n\n\n\n\n\n</pre>";
   
   
     $search = mysqli_query($mysqli, $search_str) or die(mysqli_error());
     $compare = "first_value=$first&last_value=$last";
   }
   $selLines = array();
   $linenames= array();
   if(mysqli_num_rows($search) > 0) {
     while($line = mysqli_fetch_assoc($search)) {
       array_push($selLines, $line['line_record_uid']);
       $linenames[$line['line_record_uid']] =$line['line_record_name'];
     }
   }
   if(count($selLines) > 0) {
     sort($selLines);
     $_SESSION['selected_lines']=array();
     foreach ($selLines as $sline) {
       if (! in_array($sline, $_SESSION['selected_lines'])) array_push($_SESSION['selected_lines'], $sline);
     }
     $selLines=$_SESSION['selected_lines'];
     print "Lines filtered by phenotype: " . count($selLines) . "<br><br>\n";
     print "<table class='tableclass1' style=\"float: left;\"><thead><tr><th><b>Lines found</b></th></tr></thead><tbody>";
     foreach ($selLines as $luid) {
       print "<tr><td>";
       print "<a href=\"pedigree/show_pedigree.php?line=$luid\">".$linenames[$luid]."</a>";
       print "</td></tr>";
   }
   print "</tbody></table>";
       //print "<div style='float: left; margin-left: 10px;'>";
       //print "<p><a href=\"pedigree/pedigree_markers.php\">Compare the alleles for all selected markers for these lines</a></p>";
       //print "<p><a href=\"phenotype/compare.php?phenotype=$phenotype&$compare\">Narrow your results by phenotype</a></p>";
       //print "<p><a href=\"advanced_search.php?searchtype=idMkrs\">Identify Markers that are identical for these lines</a></p>";
       //            print "<div id='ajaxMsg'></div>";
       //    print "<input type=\"button\" id=\"storeLineButton\" value=\"Store Line Names\" onclick=\"callAjaxFunc('ajaxSessionVariableFunc','&action=store&svkey=selected_lines',this.id)\" >";
       //print "</div><div style='clear: left;'></div>";
       }
       else {
       echo "<p>Sorry, no records found in lines with selected phenotype value<p>";
   }
 }
 
 function step3() {
   $lines_str = $_POST['selLines'];
   $selLines = explode(",",$lines_str);
   ?>
   <form action="haplotype_search.php" method="post">
   <h3> Optionally, also select a trait </h3>
   <input type="hidden" name=selLines value=" <?php echo $lines_str ?> ">
   <div id="phenotypeSel">
   <table id="phenotypeSelTab" class="tableclass1" cellspacing=0; cellpadding=0;>
   <thead>
   <tr>
   <th>Category</th>
   <th>Trait</th>
   <th>Experiment</th>
   </tr>
   </thead>
   <tbody>
   <tr class="nohover">
   <td>
   <select name='phenotypecategory' size=10 onfocus="DispPhenoSel(this.value, 'Category')" onchange="DispPhenoSel(this.value, 'Category')">
   <?php showTableOptions("phenotype_category"); ?>
   </select>
   </td>
   <td><p>Select a trait category from the left most column</p>
   </td>
   <td></td>
   </tr>
   <tr>
   <td>&nbsp;</td><td></td><td></td>
   </tr>
   </tbody>
   </table>
   </div>
   </form>
   </div>
   </div>
   <?php 
 }
 
}
