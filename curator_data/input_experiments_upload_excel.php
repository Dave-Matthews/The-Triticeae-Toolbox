<?php
// 3aug2012 DEM Manage multiple raw files.
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
connect();
loginTest();
//$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Experiments($_GET['function']);

class Experiments {

  private $delimiter = "\t";
  // Using the class's constructor to decide which action to perform
  public function __construct($function = null) {	
    switch($function) {
    default:
      $this->typeExperiments(); /* initial case*/
      break;
    }
  }

  private function typeExperiments() {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
?>
<style type="text/css">
table {background: none; border-collapse: collapse}
td {border: 0px solid #eee !important;}
h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
#confirm { display: none;
           top: 25%; left: 25%; width: 40%; 
           padding: 4px;
           border: 1px solid red; background-color: #efefef;
           position: fixed;
           opacity: 1.0;
	   overflow: auto;
           }
<!-- #dont { position: relative; -->
<!--         left: 50px; } -->

<!-- .btn { position: static; -->
<!--     } -->

}
</style>

<?php
    echo "<h2>Add Phenotype Experiment Results</h2>"; 
    $this->type_Experiment_Name();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  } // end of function typeExperiments()

// Entry states with their own blocks of code:
// $_POST['raw']
// $_POST['updatenew']	
// $_POST['updateold']
// $_GET['delete']
  private function type_Experiment_Name() {
    global $config;
?>

<p>Here you can load the results of a single field or greenhouse Trial.  The Annotation describing
the conditions of the Trial must already have been 
<a href="<?php echo $config['base_url'] ?>curator_data/input_annotations_upload_excel.php">loaded</a>.
 Please see the 
<a href="<?php echo $config['base_url'] ?>curator_data/tutorial/T3_Lesson2_Phenotype.html">tutorial</a> for 
step by step instructions.</font>
<p>

<div class="section">
  <h3>Add a Means File</h3>
  The Means file contains, for each trait, one value for each line in the Trial: the mean over all plots for that line.
<form action="curator_data/input_experiments_check_excel.php" method="post" enctype="multipart/form-data">
  <input type="hidden" id="means" name="means" value="-1" />
  <p><strong>Means file:</strong> <input id="file[]" type="file" name="file[]" size="50%" />
  <br><a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/PhenotypeSubmissionForm.xls">Example Means file</a>
  <br><input type="submit" value="Upload" /></p>
</form>
</div> <!-- end of div 'section' -->


<div id='rawsection' class="section">
  <h3>Add Raw Files</h3>
  One or more files of additional information maybe be attached to each Trial.  The files are archived for 
  downloading but their contents are not loaded into the database.  

<?php

if ($_GET['delete']) {
  $tc = $_GET['trial'];
  $top_path = $config['root_dir']."raw/phenotype/";
  $trial_path = $top_path . $tc . "/";
  $files = explode(',', trim($_GET['files'], ','));
  $exptuid = mysql_grab("select experiment_uid from experiments where trial_code = '$tc'");
  foreach ($files as $fl) {
    if (file_exists($top_path.$fl))
      unlink($top_path.$fl);
    elseif (file_exists($trial_path.$fl))
      unlink($trial_path.$fl);
    else {
      echo ("<p><b>Error:</b> Couldn't find file $fl.<p>");
      $failed = true;
    }
    if (!$failed) {
      $sql = "delete from rawfiles where experiment_uid = $exptuid and name = '$fl'";
      mysql_query($sql) or die (mysql_error()."<br>Query was:<br>".$sql);
    }
  }
  if (!$failed) {
    echo "<p><font color=red><b>Files deleted:";
    foreach ($files as $fl) 
      echo "<br>&nbsp;&nbsp;&nbsp;$fl";
    echo "</b></font><p>";
  }
}

  if ($_POST['raw'] OR $_POST['updatenew']) {
    $trialcode = $_POST['trialcode'];
    $newraw = $_FILES['file']['name'];
    $tempraw = $_FILES['file']['tmp_name'];
  }
  else 
    $trialcode = $_GET['trialcode'];
  $exptuid = $_GET['exptuid'];

  if ($_POST['updateold']) {
    // Edit old raw files: delete them or change description.
    $trialcode = $_POST['trialcode'];
    $exptuid = mysql_grab("select experiment_uid from experiments where trial_code = '$trialcode'");
    $oldfile = $_POST['oldfile'];
    $desc = $_POST['desc'];
    $todelete = $_POST['delete'];
    if ($todelete) {
      deleteraw();
    }
    $sql = "select name, description, directory from rawfiles where experiment_uid=$exptuid";
    $res = mysql_query($sql) or die (mysql_error()."<br>Query was:<br>".$sql);
    $j = 0;
    while ($info = mysql_fetch_assoc($res)) {
      if ($oldfile[$j] != $info['name'])
	die ("<p><b>ERROR</b>: filename mismatch, '$oldfile[$j]' vs. '".$info['name']."'.");
      if ($todelete AND in_array($j, $todelete)) {
      	if (!unlink($config['root_dir']."raw/phenotype/".$trialcode."/".$info['name']))
      	    echo "<p><b>ERROR</b>: Couldn't delete file ".$config['root_dir']."raw/phenotype/".$trialcode."/".$info['name'];
	else {
	  $sql = "delete from rawfiles where experiment_uid = $exptuid and name = '".$info['name']."'";
	  $r = mysql_query($sql) or die (mysql_error()."<br>Query was:<br>".$sql);
	  echo "<p><b><font color=red>File '".$info['name']."' deleted.</font></b>";
	}
      }
      /* echo "<br>old desc: ".$info['description']."; new desc: $desc[$j]"; */
      if ($info['description'] != $desc[$j]) {
      	$sql = "update rawfiles set description = '$desc[$j]'
                where experiment_uid = $exptuid and name = '".$info['name']."'";
      	$r = mysql_query($sql) or die (mysql_error()."<br>Query was:<br>".$sql);
      }
      $j++;
    }
  }

  if (!$trialcode AND !$exptuid) {
    // No Trial selected yet.  Pick one.
?>
    <form action = "curator_data/input_experiments_upload_excel.php" method="get">
      Trial 
      <input type=text name=trialcode>
      <input type=submit value="Go">
    </form>
    <p>
      <select onchange="window.open('<?php echo $config['base_url']; ?>curator_data/input_experiments_upload_excel.php?exptuid='+this.options[this.selectedIndex].value,'_top')">
	<option value=''>or choose from below...</option>
<?php
      $sql = "select trial_code, experiment_uid as uid 
	  from experiments where experiment_type_uid = 1
	  order by trial_code";
    $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
    while($row = mysql_fetch_assoc($r)) {
      $tc = $row['trial_code'];
      $uid = $row['uid'];
      echo "<option value='$uid'>$tc</option>\n";
    }
    echo "</select></div>";

  } // end of if(!$trialcode AND !$exptuid)
  else {
    // Trial selected, either by name or id.  Get both.
    if ($trialcode) {
      $exptuid = mysql_grab("select experiment_uid from experiments where trial_code = '$trialcode'");
      if (!$exptuid) {
	echo "<p>Trial <b>\"$trialcode\"</b> not found.";
	exit("<p><input type='Button' value='Back' onClick='history.go(-1)'>");
      }
    }
    elseif ($exptuid)
      $trialcode = mysql_grab("select trial_code from experiments where experiment_uid = '$exptuid'");
    // Show Trial name.
    echo "<p><form>Trial: <b><a href='".$config['base_url']."display_phenotype.php?trial_code=$trialcode'>$trialcode</a></b>";
    echo " &nbsp;<input type=submit value='Change'></form>";

    if ($_POST['updatenew']) {
      // Some new raw files have been selected, shown, possibly annotated, and the Save button clicked.
      // Move from /tmp to raw/ subdirectory, register in table rawfiles, and display status.
      $tc = $_POST['trialcode'];
      $exptid = mysql_grab("select experiment_uid from experiments where trial_code = '$tc'");
      $nf = $_POST['newfiles'];
      $nft = $_POST['newfilestemp'];
      $de = $_POST['desc'];
      $ac = $_POST['action'];
      $user = loadUser($_SESSION['username']);
      $userid = $user['users_uid'];
      $username = $user['name'];
      $target_path = $config['root_dir']."raw/phenotype/".$tc."/";
      if (!file_exists($target_path))
      	mkdir($target_path);
      for ($i=0; $i < count($nf); $i++) {
	// TODO: add to table input_file_log. Add experiment_set name to path.
      	if (!rename("/tmp/".$nf[$i], $target_path.$nf[$i])) 
      	  die("<p><b>ERROR</b>: File <b>$nf[$i]</b> could not be saved in archive directory '$target_path'.");
	if ($ac[$i] == "Add new") {
	  $logsql = "INSERT INTO input_file_log (file_name, users_name) 
                     VALUES('raw/phenotype/$tc/$nf[$i]', '$username')";
	  $logres = mysql_query($logsql) or die (mysql_error()."<br>Query was:<br>".$sql);
	  $sql = "insert into rawfiles (experiment_uid, users_uid, name, directory, description)
                  values ($exptid, $userid, '$nf[$i]', '$tc', '$de[$i]')";
	}
	else {
	  $sql = "update rawfiles set users_uid = $userid, description = '$de[$i]'
          where experiment_uid=$exptid and name='$nf[$i]'";
	}
	$res = mysql_query($sql) or die (mysql_error()."<br>Query was:<br>".$sql);
      }  // end for(all $nf)
      echo "<p><b><font color=red>Files saved.</font></b>";

    }

    // Handle selection of new raw files to add.
    if ($newraw AND empty($newraw[0])) {
      echo '<script type=text/javascript> alert("No file chosen."); </script>';
    }
    if (!empty($newraw[0])) {
      // Browser has returned a set of files in /tmp.  Annotate and accept.
      // First, immediately store in desired directory.
      $newraw = $_FILES['file']['name'];
      $tempraw = $_FILES['file']['tmp_name'];
      $tc = $_POST['trialcode'];
      for ($i=0; $i < count($newraw); $i++) {
	if (!move_uploaded_file($tempraw[$i], "/tmp/".$newraw[$i])) 
	  die("<p><b>ERROR</b>: File <b>$newraw[$i]</b> could not be renamed in /tmp/.");
      }
      // Display new files for user decision.
      $sql = "select name from rawfiles where experiment_uid = $exptuid";
      $res = mysql_query($sql) or die(mysql_error()."<br>Query was:<br>".$sql);
      while ($info = mysql_fetch_assoc($res)) 
	$oldfile[] = $info['name'];
      echo "<p><table>
      <tr><th>Action</th><th>Uploaded raw file</th><th>Description</th></tr>";
      echo "<form action = ".$_SERVER['PHP_SELF']." method=post>";
      for ($i=0; $i < count($newraw); $i++) {
	if (in_array($newraw[$i],$oldfile)) {
	  $action[$i] = "Replace";
	  $descrip[$i] = mysql_grab("select description from rawfiles where name='$newraw[$i]' and experiment_uid=$exptuid");
	}
	else $action[$i] = "Add new";
	echo "<tr><td><b><font color=red>$action[$i]</font></b></td><td>$newraw[$i]</td>";
	echo "<input type=hidden name=newfiles[$i] value='".$newraw[$i]."'>";
	echo "<input type=hidden name=newfilestemp[$i] value='".$tempraw[$i]."'>";
	echo "<input type=hidden name=action[$i] value='".$action[$i]."'>";
	echo "<td><input type=text name=desc[] size=60 value='".$descrip[$i]."'></td></tr>";
      }
?>
 </table>
  <input type=hidden name=trialcode value='<?php echo $trialcode ?>'>
  <input type=hidden name=updatenew value=1>
  <input type=submit value=Save>
  </form>
  <input type="Button" value="Cancel" onClick="history.go(-1); return;">
  <p>
<?php
      } // end if($newraw)

    else {
      // No raw files selected yet.  Offer to get some.
      // Form to select the files with browser's interface:
?>
   <form action="curator_data/input_experiments_upload_excel.php" method="post" enctype="multipart/form-data">
     <input type=hidden name="raw" value=1>
     <input type=hidden name=trialcode value='<?php echo $trialcode ?>'>
     <p><strong>New raw files:</strong> 
       <input id="file[]" type="file" name="file[]" size="50%" multiple> 
       <br>To select multiple files, use the Shift or Ctrl / &#8984; key.<br>
       <input type=submit value=Upload></p>
   </form>

<?php
   } /* end of else, i.e. !$newraw */

    // Show previously saved files.  Offer to delete or to edit description.
    $sql = "select name, description from rawfiles where experiment_uid = $exptuid";
    $res = mysql_query($sql) or die(mysql_error()."<br>Query was:<br>".$sql);
    if (mysql_num_rows($res) == 0)
      echo "<p>No raw files saved yet.<br>";
    else {
      echo "<table><tr><th>Delete</th><th>Current raw files</th><th>Description</th></tr>";
      echo "<form method='post' name='oldfiles'>";
      echo "<input type=hidden name=trialcode value=$trialcode>";
      $j = 0;
      while ($info = mysql_fetch_assoc($res)) {
	$oldfile[$j] = $info['name'];
	$de[$j] = $info['description'];
	// Array delete[] will be a list of the $j numbers of whatever boxes are checked.
	echo "<tr><td align='center'><input type=checkbox name=delete value='$oldfile[$j]'></td>";
	echo "<td><input type=hidden name=oldfile[] value='$oldfile[$j]'>$oldfile[$j]</td>";
	echo "<td><input type=text name=desc[] value='$de[$j]' size=60></td></tr>";
	$j++;
      }
?>
<tr>
  <td align='center'>

<script type=text/javascript>

	function popup() {
	    var i, fname, found, pp;
	    pp = document.getElementById('confirm');
	    pptxt = document.getElementById('pptxt');
	    document.getElementById('rawsection').style.opacity = '.2';
	    pp.style.display = 'block';
	    pptxt.innerHTML =  'Really delete these files?:';
	    for (i = 0; i < document.oldfiles.delete.length; i++) {
		if (document.oldfiles.delete[i].checked) {
		    fname = document.oldfiles.delete[i].value;
		    pptxt.innerHTML +=  '<br>&nbsp;&nbsp;' + fname;
		    found = true;
		}
	    }
	    if (!found) {
		pptxt.innerHTML +=  '<br>No files checked for deletion.';
		document.getElementById('do').style.display='none';
	    }
	    else 
		document.getElementById('do').style.display='inline';
	}


	function deleteold() {
	    var getstring, i, fname;
	    getstring = "?delete=1&trial=<?php echo $trialcode ?>&files=";
	    for (i = 0; i < document.oldfiles.delete.length; i++) {
		if (document.oldfiles.delete[i].checked) {
		    fname = document.oldfiles.delete[i].value;
		    getstring += fname + ",";
		}	    
	    }
	    window.location = "<?php echo $_SERVER[PHP_SELF] ?>" + getstring;
	}

</script>

    <button type=button onclick = "popup();">Delete</button></td>
  <td></td><td>
    <input type=submit value=Update></td>
</tr>
</table>
<input type=hidden name=updateold value=1>
</form>

</div> <!-- End of div 'rawsection' -->

<!-- Popup box: -->

<div name="popup" id="confirm">
  <div id="pptxt"></div>
    <button id="do" style="position:static;" onclick = "deleteold();
		       document.getElementById('confirm').style.display='none';
		       document.getElementById('pptxt').innerHTML ='';
		       document.getElementById('rawsection').style.opacity='1';
		       ">Confirm</button>
    <button style="position:static;" onclick = "
		       document.getElementById('confirm').style.display='none';
		       document.getElementById('pptxt').innerHTML ='';
		       document.getElementById('rawsection').style.opacity='1';
		       ">Cancel</button>
</div> <!-- End of div 'confirm' -->



<?php
    } // end editing form for previous raw files

  } /* end of else*/
  } /* end of type_Experiment_Name function*/
} /* end of class */
?>
