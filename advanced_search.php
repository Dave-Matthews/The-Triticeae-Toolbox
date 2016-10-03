<?php
/**
 * select lines by haplotype
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/andvanced_search.php
 */
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
include $config['root_dir'].'theme/normal_header.php';
$mysqli = connecti();
?>

<div id="primaryContentContainer">
<div id="primaryContent">
	<div class="box">
	<?php

	/**
	 * generates combination of haplotypes
	 * @param integer $num_markers number of markers
	 * @param array $marker_idx index of marker/allele combinations
	 * @param array $marker_list list of selected markers
	 * @param array $cross 2D array of all allele combinations
	 */
	function combinations($num_markers, $marker_idx, $marker_list, $cross) { 
          global $mysqli;
	  global $dispMissing;
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
          $result=mysqli_query($mysqli, $query_str) or die(mysqli_error($mysqli));
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
          while ($i < 4) {
            $j = 0;
            $marker_idx[$sub] = 0;
            while ($j < 4) {
              $k = 0;
              $tmp1 = "";
              while ($k < $num_markers) {
                $alleles = $marker_idx[$k];
                if ($tmp1 == "") {
                  $tmp1 = $marker_list[$k] . "_" . $cross[$k][$alleles];
                  $tmp2 = $cross[$k][$alleles];
		  $tmp3 = $cross[$k][$alleles];
                } else {
                  $tmp1 = $tmp1 . "_" . $marker_list[$k] . "_" . $cross[$k][$alleles];
                  $tmp2 = $tmp2 . "<td>" . $cross[$k][$alleles];
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

	/* identify lines with the same marker haplotypes */
    if(isset($_POST['haplotype'])) {
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
			  $result=mysqli_query($mysqli, $query_str) or die(mysqli_error($mysqli));
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
			    ?>
			    <form action="pedigree/pedigree_markers.php" method="post">
			    <input type="submit" value="Display Data for Selected Lines and Markers">
			    </form>
			    <?php
			  } else {
                            print "<table><tr><td>Currently Selected Lines:<td>" . count($_SESSION['selected_lines']) . "\n";
			    print "<tr><td>Lines found:<td>" . count($selLines) . "\n";
			    print "</table><br>";
			    echo "<form name='lines' id='selectLines' action='advanced_search.php' method='post'>";
			    $lines_str = implode(",",$selLines);
			    ?>
			    <input type="hidden" name="selLines" value="<?php echo $lines_str?>">
			    <p>Combine with <font color=blue>currently selected lines</font>:<br>
			    <input type="radio" name="selectWithin" value="Replace" checked>Replace<br>
			    <input type="radio" name="selectWithin" value="Add">Add (OR)<br>
			    <input type="radio" name="selectWithin" value="Yes">Intersect (AND)<br>
			    <input type="submit" value="Combine" style='color:blue'>
			    </form>
			    <?php
                          }
			} else {
				echo "<p>Sorry, no records found<p>";
				//print_r($_POST);
			}
		} elseif (count($_SESSION['selected_lines']) > 0) {
                  print "<a href=pedigree/line_properties.php>Currently Selected Lines:</a> " . count($_SESSION['selected_lines']) . "<br>\n";
                  print "Currently Selected Markers: " . count($_SESSION['clicked_buttons']) . "<br>\n";
                  if (isset($_SESSION['phenotype'])) {
                    $ntraits=substr_count($_SESSION['phenotype'], ',')+1;
                    print "<a href=pedigree/phenotype_selection.php>Currently Selected Traits:</a> " . $ntraits . "<br><br>\n";
                  } else {
                    print "<br>\n";
                  }
		?>
		<form action="pedigree/pedigree_markers.php" method="post">
		<input type="submit" value="Display Data for Selected Lines and Markers">
		</form>
		<?php
                }
                // print "<p><a href=\"pedigree/pedigree_markers.php\">Display Data for Selected Lines and Markers</a>";
		echo "</div>";
                print "</div><div style='clear: left;'></div>";
                print "<p><hr><p>";

        if (isset($_POST['selLines'])) {
        //   print "<h2>Combine Lines</h2>\n";
        $lines_str = $_POST['selLines'];
        $selLines = explode(",",$lines_str);
	   //$selLines = $_POST['selLines'];
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
             //print "<table><tr><td>Currently Selected Lines:<td>" . count($_SESSION['selected_lines']) . "</table>\n";
           }

	/* identify lines with particular phenotype values */
	if(isset($_POST['phenoSearch'])) {
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

			$search = mysqli_query($mysqli, $search_str) or die("Error with ".$search_str);
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


			$search = mysqli_query($mysqli, $search_str) or die("Error with ".$search_str);
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

		$result=mysqli_query($mysqli, $query_str) or die($sql);
		$lines = array();
		$line_uids=array();
		$line_names=array();
		$mkrs=array();
		while ($row=mysqli_fetch_assoc($result)) {
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
	  <!-- Cannot jump directly to pedigree_markers.php, the alleles are not updated.
	<form action="pedigree/pedigree_markers.php" method="post">
	  -->
	<form action="advanced_search.php" method="post"> 
	<?php  
        $tmp = count($_SESSION['clicked_buttons']);
        if ($tmp > 5) {
           echo "$tmp markers selected<br>\n";
           echo "Error - Please select no more than 5 markers<br>";
           echo "<p><a href=genotyping/marker_selection.php>Select markers</a></p>";
           return;
        }
	if (count($selLines) == 0) {
	?>
	<h2>View Haplotypes</h2>

	<h3>Select haplotype combination</h3>
	<div class="boxContent">
	<table id="haplotypeSelTab" class="tableclass1" cellpadding=0 cellspacing=0>
	<thead>
	<tr>
		<th>Marker</th>
	<?php
									 //clicked_buttons = the uids of the markers in the 
									 //current selection list.
		if(isset($_SESSION['clicked_buttons']) && count($_SESSION['clicked_buttons']) > 0) {
			$i = 0;
		  	$cross = array();
			foreach($_SESSION['clicked_buttons'] as $marker) {

				// Show Marker Name
				$nme = mysqli_query($mysqli, "SELECT marker_name FROM markers WHERE marker_uid = $marker") or die(mysqli_error($mysqli));
				$row = mysqli_fetch_assoc($nme);
				echo "<th>$row[marker_name]</th>";

				// Show Alleles corresponding to the marker.
				$allele = mysqli_query($mysqli, "SELECT DISTINCT allele_1, allele_2
                                                        FROM alleles, genotyping_data
                                                        WHERE genotyping_data.marker_uid = $marker
                                                                AND genotyping_data.genotyping_data_uid = alleles.genotyping_data_uid
                                                        ORDER BY allele_1 ASC
                                                ") or die(mysqli_error($mysqli));
				if(mysqli_num_rows($allele) > 0) {
                                  $j = 0;
				  while ($row = mysqli_fetch_assoc($allele)) {
				    $alleles = $row[allele_1].$row[allele_2];
                                    $marker_list[$i] = $marker;
				    $marker_idx[$i] = 0;
                                    $cross[$i][$j] = $alleles;
 				    $j++;
				  }
                                  $num_alleles[$i] = $j;
				  $i++;
				}
				else {
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
			    combinations($num_markers,$marker_idx,$marker_list,$cross);
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
			</tbody></table></div>
			<?php
			if ($dispMissing) {
                            echo "<input type='submit' name='hideMissing' value='Hide missing'> Hide haplotypes with missing data";
                        } else {
                            echo "<input type='submit' name='dispMissing' value='Show missing'> Show haplotypes with missing data";
                        }
		        ?>
			<p><input type="submit" name="haplotype" value="Submit"> Combine selected haplotype with currently selected lines</p>
			<?php 
		}
		else {
		  echo "<td>No markers selected";
		  echo "</table></div><p><a href=genotyping/marker_selection.php>Select markers</a></p>";
		}
	}
	if ((isset($_POST['haplotype']) || isset($_POST['selLines'])) && !isset($_POST['phenoSearch'])) { 
	  $lines_str = implode(",",$selLines);
	  ?> 
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
    <?php 
	} 
	?>
	</form>
		</div>
	</div>
	
</div>
</div>

<?php include("theme/footer.php");?>
