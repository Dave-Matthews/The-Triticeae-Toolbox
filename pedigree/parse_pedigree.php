<?php
require 'config.php';
/*
 * Logged in page initialization
 */

include $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();
session_start();
include $config['root_dir'] . 'theme/admin_header.php';

?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h2>Display a Purdy pedigree</h2>
  <div class="boxContent">

<?php
$pstr=$_REQUEST['pstr'];
if (isset($pstr) && $pstr!=='') {
    echo "<div style=\"text-align: left;\">";
    // The following is exactly the same with the pedigree_tree.php program.
    // The difference between the programs are just where the pedigree string
    // is passed from.
    print "Alleles of selected markers are shown on the right. &nbsp;&nbsp;<br>";
    $markers = $_SESSION['clicked_buttons'];
    for ($i = 0; $i<count($markers); $i++) {
        $res = mysqli_query($mysqli, "select marker_name from markers where marker_uid = $markers[$i]");
        $markername = mysqli_fetch_row($res);
        $num = $i+1;
        print "<b>$num</b>: $markername[0]<br>";
    }
    print "<a href = ".$config['base_url']."genotyping/marker_selection.php>Select markers.</a><p>";
	$pdarr=generate_pedigree_matrix($pstr);
	unset($_SESSION['draw_pedigree_matrix']); // necessary, ow the figure is not refreshed
	unset($_SESSION['draw_snps']);
	$_SESSION['draw_pedigree_matrix']=$pdarr;
	$_SESSION['draw_snps']=strtoupper("atgcnncttttcccc");
	print "<img style=\"border:none\" src=\"images/pedi_image.php?parse=".rand()."\" usemap='#pedimap' alt='Pedigree Tree with Internal Nodes'>";

	include("../images/pedi_image_imgmap.inc");
	$imgmap=get_imgmap($pdarr);
	echo "</div>";
	print write_imagemap("pedimap", $imgmap);
}
?>

<form action="pedigree/parse_pedigree.php" method="post">
<p><strong>String based pedigree</strong><br />
<input type="text" name="pstr" size = 40 value="<?php echo $_REQUEST['pstr']; ?>" />
  &nbsp;&nbsp;&nbsp;Example: Robust/3/Hazen//Glenn/Karl

<p><input type="submit" value="Get Tree" /></p>
</form>


			</div>
		</div>
	</div>
</div>

<?php include $config['root_dir'] . 'theme/footer.php'; ?>
