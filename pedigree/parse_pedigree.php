<?php
require 'config.php';
/*
 * Logged in page initialization
 */

include($config['root_dir'] . 'includes/bootstrap.inc');
connect();
session_start();
include($config['root_dir'] . 'theme/admin_header.php');

?>

<div id="primaryContentContainer">
	<div id="primaryContent">
  		<div class="box">
		<h2>Turn a string based pedigree notation into a tree based pedigree</h2>

<?php
$pstr=$_REQUEST['pstr'];
if(isset($pstr) && $pstr!=='') {
	echo "<div style=\"text-align: left;\"><pre>\n";
	// The following is exactly the same with the pedigree_tree.php program.
    // The difference between the programs are just where the pedigree string
    // is passed from.
	$pdarr=generate_pedigree_matrix($pstr);
	unset($_SESSION['draw_pedigree_matrix']); // necessary, ow the figure is not refreshed
	unset($_SESSION['draw_snps']);
	$_SESSION['draw_pedigree_matrix']=$pdarr;
	$_SESSION['draw_snps']=strtoupper("atgcnncttttcccc");
	print "<img style=\"border:none\" src=\"images/pedi_image.php?parse=".rand()."\" usemap='#pedimap' alt='Pedigree Tree with Internal Nodes'>";

	include("../images/pedi_image_imgmap.inc");
	$imgmap=get_imgmap($pdarr);
	echo "\n</pre></div>";
	print write_imagemap("pedimap", $imgmap);
}
?>

<div class="boxContent">
<p>Example: park/3/bonanza/cree//tmp32</p>

<form action="pedigree/parse_pedigree.php" method="post">
<p><strong>String based pedigree</strong><br />
<input type="text" name="pstr" value="<?php echo $_REQUEST['pstr']; ?>" /></p>

<p><input type="submit" value="Get Tree" /></p>
</form>


			</div>
		</div>
	</div>
</div>
</div>

<?php include($config['root_dir'] . 'theme/footer.php'); ?>
