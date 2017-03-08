<?php
require 'config.php';
/*
 * Logged in page initialization
 */

require $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();
session_start();

require $config['root_dir'] . 'theme/admin_header.php';

?>

<div id="primaryContentContainer">
<div id="primaryContent">
<h2>Show Pedigree Tree
<?php
if (isset($_GET['line_name'])) {
    echo " : " . htmlspecialchars($_GET['line_name']);
    ?>
    </h2>
    <?php
}


    ?>
    <?php echo (htmlspecialchars($_REQUEST['line']) != "") ? " : " . htmlspecialchars($_REQUEST['line']) : "" ; ?></h2>
    <div class="section">

<?php
if (isset($_GET['line_name'])) {
    echo "<div style=\"text-align: left;border:none\"><pre>\n";

    // Search the pedigree relations to get the pedigree tree
    $nvisited=array();
    $pediarr=array($_GET['line_name']=>getPedigrees_r($_GET['line_name'], $nvisited));
    $linename=$_GET['line_name'];
    // Generate the pedigree annotation with the names of the internal nodes
    // This is the working data structure representation in THT for pedigree
    $treestr2=tree2str_wi($pediarr, $_GET['line_name']);
    // print "\n".$treestr2."\n";

    // Draw the pedigree tree
    $pdarr=generate_pedigree_matrix($treestr2);
    unset($_SESSION['draw_pedigree_matrix']); // necessary, ow the figure is not refreshed
    unset($_SESSION['draw_snps']);
    $_SESSION['draw_pedigree_matrix']=$pdarr; // data passed through session variables
    $_SESSION['draw_snps']=strtoupper("atgcnncttttcccc");
    // print "<a href=\"images/pedi_image.php\">View Image</a>";  // used for testing
    $pagenum=0;
    if (isset($_GET['pagenum'])) {
        $pagenum=$_GET['pagenum'];
    }
    $imgrand=rand();
    print "<br><br><img style=\"border:none\" src=\"".$config['base_url']."images/pedi_image.php?pagenum=$pagenum&rand=$imgrand&line=$linename\" usemap='#pedimap' alt='Pedigree Tree with Internal Nodes'>";
    include $config['root_dir'].'images/pedi_image_imgmap.inc';
    $imgmap=get_imgmap2($pdarr, $linename);
    echo "\n</pre></div>";
    print write_imagemap("pedimap", $imgmap);
}
?>

<?php

/*  Duplicated for testing  */

if (isset($_REQUEST['line']) && ($_REQUEST['line'] != "")) {
    echo "<div style=\"text-align: left;border:none\">\n";
    print "Alleles of selected markers are shown on the right. &nbsp;&nbsp;<br>";
	$markers = $_SESSION['clicked_buttons'];
	for ($i = 0; $i<count($markers); $i++) {
	  $res = mysqli_query($mysqli, "select marker_name from markers where marker_uid = $markers[$i]");
	  $markername = mysqli_fetch_row($res);
	  $num = $i+1;
	  print "<b>$num</b>: $markername[0]<br>";
	}
	print "<a href = ".$config['base_url']."genotyping/marker_selection.php>Select markers.</a>";
	// Search the pedigree relations to get the pedigree tree
	$nvisited=array();
	$pediarr=array($_REQUEST['line']=>getPedigrees_r($_REQUEST['line'],$nvisited));
	$linename=$_REQUEST['line'];
	// Generate the pedigree annotation with the names of the internal nodes
	// This is the working data structure representation in THT for pedigree
	$treestr2=tree2str_wi($pediarr, $_REQUEST['line']);
	// print "\n".$treestr2."\n";

	// Draw the pedigree tree
	$pdarr=generate_pedigree_matrix($treestr2);
	unset($_SESSION['draw_pedigree_matrix']); // necessary, ow the figure is not refreshed
	unset($_SESSION['draw_snps']);
	$_SESSION['draw_pedigree_matrix']=$pdarr; // data passed through session variables
	$_SESSION['draw_snps']=strtoupper("atgcnncttttcccc");
	// print "<a href=\"images/pedi_image.php\">View Image</a>";  // used for testing
    $pagenum=0;
    if (isset($_GET['pagenum'])) {
        $pagenum=$_GET['pagenum'];
    }
    $imgrand=rand();
    print "<br><br><img style=\"border:none\" src=\"".$config['base_url']."images/pedi_image.php?pagenum=$pagenum&rand=$imgrand&line=$linename\" usemap='#pedimap' alt='Pedigree Tree with Internal Nodes'>";
    include $config['root_dir'].'images/pedi_image_imgmap.inc';
    $imgmap=get_imgmap2($pdarr, $linename);
    echo "\n</pre></div>";
    print write_imagemap("pedimap", $imgmap);
}
?>

<form action="<?php echo $config['base_url']; ?>pedigree/pedigree_tree.php" method="post">
<p><strong>Line Name</strong><br />
<?php
if (isset($_GET['line_name'])) {
    ?>
    <input type="text" name="line" value="<?php echo $_GET['line_name']; ?>" />
    <?php
} else {
    ?>
    <input type="text" name="line" value="<?php echo $_REQUEST['line']; ?>" />
    <?php
}
?>
&nbsp;&nbsp;&nbsp;Examples: cree, nd20448

<p><input type="submit" value="Get Tree" /></p>
</form>
</div>
</div>
</div>
</div>

<?php
require $config['root_dir'] . 'theme/footer.php';
