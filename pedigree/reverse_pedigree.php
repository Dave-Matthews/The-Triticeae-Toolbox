<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir']. 'includes/bootstrap.inc');

connect();
session_start();

include($config['root_dir']. 'theme/normal_header.php');
?>

<div id="primaryContentContainer">
	<div id="primaryContent">

<?php

if(isset($_REQUEST['line'])) {

	// Search the pedigree relations to get the pedigree tree
	$pediarr = getReversePedigrees($_REQUEST['line']);

	// Names at all the Levels
	$level = getNameChart($pediarr);

	// Display Table
	echo "<h2>Children for the line ". $level[0][0] . "</h2>";
	echo "\n<table class=\"tableclass1\">
	<thead>
	<tr>
		<td><strong>Child</strong></td>
		<td><strong>Generations Away</td>
	</tr>
	</thead>";
	for($i=1; $i<count($level); $i++) {
		for($j=0; $j<count($level[$i]); $j++) {
			echo "\n\t<tr>";
			echo "\n\t\t<td><a href=\"./pedigree/show_pedigree.php?line=".urlencode($level[$i][$j])."\">".$level[$i][$j]."</a></td>";
			echo "\n\t\t<td align=\"center\">".$i."</td>";
			echo "\n\t</tr>";
		}
	}
	echo "\n</table>";


	/*
	// Define the 3 arrays
	$blocks = array();
	$lines = array();
	$text = array();

	// Block Sizes
	$blockh = "50";
	$blockw = "50";
	$blockmargin = "10";

	// Chart the tree
	$chart = getChart($pediarr);
	print_r($chart);

	// Specify Image Dimensions
	$imgwidth = count($chart) * ($blockw + $blockmargin) + $blockmargin;
	$imgheight = $imgwidth;			//square

	// Insert Blocks
	for($i=0; $i<count($chart); $i++) {
		$spacex = ($blockmargin + $blockw) * $i;
		$spacey = ($blockmargin + $blockh) * $i;
		array_push($blocks, array('coords'=>array($blockmargin+$spacex, $blockmargin + $spacey, ($blockw+$blockmargin)+$spacex, $blockh + $blockmargin + $spacey),
							'imgclr'=>'im_grayblue',
							'text'=>'name',
							'textsize'=>10,
							'border'=>0,
							'border_color'=>'im_blue',
							'link'=>$_SERVER['PHP_SELF'],
							'title'=>'name'));
	}


	$img = array();

	// The size of the iamge
	$img['image_size'] = array();
	$img['image_size'][0] = $imgwidth;
	$img['image_size'][1] = $imgheight;

	// The blocks to be drawn in the image
	$img['image_blks'] = $blocks;

	// The lines to be drawn in the image
	$img['image_dlns'] = $lines;

	// The text (just text) to be drawn in the image
	$img['image_dtxs'] = $text;

	print_r($img);

	unset($_SESSION['draw_map_matrix']);
	$_SESSION['draw_map_matrix'] = $img;
	//echo "<a href=\"images/map_image.php\">Image</a>";
	echo "<img style=\"border:none\" src=\"images/map_image.php\" alt=\"Image\">";
	*/
}
?>

<form action="./pedigree/reverse_pedigree.php" method="post">
<p><strong>Line Name</strong><br />
<input type="text" name="line" value="<?php echo $_REQUEST['line']; ?>" /></p>

<p><input type="submit" value="Get Children" /></p>
</form>


	</div>
</div>
</div>

<?php include($config['root_dir']. 'theme/footer.php');?>
