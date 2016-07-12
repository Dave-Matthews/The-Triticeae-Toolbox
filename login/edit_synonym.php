<?php
/**
 * login/edit_synonym.php, dem 13feb2012
 * dem 26aug13 Add merging of lines.
 * dem  7oct14 Make sure the "new" synonym proposed doesn't already exist.
 **/

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();
ob_start();
require $config['root_dir'] . 'theme/admin_header.php';
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
?>
<!-- Start of Synonyms section -->
<div class="boxContent">
<h2>Edit Line Synonyms</h2>
  <!-- Select which line to edit. -->
  <form method="get">
    <p><strong>Line Name</strong><br>
      <input type="text" name="line" value="<?php echo $_GET['line']; ?>">
      <input type="submit" value="Search" /></p>
  </form>
<?php
// Has a Synonym update been submitted?
if (!is_null($_GET['newsyn'])) {
  $input = $_GET;
  foreach($input as $k=>$v)
    $input[$k] = addslashes($v);
  array_pop($input); // Remove line name.
  $line_uid = array_pop($input);
  $newsyn = array_pop($input);
  $changed = array("unchanged", "updated");
  $flag = 0;
  if (!empty($newsyn)) {
    // Does the name already exist as either a synonym or a line name?
    $lsid = mysql_grab("select line_record_uid from line_records where line_record_name = '$newsyn'");
    if ($lsid)
      echo "'$newsyn' already exists as a Line Name.<br>";
    else {
      // If not a primary name, check for synonym.
      $lname = mysql_grab("select line_record_name from line_synonyms ls, line_records lr where line_synonym_name = '$newsyn' and ls.line_record_uid = lr.line_record_uid");
      if ($lname)
	echo "'$newsyn' is already a synonym for a different line, $lname.<br>";
      else {
	// No problems. Add a new value.
	$sql = "insert into line_synonyms 
           (line_record_uid, line_synonym_name, updated_on, created_on) 
           values ($line_uid, '$newsyn', now(), now())";
	$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli)."<br>Query: ".$sql);
	$flag = 1;
      }
    }
  }
  foreach($input as $k=>$v) {
    if (empty($v)) {
      // Delete the record.
      $sql = "delete from line_synonyms where line_synonyms_uid = $k";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli)."<br>Query: ".$sql);
      $flag = 1;
    }
    else {
      $oldval = mysql_grab("select line_synonym_name from line_synonyms where line_synonyms_uid = $k");
      if ($oldval != $v) {
	// Edit the value.
	$res = mysqli_query($mysqli, "update line_synonyms set line_synonym_name = '$v', updated_on = now()
             where line_synonyms_uid = $k") or die(mysqli_error($mysqli)."<br>Query: ".$sql);
	$flag = 1;
      }
    }
  }
  echo "Database <font color=green><b>$changed[$flag]</b></font>.<p>";
}

// Has a GRIN Accession update been submitted?
if(!is_null($_GET['newgrin'])) {
  $input = $_GET;
  foreach($input as $k=>$v)
    $input[$k] = addslashes($v);
  array_pop($input); // Remove line name.
  $line_uid = array_pop($input);
  $newgrin = array_pop($input);
  $changed = array("unchanged", "updated");
  $flag = 0;
  if (!empty($newgrin)) {
    // Add a new value.
    $sql = "insert into barley_pedigree_catalog_ref
           (barley_pedigree_catalog_uid, line_record_uid, barley_ref_number, updated_on, created_on) 
           values (2, $line_uid, '$newgrin', now(), now())";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli)."<br>Query: ".$sql);
    $flag = 1;
  }
  foreach($input as $k=>$v) {
    if (empty($v)) {
      // Delete the record.
      $sql = "delete from barley_pedigree_catalog_ref where barley_pedigree_catalog_ref_uid = $k";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli)."<br>Query: ".$sql);
      $flag = 1;
    }
    else {
      $oldval = mysql_grab("select barley_ref_number from barley_pedigree_catalog_ref 
                            where barley_pedigree_catalog_ref_uid = $k");
      if ($oldval != $v) {
	// Edit the value.
	$res = mysqli_query($mysqli, "update barley_pedigree_catalog_ref set barley_ref_number = '$v', updated_on = now()
             where barley_pedigree_catalog_ref_uid = $k") or die(mysqli_error($mysqli)."<br>Query: ".$sql);
	$flag = 1;
      }
    }
  }
  echo "Database <font color=green><b>$changed[$flag]</b></font>.<p>";
}

// Have we searched?
if(isset($_GET['line'])) {
  $line = $_GET['line'];
  $line_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$line'");
  if (empty($line_uid))
    echo "Line name not found.<p>";
  else {
    echo "<table><tr><td style='vertical-align:top'>";
    echo "<form>";
    echo "<b>Synonyms</b><br>";
    $sql = "select line_synonyms_uid, line_synonym_name
            from line_synonyms where line_record_uid = $line_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while($row = mysqli_fetch_row($res)) 
      echo "<input type=text name='$row[0]' value='$row[1]'><br>";
    echo "<input type=text name='newsyn'><br>";
    echo "<input type=hidden name='line_uid' value='$line_uid'>";
    echo "<input type=hidden name='line' value=$line>";
    echo "<input type=submit value='Accept'>";
    echo "</form></td>";

    echo "<td style='vertical-align:top'><form>";
    echo "<b>GRIN Accessions</b><br>";
    $sql = "select barley_pedigree_catalog_ref_uid, barley_ref_number
            from barley_pedigree_catalog_ref where line_record_uid = $line_uid
            and barley_pedigree_catalog_uid = 2";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while($row = mysqli_fetch_row($res)) 
      echo "<input type=text name='$row[0]' value='$row[1]'><br>";
    echo "<input type=text name='newgrin'><br>";
    echo "<input type=hidden name='line_uid' value='$line_uid'>";
    echo "<input type=hidden name='line' value=$line>";
    echo "<input type=submit value='Accept'>";
    echo "</form></td></tr></table>";
  }
}
echo "</div>";
// end of Synonyms section

// start of Merge section
?>
<div class="boxContent">
<h2>Merge Two Lines</h2>
  <!-- Select which lines to merge. -->
  <form method="get">
    <p><strong>Line to keep</strong><br>
      <input type="text" name="keepline" value="<?php echo $_GET['keepline']; ?>">
    <p><strong>Line to merge into it</strong><br>
      <input type="text" name="oldline" value="<?php echo $_GET['oldline']; ?>">
      <input type="submit" value="Search" /></p>
  </form>
<?php
  // Have we Searched for the two lines?  Show what data they have.
  if (isset($_GET[keepline]) AND isset($_GET[oldline])) {
    $keepline = $_GET[keepline];
    $oldline = $_GET[oldline];
    $kline_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$keepline'");
    $oline_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$oldline'");
    $notfound = "";
    if (empty($kline_uid))
      $notfound = "Line name '$keepline' not found.<br>";
    if (empty($oline_uid))
      $notfound = "Line name '$oldline' not found.<br>";
    echo $notfound;
    if (empty($notfound) ) {
      if (!$_GET['confirm']) {
      // Show details about these lines.
      // Get the properly capitalized names.
      $keepline = mysql_grab("select line_record_name from line_records where line_record_uid = $kline_uid");
      $oldline = mysql_grab("select line_record_name from line_records where line_record_uid = $oline_uid");
      $ids = array($kline_uid, $oline_uid);
      echo "<table><tr>";
      echo "<th><th><a href='".$config['base_url']."view.php?table=line_records&name=$keepline'>$keepline";
      echo "<th><a href='".$config['base_url']."view.php?table=line_records&name=$oldline'>$oldline";
      // Some passport info:
      // Direct parents
      echo "<tr><td><strong>Parents</strong>";
      foreach ($ids as $lnid) {
	echo "<td>";
	$res = mysqli_query($mysqli, "select line_record_name
			    from line_records
			    where line_record_uid in (
			      select parent_id
			      from pedigree_relations
			      where line_record_uid = $lnid)");
	$r = array();
	while ($row = mysqli_fetch_row($res)) 
	  $r[] = $row[0];
	$parents[$lnid] = implode(", ", $r);
	echo $parents[$lnid];
      }
      // A validation test:
      if ($parents[$kline_uid] != $parents[$oline_uid] 
	  and (!empty($parents[$kline_uid])) 
	  and (!empty($parents[$oline_uid])))
	$refuse .= "<br>their parents are different. <a href=$config[base_url]login/edit_pedigree.php?line=$oldline>Edit</a>";
      // Pedigree string
      echo "<tr><td><strong>Pedigree</strong>";
      foreach ($ids as $lnid) {
	$ped = mysql_grab("select pedigree_string from line_records where line_record_uid=$lnid");
	echo "<td>$ped";
      }
      // Description
      echo "<tr><td><strong>Description</strong>";
      foreach ($ids as $lnid) {
	$desc = mysql_grab("select description from line_records where line_record_uid=$lnid");
	echo "<td>$desc";
      }
      // Panels
      $sql = "select linepanels_uid, name, line_ids from linepanels";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      while ($row = mysqli_fetch_row($res)) {
	$panelid = $row[0];
	$members[$panelid] = explode(",", $row[2]);
	foreach ($ids as $lnid) {
	  $inpanel[$panelid][$lnid] = array_search($lnid, $members[$panelid]);
	  if ($inpanel[$panelid][$lnid]) {
	    $panels[$lnid][] = $row[0];
	    $panelnames[$lnid][] = $row[1];
	  }
	}
      }
      echo "<tr><td><strong>Panels</strong>";
      foreach ($ids as $lnid) {
        if (is_array($panelnames[$lnid])) {
	    $panelist = implode(", ", $panelnames[$lnid]);
            echo "<td>$panelist";
        } else {
            echo "<td>$panellist[$lnid]";
        }
      }
      // Phenotype data
      echo "<tr><td><strong>Phenotype Trials</strong>";
      foreach ($ids as $lnid) {
	$sql = "select trial_code
	 from tht_base tb, experiments e, experiment_types et
	 where tb.experiment_uid = e.experiment_uid
	 and e.experiment_type_uid = et.experiment_type_uid
	 and experiment_type_name = 'phenotype'
	 and line_record_uid = $lnid";
	$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
	while ($tr = mysqli_fetch_row($res))
	  $trials[$lnid][] = $tr[0];
	$triallist = implode(", ", $trials[$lnid]);
	echo "<td>$triallist";
      }
      // Validation: Can't both be in the same trial.
      foreach ($trials[$oline_uid] as $tr)
	if (in_array($tr, $trials[$kline_uid]))
	  $refuse .= "<br>they were both tested in trial <b>$tr</b>.";
      // Genotype data
      echo "<tr><td><strong>Genotype Experiments</strong>";
      foreach ($ids as $lnid) {
	$sql = "select trial_code
	 from tht_base tb, experiments e, experiment_types et
	 where tb.experiment_uid = e.experiment_uid
	 and e.experiment_type_uid = et.experiment_type_uid
	 and experiment_type_name = 'genotype'
	 and line_record_uid = $lnid";
	$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
	while ($ge = mysqli_fetch_row($res))
	  $gexpts[$lnid][] = $ge[0];
	$gexptlist = implode(", ", $gexpts[$lnid]);
	echo "<td>$gexptlist";
      }
      // Validation: Can't both be in the same genotyping experiment.
      foreach ($gexpts[$oline_uid] as $ge)
	if (in_array($ge, $gexpts[$kline_uid]))
	  $refuse .= "<br>they were both tested in experiment <b>$ge</b>.";
      echo "</table>";
      // Show allele differences!!
      $keepalllist = mysql_grab("select alleles from allele_byline where line_record_uid = $kline_uid");
      $oldalllist  = mysql_grab("select alleles from allele_byline where line_record_uid = $oline_uid");
      $keepalleles = explode(",", $keepalllist);
      $oldalleles  = explode(",", $oldalllist);
      $markerct = 0; $allelediff = 0;
      for ($i = 0; $i < count($keepalleles); $i++) {
	if (!empty($keepalleles[$i]) and !empty($oldalleles[$i])) {
	  $markerct++;
	  if ($keepalleles[$i] != $oldalleles[$i])
	    $allelediff++;
	}
      }
      if ($markerct > 0)
	$percent = round( (($allelediff / $markerct) * 100), 2);
      else 
	$percent = 0;
      echo "<p>$keepline and $oldline alleles differ for <b>$allelediff</b> of <b>$markerct</b> markers, <b>$percent</b>%.<p>";
    }
      // Validate.
      if (!empty($refuse))
	echo "<p><b>$keepline and $oldline cannot be merged because:</b> $refuse";
      else {
	// Ask for confirmation.
	echo "<form method=GET>";
	echo "<p>Move the phenotype and genotype data <b>to</b> $keepline <b>from</b> $oldline, and 
           <font color=red><b>delete $oldline</b></font>?<br>";
	echo "<input type=hidden name=keepline value=$keepline>";
	echo "<input type=hidden name=oldline value=$oldline>";
	echo "<input type=submit name=confirm value=Yes> <input type=submit name=confirm value=No> ";
	echo "<br>There is no Undo.</form>";
      
	// Confirmed?  I.e. "Yes" button clicked?
	if ($_GET[confirm] == "Yes") {
	  // No action, since old line can't be accessed.  Will go away when alleles next added:
	  // allele_cache, allele_byline, allele_byline_clust, allele_conflicts
	  // ?: What about allele_bymarker, allele_bymarker_idx?
	  // Remove oldline from all panels it's in.
	  foreach ($panels[$oline_uid] as $panelid) {
	    unset($members[$panelid][$inpanel[$panelid][$oline_uid]]);
	    $memberlist = implode(',', $members[$panelid]);
	    $sql = "update linepanels set line_ids = '$memberlist' where linepanels_uid = $panelid";
	    $res = mysqli_query($mysqli, $sql) or die("<p><b>Error: </b>".mysqli_error($mysqli)."<br>Command was:<br>$sql");
	  }
	  // Move phenotype and genotype data from oldline to keepline by replacing tht_base.line_record_uid and fieldbook.line_uid.
	  // Delete from pedigree_relations both as line_record_uid and parent_id.
	  $commands = array("update tht_base set line_record_uid = $kline_uid where line_record_uid = $oline_uid",
			    "update fieldbook set line_uid = $kline_uid where line_uid = $oline_uid",
			    "delete from pedigree_relations where line_record_uid = $oline_uid",
			    "delete from pedigree_relations where parent_id = $oline_uid",
			    "delete from line_synonyms where line_record_uid = $oline_uid",
			    "delete from barley_pedigree_catalog_ref where line_record_uid = $oline_uid",
			    "delete from line_properties where line_record_uid = $oline_uid",
                            "delete from allele_bymarker_idx where line_record_uid = $oline_uid",
			    "delete from line_records where line_record_uid = $oline_uid");
	  foreach ($commands as $sql) {
	    $res = mysqli_query($mysqli, $sql) or die("<p><b>Error: </b>".mysqli_error($mysqli)."<br>Command was:<br>$sql");
	  }
	  echo "<p>Line <b>$oldline</b> deleted. Phenotype and genotype data merged into <b>$keepline</b>.";
          echo "<br>Recreating allele_bymarker and allele_bymarker_idx table.\n";
          $cmd = "/usr/bin/php ../cron/create-allele-bymarker.php > /dev/null &";
          exec($cmd, $output);
          foreach ($output as $line) {
              echo "$line<br>\n";
          }
	} // end of confirm = Yes
	if ($_GET[confirm] == "No")
	  echo "<p><b>Merge canceled!</b>";
      } // end of else ($refuse is empty.)
    } // end if (empty($notfound))
  }
echo "</div>";
// end of Merge section

echo "</div>";
include($config['root_dir'] . '/theme/footer.php');?>
