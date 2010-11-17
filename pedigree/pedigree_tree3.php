<?php
require 'config.php';
/*
 * Logged in page initialization
 */

include($config['root_dir']."includes/bootstrap.inc");
connect();
loginTest();
/* ******************************* */
$row = loadUser($_SESSION['username']);
include($config['root_dir']."theme/cheader.php");

?>

<p>Try, for example, tmp9, morex, tmp4, and cree.</p>

<?php
if(isset($_REQUEST['line'])) {
	echo "<div class=\"box\" style=\"text-align: left;\"><pre>\n";
	$nvisited=array();
	$pediarr=array($_REQUEST['line']=>getPedigrees_r($_REQUEST['line'],$nvisited));
	$treestr=tree2str($pediarr, $_REQUEST['line']);
	$treestr2=tree2str_wi($pediarr, $_REQUEST['line']);  // get the internal names
	print "PediTree Style Text Annotation:\n$treestr\n\nPedigree (internal nodes not displayed)\n\n";
	print "<img src=\"images/pedi_img.php?pstr=".$treestr."\" alt='Pedigree Tree'>";
	print "\n".$treestr2."\n";
	include("../images/pedi_img_wi_imgmap.inc");
	print "<img style=\"border:none\" src=\"images/pedi_img_wi.php?pstr=".$treestr2."\" usemap='#peditreewi' alt='Pedigree Tree with Internal Nodes'>";

	echo "\n</pre></div>";
	print "<map name=\"peditreewi\">";
	$imgmap=get_imgmap($treestr2);
	foreach ($imgmap as $marr) {
		print "<area ";
		foreach ($marr as $mk=>$mv) {
			print "$mk=\"$mv\"";
		}
		print ">\n";
	}
	print "</map>";
}
?>

<form action="login/pedigree_tree3.php" method="post">
<p><strong>Line Name</strong><br />
<input type="text" name="line" value="<?php echo $_REQUEST['line']; ?>" /></p>

<p><input type="submit" value="Get Tree" /></p>
</form>

<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

<?php include($config['root_dir']."theme/footer.php");?>
