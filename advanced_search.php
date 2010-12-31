<?php

/*
 * Logged in page initialization
 */
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();
?>

<div id="primaryContentContainer">
<div id="primaryContent">
	<div class="box">
	<?php

  // dem 31dec10: We may want to use the 'selectWithin' option.
//      /* Search Within  certain Lines */
//     $in_these_lines = "";
//     if((is_array($_SESSION['selected_lines'])) && (count($_SESSION['selected_lines']) > 0) && ($_REQUEST['selectWithin'] == "Yes") ) {
//                 $_GET['selectWithin'] = "Yes";
// 		$in_these_lines = "AND line_records.line_record_uid IN (" . implode(",", $_SESSION['selected_lines']) . ")";
//     }
//     if($_POST['rowType'] != "ignore") {
//                 $in_these_lines .= " AND line_records.row_type =" . $_POST['rowType'] ;
//     }
//     if($_POST['variety'] != "ignore") {
//                 $in_these_lines .= " AND line_records.variety = '" . $_POST['variety']  . "'";
//     }
//     if($_POST['primary_end_use'] != "ignore") {
//                 $in_these_lines .= " AND line_records.primary_end_use REGEXP '" . $_POST['primary_end_use']  . "'";
//     }


	/* identify lines with the same marker haplotypes */
    if(isset($_POST['haplotype'])) {
		print "<h2>Results of Search by Haplotype</h2>";
    		/* Get the Marker Uids */
			$markers = array();
			foreach($_POST as $k=>$v) {
				if(strpos(strtolower($k), "marker") !== FALSE) {
				  // example "marker_784=>BB"
					$tm = explode("_", $k);
                                        if($v != "Any")
					   $markers[$tm[1]] = $v;
				}
			}
			$marker_instr=" and D.marker_uid in (".implode("," , array_keys($markers)).")";
			if(count($markers) < 1) {
                                warning("No marker values selected");
                                $marker_instr="";
			}
			$in_these_lines = str_replace("line_records.", "A.", $in_these_lines);
			$query_str="select A.line_record_name, A.line_record_uid, D.marker_uid, E.allele_1, E.allele_2
						from line_records as A, tht_base as B, genotyping_data as C, markers as D, alleles as E
						where A.line_record_uid=B.line_record_uid and B.tht_base_uid=C.tht_base_uid and
						C.marker_uid=D.marker_uid and C.genotyping_data_uid=E.genotyping_data_uid
                        $marker_instr $in_these_lines";
			// print $query_str;
			$result=mysql_query($query_str) or die(mysql_error());
			//print "Number of rows = ". mysql_num_rows($result) . "\n";
			$lines = array();
			$line_uids=array();
			$line_names=array();
			while ($row=mysql_fetch_assoc($result)) {
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
			foreach ($lines as $lnm=>$lmks) {
				$flag=0;
				foreach ($markers as $mkr=>$val) {
					if (strtolower($lmks[$mkr])!==strtolower($val)) {
						// print strtolower($lmks[$mkr])."***".strtolower($val)."<br>";
						$flag++;
					}
				}
				if ($flag==0) {
					// print $lnm."<br>";
					array_push($selLines, $line_uids[$lnm]);
				}
			}
			if(count($selLines) > 0) {
				$_SESSION['selected_lines']=array();
				foreach ($selLines as $sline) {
					if (! in_array($sline, $_SESSION['selected_lines'])) array_push($_SESSION['selected_lines'], $sline);
				}
				$selLines=$_SESSION['selected_lines'];
				sort($selLines);
				print "<table class='tableclass1' style=\"float: left;\"><thead><tr><th><b>Lines found</b></th></tr></thead><tbody>";
				foreach ($selLines as $luid) {
				  print "<tr><td style='padding: 1px'>";
					print "<a href=\"pedigree/show_pedigree.php?line=$luid\">".$line_names[$luid]."</a>";
					print "</td></tr>";
				}
				print "</tbody></table>";
				print "<div style='float: left; margin-left: 10px;'>";
				print "<p><a href=\"pedigree/pedigree_markers.php\">Display the haplotypes</a>";
				print "<p><a href=\"advanced_search.php?searchtype=idMkrs\">Identify markers that are identical for these lines</a><br>";
				print "<div id='ajaxMsg'></div>";
				print "<p><input type=\"button\" id=\"storeLineButton\" value=\"Store line names\" onclick=\"callAjaxFunc('ajaxSessionVariableFunc','&action=store&svkey=selected_lines',this.id)\" >";
				print "</div><div style='clear: left;'></div>";
				print "<p><hr><p>";
			}
			else {
				echo "<p>Sorry, no records found<p>";
				//print_r($_POST);
			}
	}

	/* identify lines with particular phenotype values */
	if(isset($_POST['phenoSearch'])) {
		// Find all lines associated with the given phenotype data.
		print "<h2>Results of Search by Phenotype</h2>";
		$phenotype = $_POST['phenotype'];
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

			$search = mysql_query($search_str) or die("Error with ".$search_str);
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


			$search = mysql_query($search_str) or die("Error with ".$search_str);
			$compare = "first_value=$first&last_value=$last";
		}
		$selLines = array();
		$linenames= array();
		if(mysql_num_rows($search) > 0) {
			while($line = mysql_fetch_assoc($search)) {
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
			print "<table class='tableclass1' style=\"float: left;\"><thead><tr><th><b>Lines found</b></th></tr></thead><tbody>";
			foreach ($selLines as $luid) {
					print "<tr><td>";
					print "<a href=\"pedigree/show_pedigree.php?line=$luid\">".$linenames[$luid]."</a>";
					print "</td></tr>";
			}
			print "</tbody></table>";
			print "<div style='float: left; margin-left: 10px;'>";
			print "<p><a href=\"pedigree/pedigree_markers.php\">Compare the alleles for all selected markers for these lines</a></p>";
			print "<p><a href=\"phenotype/compare.php?phenotype=$phenotype&$compare\">Narrow your results by phenotype</a></p>";
			print "<p><a href=\"advanced_search.php?searchtype=idMkrs\">Identify Markers that are identical for these lines</a></p>";
                        print "<div id='ajaxMsg'></div>";
		        print "<input type=\"button\" id=\"storeLineButton\" value=\"Store Line Names\" onclick=\"callAjaxFunc('ajaxSessionVariableFunc','&action=store&svkey=selected_lines',this.id)\" >";
			print "</div><div style='clear: left;'></div>";
		}
		else {
			echo "<p>Sorry, no records found<p>";
		}
	}
	/* identify markers that are identical to all the lines */
	if (isset($_GET['searchtype']) && $_GET['searchtype']=="idMkrs") {
		print "<h2>Identify identical markers for the selected lines</h2>";
		$lines=$_SESSION['selected_lines'];
		$lines_instr=implode(",", $lines);
		$query_str="select A.line_record_name, A.line_record_uid, D.marker_uid, allele_1, allele_2
				from line_records as A, tht_base as B, genotyping_data as C, markers as D, alleles as E
				where A.line_record_uid=B.line_record_uid and B.tht_base_uid=C.tht_base_uid and
				C.marker_uid=D.marker_uid and C.genotyping_data_uid=E.genotyping_data_uid and
				A.line_record_uid in (".$lines_instr.")";

		$result=mysql_query($query_str);
		$lines = array();
		$line_uids=array();
		$line_names=array();
		$mkrs=array();
		while ($row=mysql_fetch_assoc($result)) {
			$linename=$row['line_record_name'];
			$lineuid=$row['line_record_uid'];
			$mkruid=$row['marker_uid'];
			$alleleval=$row['allele_1'].$row['allele_2'];
			$line_uids[$linename]=$lineuid;
			$line_names[$lineuid]=$linename;
			if (! isset($lines[$linename])) $lines[$linename]=array();
			if (! isset($lines[$linename][$mkruid])) $lines[$linename][$mkruid]=$alleleval;
			if (! isset($mkrs[$mkruid])) $mkrs[$mkruid]=1;
		}
		$selMkrs=array();
		foreach ($mkrs as $mkr=>$temp) {
			$flag=0;
			$flagval="";
			foreach ($lines as $lnm=>$lmks) {
				if (! isset($lmks[$mkr]) || strlen($lmks[$mkr])<1) continue;
				else {
					if (strlen($flagval)<1) $flagval=$lmks[$mkr];
					else {
						if ($flagval!==$lmks[$mkr]) {
							// print $flagval." ".$lmks[$mkr]."<br>";
							$flag=1;
							break;
						}
					}
				}
			}
			// print $mkr." ".$flag." ".$flagval."<br>";
			if ($flag==0 && strlen($flagval)>0) array_push($selMkrs, $mkr);
		}
		if (count($selMkrs)<1) {
			print "<p>No identical markers found for the selected lines</p>";
		}
		else {
			$_SESSION['clicked_buttons']=$selMkrs;
			print "<p><a href=\"pedigree/pedigree_markers.php\">Display the lines and markers</a>";
		}

	}
	?>
	</div>
	<div class="box">
	<h2>View Haplotypes</h2>
	  <!-- Cannot jump directly to pedigree_markers.php, the alleles are not updated.
	<form action="pedigree/pedigree_markers.php" method="post">
	  -->
	<form action="advanced_search.php" method="post"> 

	<h3>Select haplotype</h3>
	<div class="boxContent">
	<table id="haplotypeSelTab" class="tableclass1" cellpadding=0 cellspacing=0>
	<thead>
	<tr>
		<th>Marker</th>
		<th>Allele</th>
		<th>Marker</th>
		<th>Allele</th>
		<th>Marker</th>
		<th>Allele</th>
		<th>Marker</th>
		<th>Allele</th>
		<th>Marker</th>
		<th>Allele</th>
		<th>Marker</th>
		<th>Allele</th>
	</tr>
	</thead>
	</tbody>
	<tr>
	<?php
									 //clicked_buttons = the uids of the markers in the 
									 //current selection list.
		if(isset($_SESSION['clicked_buttons']) && count($_SESSION['clicked_buttons']) > 0) {
			$i = 0;
			foreach($_SESSION['clicked_buttons'] as $marker) {
				if($i % 6 == 0 && $i != 0)
					echo "\t</tr>\n\t<tr>";

				// Show Marker Name
				$nme = mysql_query("SELECT marker_name FROM markers WHERE marker_uid = $marker") or die(mysql_error());
				$row = mysql_fetch_assoc($nme);
				echo "\n\t<td>$row[marker_name]</td>\n\t<td>";

				// Show Alleles corresponding to the marker.
				$allele = mysql_query("SELECT DISTINCT allele_1, allele_2
							FROM alleles, genotyping_data
							WHERE genotyping_data.marker_uid = $marker
								AND genotyping_data.genotyping_data_uid = alleles.genotyping_data_uid
							ORDER BY allele_1 ASC
						") or die(mysql_error());
				if(mysql_num_rows($allele) > 0) {
				  echo "<select name='marker_$marker' style='width: 80px;'>";
				  while ($row = mysql_fetch_assoc($allele)) {
				    $alleles = $row[allele_1].$row[allele_2];
				    if ($alleles == $_POST['marker_'.$marker]) {
				      echo "<option selected value=\"$alleles\">$alleles</option>";
				    }
				    else {
				      echo "<option value=\"$alleles\">$alleles</option>";
				    }
				  }
				  if ($_POST['marker_'.$marker] == "Any") {
  				      echo "<option selected value=\"Any\">Any</option>";
  				    }
				  else {
				  echo "<option value=\"Any\">Any</option>";
				  }
				  echo "</select>";

				}
				else {
					echo "No Data Available";
				}

				echo "</td>\n";
				$i++;
			}
			echo "</tr>";
		}
		else {
		  echo "<td>No markers selected";
		}
	?>
	</tbody>
	</table>
	<p><a href="genotyping/marker_selection.php">Select markers</a></p>
	</div>

	  <h3> Optionally, also select a phenotype </h3>
	<div id="phenotypeSel">
	<table id="phenotypeSelTab" class="tableclass1" cellspacing=0; cellpadding=0;>
	<thead>
	<tr>
		<th>Category</th>
		<th>Phenotype</th>
		<th>Experiment</th>
	</tr>
	</thead>
	<tbody>
	<tr class="nohover">
		<td>
			<select name='phenotypecategory' size=10 onfocus="DispPhenoSel(this.value, 'Category')" onchange="DispPhenoSel(this.value, 'Category')">;
			<?php showTableOptions("phenotype_category"); ?>
			</select>
		</td>
		<td><p>Select a phenotype category from the left most column</p>
		</td>
		<td></td>
	</tr>
	<tr>
		<td>&nbsp;</td><td></td><td></td>
        </tr>
	</tbody>
	</table>
	</div>

        <p>Note: the results of this search will replace any lines that are currently remembered for you.</p>
	<p><input type="submit" name="haplotype" value="Search"></p>

	</form>
		</div>
	</div>
</div>
</div>

<?php include("theme/footer.php");?>
