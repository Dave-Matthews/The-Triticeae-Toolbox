<?php
/**
 * Quick search
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/search.php
 * 
 */
	include("includes/bootstrap.inc");
	connect();

	include("theme/normal_header.php");
?>


<div id="primaryContentContainer">
	<div id="primaryContent">
		<div class="box">
		<h2>Search <?php echo beautifulTableName($_REQUEST['table'], 1) ?></h2>
			<div class="boxContent">

	<?php 

		//phenotype search has been made.
		if(isset($_POST['phenotypecategory'])) {
			// Find all lines associated with the given phenotype data.

			$phenotype = $_POST['phenotype'];

			if(isset($_POST['na_value']) && $_POST['na_value'] != "") {	// no range specified, single value

				$value = $_POST['na_value'] == "" ? " " : $_POST['na_value'];
				$search = mysql_query("
						SELECT line_records.line_record_uid, line_record_name
						FROM line_records, tht_base, phenotype_data
						WHERE value REGEXP '$value'
							AND line_records.line_record_uid = tht_base.line_record_uid
							AND tht_base.tht_base_uid = phenotype_data.tht_base_uid
							AND phenotype_data.phenotype_uid = '$phenotype'
						") or die(mysql_error());
			}
			else {
				$first = $_POST['first_value'] == "" ? getMaxMinPhenotype("min", $phenotype) : $_POST['first_value'];
				$last = $_POST['last_value'] == "" ? getMaxMinPhenotype("max", $phenotype) : $_POST['last_value'];

				$search = mysql_query("
						SELECT line_records.line_record_uid, line_record_name
						FROM line_records, tht_base, phenotype_data
						WHERE value BETWEEN $first AND $last
							AND line_records.line_record_uid = tht_base.line_record_uid
							AND tht_base.tht_base_uid = phenotype_data.tht_base_uid
							AND phenotype_data.phenotype_uid = '$phenotype'
						") or die(mysql_error());
			}

			if(mysql_num_rows($search) < 1) {
				echo "<p>Sorry, no records found<p>";
			}
			else {
				$found = array();
				while($line = mysql_fetch_assoc($search)) {
					array_push($found, "line_records@@line_record_name@@$line[line_record_uid]");
				}
			}
		}

/*****************************************************************************************************************/
		
		/* identify the lines with all the specified allele values */
		if(isset($_POST['haplotype'])) {

			/* Get the Marker Uids */
			$markers = array();
			foreach($_POST as $k=>$v) {
				if(strpos(strtolower($k), "marker") !== FALSE) {
					$tm = explode("_", $k);
					$markers[$tm[1]] = $v;
				}
			}
			$marker_instr=implode("," , array_keys($markers));
			// print $marker_instr."\n";
			if(count($markers) < 1) {
				error(1, "No Markers Selected");
			}
			
			$query_str="select A.line_record_name, A.line_record_uid, D.marker_uid, 
                            concat(allele_1,allele_2) as value 
			    from line_records as A, tht_base as B, genotyping_data as C, markers as D, alleles as E 
			    where A.line_record_uid=B.line_record_uid and B.tht_base_uid=C.tht_base_uid and 
			    C.marker_uid=D.marker_uid and C.genotyping_data_uid=E.genotyping_data_uid and 
						D.marker_uid in (".$marker_instr.")";
			$result=mysql_query($query_str);
			$lines = array();
			$line_uids=array();
			$line_names=array();
			while ($row=mysql_fetch_assoc($result)) {
				$linename=$row['line_record_name'];
				$lineuid=$row['line_record_uid'];
				$mkruid=$row['marker_uid'];
				$alleleval=$row['value'];
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
				$_SESSION['selected_lines']=$selLines;
				sort($selLines);
				print "<p><a href=\"pedigree/pedigree_markers.php\">Display the lines and markers</a>";
				print "<table class='tableclass1'><thead><tr><td>Line names</td></tr></thead><tbody>";
				foreach ($selLines as $luid) {
					print "<tr><td>";
					print "<a href=\"pedigree/show_pedigree.php?line=$luid\">".$line_names[$luid]."</a>";
					print "</td></tr>";
				}
				print "</tbody></table>";
			}
			else {
				echo "<p>Sorry, no records found<p>";
			}
		}

/*****************************************************************************************************************/

		//generic keyword search has been made
		if(isset($_POST['keywords']) ) {	//sidebar general search term has been submitted

			$allTables = array();
			$searchTree = array();
			$found = array();

			/* Populate the allTables array */
			if(isset($_REQUEST['table'])) {
				array_push($allTables, mysql_real_escape_string($_REQUEST['table']));			
			} else { 
				$tableQ = mysql_query("SHOW TABLES");
				while($row = mysql_fetch_row($tableQ)) {
					array_push($allTables, $row[0]);
				}
			}

			/* get unique keys of each table */
			foreach($allTables as $table) {
				$ukeys = get_ukey($table);
				$names = array();

				/* do not search through _uids */
                                /* do not add duplicates */
				for($i=0; $i<count($ukeys); $i++) {
			           if (strpos($ukeys[$i], "_uid")  === FALSE) {
                                     if (!in_array($ukeys[$i],$names)) {
		          	       array_push($names, $ukeys[$i] );
                                     }
				   }
				}

				/* add this table to the search tree if there are fields to search */
				if(count($names) > 0) {
					$searchTree[$table] = $names;
				}
			}

			/* Break Apart the Keywords and query */
			$words = explode(" ", $_POST['keywords']);
			for($i=0; $i<count($words); $i++) {
				if(trim($words[$i]) != "") {
					$found = array_merge($found, generalTermSearch($searchTree, $words[$i]));
				}
			}

			if(strlen($_POST['keywords']) < 1) 
				echo "<p>Please enter what you would like to search for before pressing \"search\".</p>";
			else if(count($found) < 1)
				echo "<p>We're sorry but nothing matches " . $_POST['keywords'] . ".</p>";

		}

		/* Handle the results */
// If there's only one hit, jump directly to it.
if (count($found) == 1) {
  $line = explode("@@", $found[0]);
  echo "Single result, redirecting.<br>";

  // Intercept experiments and route to display_phenotype.php or display_genotype.php.
  if ($line[0] == "experiments") {
      $trialcode = mysql_grab("select trial_code from experiments where experiment_uid = $line[2]");
      $expttype = mysql_grab("select experiment_type_uid from experiments where experiment_uid = $line[2]");
      if ($expttype == 1)
	echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."display_phenotype.php?trial_code=$trialcode\">";
      else
	echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."display_genotype.php?trial_code=$trialcode\">";
    }
  else {
    echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."view.php?table=".urlencode($line[0])."&uid=$line[2]\">";
  }
}
		elseif(count($found) > 0) {
			displayTermSearchResults($found);
		}
		else {
			print <<<_SEARCHFORM
			<form method="post" action="search.php">
				<div>
					<p><strong>General Search</strong>:</p>
					<input type="text" maxlength="32" name="keywords" ><br />
					<input type="submit" class="button" value="Search">
				</div>
			</form>
_SEARCHFORM;
		}

	?>
			</div>
		</div>
	</div>
</div>
</div>

<?php include("theme/footer.php");?>
