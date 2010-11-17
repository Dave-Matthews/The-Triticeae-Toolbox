<?php
require 'config.php';
ob_start();
include($config['root_dir'].'includes/bootstrap.inc');
connect();
session_start();
if(!isset($_GET['scroll'])){
?>

<body onload="if(typeof recallScroll === 'function') recallScroll(null)">

<?php
}
/**
 * Check for overlaps and find a place for a new label
 *
 * @param int $xval the x coord of the label
 * @param array $yarr the existing labels
 * @return int $lby the y coord (upper left corner) for the new label
 *
 */
function check_overlap ($xval, $yval, array $yarr, $lbw, $lbh) {
	for ($i=0; $i<count($yarr); $i++) {
		if (abs($yarr[$i][1]-$yval)<=$lbw-5 && abs($yarr[$i][0]-$xval)<=$lbh) {
			$yval+=$lbh+5;
			$xval+=$lbh+5;
			$i=$i-30>0 ? $i-30 : 0;
		}
	}
	return array($xval, $yval);
}

/**
 * Get the height of the highest label
 *
 * @param array $yarr label positions
 * @return int $clh
 */
function get_cumulative_label_height(array $yarr) {
	$clh=0;
	for ($i=0; $i<count($yarr); $i++) {
		if ($yarr[$i][1]>$clh) $clh=$yarr[$i][1];
	}
	return $clh;
}

/**
 * Get the scale unit and the starting scale
 */
function get_scales($dstt, $dend) {
	$sunit=0.001; // starting scale
	while ($sunit*20<($dend-$dstt)) {
		$sunit*=10;
	}
	$iunit=0; // initial mark
	if ($iunit>$dstt) {
		while (($iunit-$sunit)>$dstt) $iunit-=$sunit;
	}
	else {
		while ($iunit<$dstt) $iunit+=$sunit;
	}
	return array($sunit, $iunit);
}

/**
 * Returns the starting and ending position of the map and the unit it uses
 *
 * @param string $mapname the name of the map
 * @return array $mapdim (start, end, unit)
 */
function get_map_dim ($mapname) {
	if (! isset($mapname) || $mapname=='') {
		error(1, "Must supply a map name");
	}
	$result=mysql_query("SELECT mapset_uid, map_start, map_end, map_uid FROM map WHERE map_name=\"$mapname\"");
	$num_rows=mysql_num_rows($result);

	$mstt='';
	$mend='';
	$munit='';
	$mtype='';
	$msetname='';
	$mapid=-1;
	$msetid=-1;
	if ($num_rows<=0) {
		error(1, "Invalid map name");
	}
	else {
		while( $row = mysql_fetch_assoc($result) ) {
			$msetid=$row['mapset_uid'];
			$mstt=$row['map_start'];
			$mend=$row['map_end'];
			$mapid=$row['map_uid'];
		}
	}
	$result2=mysql_query("select mapset_name, map_type, map_unit from mapset where mapset_uid=$msetid");
	$nrows=mysql_num_rows($result2);
	if ($nrows<=0) {
		error(1, "Invalid mapset id");
	}
	else {
		while( $row = mysql_fetch_assoc($result2) ) {
			$msetname=$row['mapset_name'];
			$munit=$row['map_unit'];
			$mtype=$row['map_type'];
		}
	}
	if ($mstt==$mend) {
		$result=mysql_query("select min(start_position), max(start_position) from markers_in_maps where map_uid=$mapid");
		$num_rows=mysql_num_rows($result);
		if ($num_rows<=0) {
			error(1, "Invalid map name");
		}
		else {
			while( $row = mysql_fetch_assoc($result) ) {
				$mstt=array_shift($row);
				$mend=array_shift($row);
			}
		}
	}
	return array($mstt, $mend, $munit, $mtype, $msetname, $mapid);
}

/**
 * Get the markers in the map specified by $mapid and between $ustt and $uend
 *
 * @param int $mapid
 * @param int $ustt
 * @param int $uend
 * @return array $markers
 */
function get_map_markers ($mapid, $ustt, $uend) {
	if (! isset($mapid) || $mapid=='') {
		error(1, "Must supply a map name");
	}
	$result=mysql_query("SELECT start_position, end_position, marker_name, A.marker_uid from markers_in_maps as A, markers as B where A.marker_uid=B.marker_uid and map_uid=$mapid and start_position>=$ustt and start_position<=$uend order by start_position");
	// print "SELECT start_position, end_position, marker_name from markers_in_maps as A, markers as B where A.marker_uid=B.marker_uid and map_uid=$mapid order by start_position";
	$nrows=mysql_num_rows($result);
	$markers=array();
	if ($nrows<=0) {
		//error(1, "No Markers found in the range");
	}
	else {
		while( $row = mysql_fetch_assoc($result) ) {
			$mkstt=$row['start_position'];
			$mkend=$row['end_position'];
			$mknm=$row['marker_name'];
			$mkid=array_pop($row);
			array_push($markers, array('start'=>$mkstt, 'end'=>$mkend, 'name'=>$mknm, 'id'=>$mkid));
		}
	}
	return $markers;
}

/**
 * get the smallest distance between two markers
 */
function get_smlst(array $markers) {
	$smlst=-1;
	for ($i=0; $i<count($markers)-1; $i++) {
		for ($j=$i+1; $j<count($markers); $j++) {
			$dist=abs($markers[$i]['start']-$markers[$j]['start']);
			if ($dist==0) continue;
			if ($smlst<0 || $smlst>$dist) {
				$smlst=$dist;
			}
		}
	}
	return $smlst;
}

/**
 * Generate the image map
 *
 * @param array $blks the drawing blocks
 * @param string $umapname the name of the image map
 * @return string $mapstr
 */
function get_imagemap (array $blks, $umapname) {
	$imgmap=array();
	foreach ($blks as $blk) {
		if (isset($blk['link']) && $blk['link']!=='' && $blk['link']!='TODO') {
			array_push($imgmap, array('shape'=>'rect',
									  'coords'=>implode(",",$blk['coords']),
									  'href'=>$blk['link'],
									  'alt'=>'',
									  'title'=>$blk['title']));
		}
	}
	$mapstr="<map name=\"$umapname\">";
	foreach ($imgmap as $marr) {
		$mapstr.="<area ";
		foreach ($marr as $mk=>$mv) {
			$mapstr.="$mk=\"$mv\" ";
		}
		$mapstr.=">\n";
	}
	$mapstr.="</map>";
	return $mapstr;
}

if (isset($_REQUEST['mapname'])) {
	$mapname=$_REQUEST['mapname'];
	// get the start($mapdim[0]), end ($mapdim[1]) positions of a map and unit ($mapdim[2])
	$mapdim=get_map_dim($mapname);
	// get the mapset name of a map, ['name']->name, ['unit']->unit, ['type']->type
	$dstt=$mapdim[0]; // data start
	$dend=$mapdim[1]; // data end
	if ($dend-$dstt<=1) die("Invalid map range");
	// dealing with zoom and move
	if (isset($_REQUEST['mapstt'])) {
		$ustt=$_REQUEST['mapstt'];
		if ($ustt>=$dstt && $ustt<=$dend) $dstt=$ustt;
	}
	if (isset($_REQUEST['mapend'])) {
		$uend=$_REQUEST['mapend'];
		if ($uend>=$dstt && $uend<=$dend) $dend=$uend;
	}
	while ($dend-$dstt<8) {
		$dend=$dend+2>$mapdim[1]? $mapdim[1] : $dend+2;
		$dstt=$dstt-2<$mapdim[0]? $mapdim[0] : $dstt-2;
	}
	$zoomunit=round(($dend-$dstt)/5);
	$mapid=$mapdim[count($mapdim)-1];
	// get the markers associated with a map
	$markers=get_map_markers($mapid, $dstt, $dend);
	// get the distance of any two closest (but not overlapped) markers
	$smlst=get_smlst($markers);
	$xdim=round(($dend-$dstt)/$smlst)*2;
	if ($xdim<800) $xdim=800;
	if ($xdim>1600) $xdim=1600;
	$xratio=$xdim/($dend-$dstt);

	/* the general settings for the graph */
	$afb=100; // axis from the left edge
	$hmargin=50; // margin from the top, excluding the buttons
	$mls=60; // minimum distance from the marker labels to the axis
	$lbw=50; // label width
	$lbh=16; // label height
	$bt_width=50; // button (first row) width
	$bt_heigth=50; // button (first row) height
	$frh=50;  // first row height
	$bmargin=5; // button margin
	$font="../images/trebuchet.ttf"; // true type font

    $ychk=array(); // the position of the lables
    for ($i=0; $i<count($markers); $i++) {
    	$mkstt=$markers[$i]['start'];
    	$mkx=round(($mkstt-$dstt)*$xratio)+$hmargin+$frh; // x postion on the map
		$lbx=$mkx+$mls;
		$lby=$mls;
		$chkflag=0;
		$chkpos=check_overlap($lbx, $lby, $ychk, $lbw, $lbh);
		// print "<br>$lbx $lby<br>";
		array_push($ychk,array($chkpos[0], $chkpos[1], $markers[$i]['name'],$markers[$i]['id']));
    }
    $clh=get_cumulative_label_height($ychk); // the cumulative label height
	// image dimension
	$img_height=$frh+$hmargin+$clh+$xdim;
	$img_width=$hmargin+$afb+$clh+$lbw;
	if ($img_width<800) $img_width=800;

	$blks=array();
	$dlns=array();
	$dtxs=array();
	// draw the first row
	array_push($blks, array('coords'=>array(0,0,$img_width, $frh),
							'imgclr'=>'im_khaki3',
							'text'=>'',
							'textsize'=>8,
							'border'=>1,
							'border_color'=>'im_khaki3',
							'link'=>'',
							'title'=>''));
	array_push($dtxs, array('x'=>50, 'y'=>20, 'text'=>"Map: $mapname", 'fontsize'=>6, 'text_clr'=>'im_black'));
	array_push($dtxs, array('x'=>5, 'y'=>$img_height-30, 'text'=>'THTMAP', 'fontsize'=>1, 'text_clr'=>'im_black'));
	// draw the mapset info button
	array_push($blks, array('coords'=>array(350,$bmargin,350+$bt_width*3-$bmargin, $frh-$bmargin),
							'imgclr'=>'im_khaki2',
							'text'=>'MapSet Information',
							'textsize'=>4,
							'border'=>0,
							'border_color'=>'im_blue',
							'link'=>'TODO',
							'title'=>'Get MapSet Information'));
	// draw the zooming buttons

	array_push($blks, array('coords'=>array(500+$bmargin,$bmargin,500+$bt_width-$bmargin, $frh-$bmargin),
							'imgclr'=>'im_khaki2',
							'text'=>'  <',
							'textsize'=>10,
							'border'=>0,
							'border_color'=>'im_blue',
							//'link'=>$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt-$zoomunit)."&mapend=".($dend-$zoomunit),
							'link'=>"javascript:rememberScroll('".$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt-$zoomunit)."&mapend=".($dend-$zoomunit)."')",
							'title'=>'Move Left'));
	// array_push($dtxs, array('x'=>500+$bmargin, 'y'=>$bmargin, 'text'=>'<', 'fontsize'=>18, 'text_clr'=>'im_black'));
	array_push($blks, array('coords'=>array(550+$bmargin,$bmargin,550+$bt_width-$bmargin, $frh-$bmargin),
							'imgclr'=>'im_khaki2',
							'text'=>'  >',
							'textsize'=>10,
							'border'=>0,
							'border_color'=>'im_blue',
							//'link'=>$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt+$zoomunit)."&mapend=".($dend+$zoomunit),
							'link'=>"javascript:rememberScroll('".$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt+$zoomunit)."&mapend=".($dend+$zoomunit)."')",
							'title'=>'Move Right'));
	array_push($blks, array('coords'=>array(600+$bmargin,$bmargin,600+$bt_width-$bmargin, $frh-$bmargin),
							'imgclr'=>'im_khaki2',
							'text'=>'  +',
							'textsize'=>10,
							'border'=>0,
							'border_color'=>'im_blue',
							//'link'=>$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt+$zoomunit)."&mapend=".($dend-$zoomunit),
							'link'=>"javascript:rememberScroll('".$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt+$zoomunit)."&mapend=".($dend-$zoomunit)."')",
							'title'=>'Zoom In'));
	array_push($blks, array('coords'=>array(650+$bmargin,$bmargin,650+$bt_width-$bmargin, $frh-$bmargin),
							'imgclr'=>'im_khaki2',
							'text'=>'  -',
							'textsize'=>10,
							'border'=>0,
							'border_color'=>'im_blue',
							//'link'=>$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt-$zoomunit)."&mapend=".($dend+$zoomunit),
							'link'=>"javascript:rememberScroll('".$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=".($dstt-$zoomunit)."&mapend=".($dend+$zoomunit)."')",
							'title'=>'Zoom Out'));
    // draw the axis
	$axis_x=$hmargin+$afb;
	// array_push($dlns, array($hmargin, $axis_y, $axisend, $axis_y, 'im_black'));
	array_push($dlns, array($axis_x, $frh+$hmargin, $axis_x, $frh+$hmargin+$xdim, 'im_black'));
	// draw the scales
	$dscale=get_scales($dstt, $dend);
	$sunit=$dscale[0];
	$iunit=$dscale[1];
	while ($iunit>=$dstt && $iunit<=$dend) {
		if ($iunit!=$dstt && $iunit!=$dend) {
			$dunit=round(($iunit-$dstt)*$xratio)+$hmargin+$frh;
			array_push($dlns, array($axis_x-5, $dunit, $axis_x, $dunit, 'im_black'));
			array_push($dtxs, array('x'=>$axis_x-20, 'y'=>$dunit, 'text'=>$iunit, 'fontsize'=>3, 'text_clr'=>'im_black'));
		}
		$iunit+=$sunit;
	}
	$dunit=$hmargin+$frh; // draw the start
	array_push($dlns, array($axis_x-15, $dunit, $axis_x, $dunit, 'im_black'));
	array_push($dtxs, array('x'=>$axis_x-50, 'y'=>$dunit, 'text'=>round($dstt,2), 'fontsize'=>3, 'text_clr'=>'im_black'));
	$dunit=round(($dend-$dstt)*$xratio)+$hmargin+$frh; // draw the end
	array_push($dlns, array($axis_x-15, $dunit, $axis_x, $dunit, 'im_black'));
	array_push($dtxs, array('x'=>$axis_x-50, 'y'=>$dunit, 'text'=>round($dend,2), 'fontsize'=>3, 'text_clr'=>'im_black'));

	// draw the labels

	// if a label is clicked, it will turn red, click again it will recover
    $clkblk=array();
    if (isset($_SESSION['clicked_buttons']) && is_array($_SESSION['clicked_buttons'])) $clkblk=$_SESSION['clicked_buttons'];
    if (isset($_REQUEST['clicked'])) {
    	if (in_array($_REQUEST['clicked'],$clkblk)) {
    		$idx=array_search($_REQUEST['clicked'], $clkblk);
    		array_splice($clkblk, $idx, 1);
    	}
    	else {
    		array_push($clkblk, $_REQUEST['clicked']);
    	}
    }
    $blkclrs=array('im_white','im_orange');
    // a clear button
	array_push($blks, array('coords'=>array(700+$bmargin,$bmargin,700+$bt_width-$bmargin, $frh-$bmargin),
							'imgclr'=>'im_khaki2',
							'text'=>'Clear',
							'textsize'=>4,
							'border'=>0,
							'border_color'=>'im_blue',
							//'link'=>$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=$dstt&mapend=$dend&clearall=1",
							'link'=>"javascript:rememberScroll('".$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=$dstt&mapend=$dend&clearall=1')",
							'title'=>'Clear all marker selections'));

	if (isset($_REQUEST['clearall']) && $_REQUEST['clearall']==1) $clkblk=array();
    $_SESSION['clicked_buttons']=$clkblk;

	// store the selected markers into the database
    $username=$_SESSION['username'];
    if (! isset($username) || strlen($username)<1) $username="Public";
    store_session_variables('clicked_buttons', $username);

    // draw the markers
	for ($i=0; $i<count($ychk); $i++) {
		// draw the label box
		$dlbx=$ychk[$i][0];
		$dlby=$hmargin+$afb+$ychk[$i][1];
		$dlbt=$ychk[$i][2];
		$dlbi=$ychk[$i][3];
		$clrflg=0;
		if (is_array($clkblk) && in_array($dlbi, $clkblk)) $clrflg=1;
		$chktlt=array("Check $dlbt", "Uncheck $dlbt");
		array_push($blks, array('coords'=>array($dlby,$dlbx-$lbh/2,$dlby+$lbw,$dlbx+$lbh/2),
							'imgclr'=>$blkclrs[$clrflg],
							'text'=>substr($dlbt,0, 8),
							'textsize'=>2,
							'border'=>1,
							'border_color'=>'im_khaki3',
							//'link'=>$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=$dstt&mapend=$dend&clicked=$dlbi",
							'link'=>"javascript:rememberScroll('".$_SERVER['PHP_SELF']."?mapname=$mapname&mapstt=$dstt&mapend=$dend&clicked=$dlbi')",
							'title'=>$chktlt[$clrflg]));
		// draw the linking line
		// print "<br> $dlbt $dlbx $dlby";
		$lkl=5; // length of short link
		array_push($dlns, array($hmargin+$afb, $dlbx-$ychk[$i][1],$dlby,$dlbx, 'im_black'));
		// array_push($dlns, array($dlbx-$ychk[$i][1], $axis_y, $dlbx-$ychk[$i][1], $axis_y-$lkl, 'im_black'));
		// array_push($dlns, array($dlbx-$ychk[$i][1], $axis_y-$lkl, $dlbx-$lkl, $dlby, 'im_black'));
		// array_push($dlns, array($dlbx-$lkl, $dlby, $dlbx, $dlby, 'im_black'));
	}
	$_SESSION['draw_map_matrix']=array('image_size'=>array($img_width, $img_height), 'image_blks'=>$blks, 'image_dlns'=>$dlns, 'image_dtxs'=>$dtxs);
	// print_r($blks);
	// print "<a href=\"http://lab.bcb.iastate.edu/sandbox/yhames04/images/map_image.php\">View Image</a>";  // used for testing
	$imgrand=rand();
	?>
<script type="text/javascript">
	// This set of functions allows the map to remeber where the user had
	// scrolled the iframe before selecting a marker.
	// @author Gavin Monroe
	function rememberScroll(link)
	{
		var scroll = pageYOffset;
		link = link + "&scroll=" + scroll;
		//alert(top.src);
		//window.parent.foo();
		//document.location = link;
		parent.reloadMap(link, scroll);
	}
	function recallScroll(scroll){
		if(scroll == null )scroll = getURLVar("scroll");
		//else alert(scroll);
		document.body.scrollTop = scroll;
		window.pageYOffset = scroll;
	}
	function getURLVar(urlVarName) {
		var urlHalves = String(document.location).split('?');
		var urlVarValue = '';
		if(urlHalves[1]){
			var urlVars = urlHalves[1].split('&');
			for(i=0; i<=(urlVars.length); i++){
				if(urlVars[i]){
					var urlVarPair = urlVars[i].split('=');
					if (urlVarPair[0] && urlVarPair[0] == urlVarName) {
						urlVarValue = urlVarPair[1];
					}
				}
			}
		}
		return urlVarValue;
	}

</script>
<?php
	if(isset($_GET['scroll']))$onload="recallScroll({$_GET['scroll']})";
	print "<img id='mapimg' onload='$onload' style=\"border:none\" src=\"".$config['base_url']."images/map_image.php?rand=$imgrand\" usemap='#mapmap' alt=\"Map $mapname \">";
	print get_imagemap($blks, 'mapmap')."\n";
}

?>

	<div class="box">
		<h2>Show Markers in Maps</h2>
		<div class="boxContent">
			<p>Choose a map</p>
			<form action="<?php echo $config['base_url']; ?>genotyping/mapdisplay_iframe.php" method="post">
			<p><strong>map name</strong><br />
			<select name='mapname' size=20>;
			<?php
			$result=mysql_query("select map_name from map") or die(mysql_error);
			while ($row=mysql_fetch_assoc($result)) {
				$selval=$row['map_name'];
				print "<option value=\"$selval\">$selval</option>\n";
			}
			print "</select></p><p>";
			?>
			<p><input type="submit" value="Get Map" /></p>
			</form>
		</div>
	</div>
<?php
	if(!isset($_GET['scroll'])){
?>
</body>
<?php
	}
ob_end_flush();
?>
