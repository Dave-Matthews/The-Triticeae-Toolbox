<?php 
/** Genotype Annotation importer
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/curator_data/genotype_annotatins_upload.php

 * 03/01/2011  JLee  Create for genotype annotation importer
 * 23aug2013 dem Add Platform management.
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
connect();

// AJAX function call.  
$functioncall = $_GET['func'];
if (!empty($functioncall)) {
    unset($_GET['func']); // Remove function name and pass the rest as arg.
    call_user_func($functioncall, $_GET);
    exit; // If running this, don't do anything else in the script.
}

loginTest();
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

require $config['root_dir'] . 'theme/admin_header.php';

// If we are re-entering the script with a platform-management submission, handle it.
// Add a new platform.
$platform = $_REQUEST['platform'];
if (!empty($platform)) {
    $desc = $_REQUEST['description'];
    $sql="select platform_uid from platform where platform_name = '$platform'";
    $r = mysql_query($sql) or die(mysql_error());
    if (mysql_num_rows($r) > 0)
        $feedback = "<p><font color=red>Platform \"<b>$platform</b>\" already exists.</font>";
    else {
        $sql = "insert into platform (platform_name, description) values ('$platform', '$desc')";
        $r = mysql_query($sql) or die(mysql_error());
        $feedback = "Platform \"<b>$platform</b>\" added.<p>";
    }
}
// Delete a platform.
if (isset($_REQUEST['delete'])) {
    $remove = $_REQUEST['platformlist'];
    if (!empty($remove)) {
        $plname = mysql_grab("select platform_name from platform where platform_uid = $remove");
        $sql = "delete from platform where platform_uid = $remove";
        $r = mysql_query($sql) or die(mysql_error());
        $feedback = "Platform \"<b>$plname</b>\" deleted.<p>";
    }
}
// Edit a platform description.
if (isset($_REQUEST['update'])) {
    $platformid = $_REQUEST['platformlist'];
    if (!empty($platformid)) {
        $sql = "update platform set description = '".$_REQUEST['editdesc']."' where platform_uid = $platformid";
        $r = mysql_query($sql) or die(mysql_error());
        $feedback =  "Description updated.<p>";
    }
}
// End of handling platform management form submissions.

?>

<style type="text/css">
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<div class=section>
  <h2>Upload Genotype Experiment Annotation</h2>
  <form action="curator_data/genotype_annotations_check.php" method="post" enctype="multipart/form-data">
    <p><b>Genotype Annotation File:</b>
      <input id="file[]" type="file" name="file[]" size="80%" /><br>
      <a href="curator_data/examples/Geno_Annotation_Sample.txt">Example Genotype Annotation File</a>
    <p> Do you want this data to be Public? 
      <input type='radio' name='flag' value="1" checked="checked" /> Yes &nbsp;&nbsp; 
      <input type='radio' name='flag' value="0"/> No<br>
      <input type="submit" value="Upload Annotation File" />
  </form>
</div>


<div class=section>
  <h2>Genotyping Platforms</h2>
  <!-- If we have made a change, say what we did. -->
  <?php echo $feedback ?>

  <h3>Add new</h3>
  <form action = <?php echo $_SERVER['PHP_SELF'] ?> method="post">
    <b>Name</b> <input type=text name=platform> 
    <input type=submit value="Add"><br>
  </form>

  <h3>Edit</h3>

<?php
// Edit platform descriptions, or delete.
$r = mysql_query("select * from platform") or die(mysql_error());
if (mysql_num_rows($r) > 0) {
    // If user has sent a command and we are refreshing the page, show confirmation.
    print "<form method=post>";
    print "<table><tr><th>Name<th>Description";
    print "<tr><td><select size=7 id=platformlist name=platformlist style='width: 16em' onchange='pickplatform(this)'>";
    while ($row = mysql_fetch_assoc($r)) {
        $platid = $row['platform_uid'];
        $platname = $row['platform_name'];
        print "<option value=$platid>$platname</option>\n";
    }
    print "</select>";
    print "<td><textarea name=editdesc id=editdesc cols=30 rows=7></textarea>";
    print "</table>";
    print "<input type=submit id='Updbtn' name=update value='Update' disabled>";
    print "<br><input type=submit id='Delbtn' name=delete value='Delete platform' disabled>";
    print "</form>";
}
print "</div>";

$footer_div = 1;
require $config['root_dir'].'theme/footer.php';
?>

<script type=text/javascript>
    function pickplatform(picked) {
	// A Platform name was clicked.
	// Activate the action buttons.
	getElmt("Updbtn").disabled = false;
	getElmt("Delbtn").disabled = false;
	// Output to textarea "Description".
        var resp = document.getElementById("editdesc");
        var req = getXMLHttpRequest();
        if(!req) 
            resp.innerHTML = "This function requires Ajax. Please report the problem.";
        var qs = "?func=dispDesc&platformid="+picked.value; 
        req.onreadystatechange = function() {
            if(req.readyState == 4) {
                resp.innerHTML = req.responseText;
                resp.style.display="block";
            }
        }
        req.open("GET", "<?php echo $_SERVER['PHP_SELF'] ?>"+qs, true);
        req.send(null);
    }
</script>

<?php
/**
 * An existing Platform has been clicked.  Show its description.
 *
 * @param array $args list of arguements
 *
 * @return NULL
 */
function dispDesc($args)
{
    $platformid = $args['platformid'];
    $desc = mysql_grab("select description from platform where platform_uid = $platformid");
    if (empty($desc))
        $desc = "(none)";
    echo $desc;
}
?>
