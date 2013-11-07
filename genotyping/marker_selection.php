<?php
/**
 * select markers and save in session variable
 * 
 * @category PHP
 * @package T3
 * 
 * 16mar12 dem Allow selecting markers that are not in maps.
 *             Un-require all marker names to also be in marker_synonyms.value.
 * 9/2/2010   J.Lee modify to add new snippet Gbrowse tracks
 * 8/29/2010  J.Lee modify to not use iframe for link to Gbrowse   
 */
$usegbrowse = False;
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();
session_start();
include($config['root_dir'].'theme/admin_header.php');
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <script type="text/javascript" src="theme/new.js"></script>
  <h2> Select Markers</h2>
  <br>
  <div id= "current" class="boxContent">
  <h3>Currently selected markers</h3>
  <?php

  /**
   * get map_uid for given mapname
   * @return integer
   */
  function get_submitted_mapid() {
  $us_mapname=$_POST['mapname'] or die('No mapname submitted.');
  $sql = "select map_uid from map
where map_name='" . mysql_real_escape_string($us_mapname) . "'";
  $sqlr = mysql_fetch_assoc(mysql_query($sql));
  return $sqlr['map_uid'];
}  

if ( isset($_POST['selMarkerstring']) && $_POST['selMarkerstring'] != "" ) {
  // Handle <space>- and <tab-separated words.
  //$selmkrnames = preg_split("/\r\n/", $_POST['selMarkerstring']);
  $s = preg_replace("/\\s+/", "\\r\\n", $_POST['selMarkerstring']);
  $selmkrnames = explode("\\r\\n", $s);
  // Get the marker uids.
  $selmkrs = array();
  foreach ($selmkrnames as $mkrnm) {
    //$sql = "select distinct marker_uid from marker_synonyms where value = '$mkrnm'";
    $sql = "select distinct marker_uid from marker_synonyms where value = '$mkrnm' UNION
            select marker_uid from markers where marker_name = '$mkrnm'";
    $r = mysql_query($sql);
    if (mysql_num_rows($r) == 0)
      echo "<font color=red>\"$mkrnm\" not found.</font><br>";
    else {
      $row = mysql_fetch_row($r);
      // Trap case where a marker is entered twice, even as synonym, e.g. 11_0090 and 1375-2534.
      if (! in_array($row[0], $selmkrs))
	array_push($selmkrs, $row[0]);
    }
  }
  $clkmkrs=$_SESSION['clicked_buttons'];
  if (!isset($clkmkrs) || ! is_array($clkmkrs)) $clkmkrs=array();
  foreach($selmkrs as $mkruid) {
    if (! in_array($mkruid, $clkmkrs)) 
      array_push($clkmkrs, $mkruid);
  }
  $_SESSION['clicked_buttons'] = $clkmkrs;
  // Get the uid of a map each of the markers is on.
    $mapids = $_SESSION['mapids'];
    if (!isset($mapids) || !is_array($mapids))
      $mapids = array();
  foreach ($selmkrs as $mkr) {
    $sql = "select distinct map_uid from markers_in_maps where marker_uid = $mkr";
    //$sql = "select distinct map_uid from markers where marker_uid = $mkr";
    $r = mysql_query($sql);
    $row = mysql_fetch_row($r);
    if (! in_array($row[0], $mapids))
      array_push($mapids, $row[0]);
  }
  $_SESSION['mapids'] = $mapids;
  ?>
  <script type="text/javascript">
    update_side_menu();
  </script>
  <?php
 }

if (isset($_POST['selMkrs']) || isset($_POST['selbyname'])) {
  $mapid = get_submitted_mapid();
    if (isset($_POST['selMkrs'])) 
      $selmkrs=$_POST['selMkrs'];
    else {
      $selbyname = $_POST['selbyname'];
/*       $sql = "select m.marker_uid from markers as m inner join */
/* markers_in_maps as mm using(marker_uid) where mm.map_uid=$mapid and */
/* m.marker_name='" . mysql_real_escape_string($selbyname) . "'"; */
      $sql = "select m.marker_uid from markers where
m.marker_name='" . mysql_real_escape_string($selbyname) . "'";
      $sqlr = mysql_fetch_assoc(mysql_query($sql));
      $selmkrs = array($sqlr['marker_uid']);
    }
    $mapids = $_SESSION['mapids'];
    if (!isset($mapids) || !is_array($mapids))
      $mapids = array();
    $clkmkrs=$_SESSION['clicked_buttons'];
    if (!isset($clkmkrs) || ! is_array($clkmkrs)) $clkmkrs=array();
    foreach($selmkrs as $mkruid) {
      if (! in_array($mkruid, $clkmkrs)) {
	array_push($clkmkrs, $mkruid);
	array_push($mapids, $mapid);
      }
    }
    $_SESSION['clicked_buttons'] = $clkmkrs;
    $_SESSION['mapids'] = $mapids;
    ?>
    <script type="text/javascript">
      update_side_menu();
    </script>
    <?php
  }

if (isset($_POST['deselMkrs'])) {
  $selmkrs=$_SESSION['clicked_buttons'];
  $mapids=$_SESSION['mapids'];
  foreach ($_POST['deselMkrs'] as $mkr) {
    if (($mkridx=array_search($mkr, $selmkrs)) !== false) {
      array_splice($selmkrs, $mkridx,1);
      array_splice($mapids, $mkridx, 1);
    }
  }
  if (count($selmkrs) > 0) {
    $_SESSION['clicked_buttons']=$selmkrs;
    $_SESSION['mapids']=$mapids;
  } else {
    unset($_SESSION['clicked_buttons']);
  }
  ?>
  <script type="text/javascript">
    update_side_menu();
  </script>
  <?php
 }

// If anything is Currently Selected, show.
if (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0) && (count($_SESSION['clicked_buttons']) < 1000)) { 
  print "<form id='deselMkrsForm' action='".$_SERVER['PHP_SELF']."' method='post'>";
  print "<table><tr><td>\n";
  print "<select id='mlist' name='deselMkrs[]' multiple='multiple' size=10>";
  $mapids = $_SESSION['mapids'];
  if (!isset($mapids) || !is_array($mapids))
    $mapids = array();
  reset($mapids);

  $chrlist = array();
  $markerlist = array();
  $count_markers = 0;
  foreach ($_SESSION['clicked_buttons'] as $mkruid) {
    $count_markers++;
    $mapid = current($mapids);
    next($mapids);
/*     $sql = "select m.marker_name, mm.chromosome */
/* from markers as m inner join markers_in_maps as mm using(marker_uid) */
/* where marker_uid=$mkruid" . ($mapid ? " and mm.map_uid=$mapid":""); */
    $sql = "select marker_name from markers where marker_uid=$mkruid";
    $result=mysql_query($sql)
      //        or die("invalid marker uid\n");
      or die(mysql_error());
    while ($row=mysql_fetch_assoc($result)) {
      $selval=$row['marker_name'];
      $selchr=$row['chromosome'];
      if(! in_array($selval,$markerlist)) {
        array_push($markerlist, $selval);
        array_push($chrlist, $selchr);
        print "<option value='$mkruid'>$selval</option>\n";
      }
    }
  }
  $chrlist = array_unique($chrlist);
 print "</select>";
 print "</td><td>\n";

 // Show GBrowse maps.
 if ($usegbrowse) {
   sort($chrlist);
   echo "<script type='text/javascript'>
   var mlist = \$j('#mlist option').map(function () { return \$j(this).text(); });
   </script>";
 foreach ($chrlist as $chr) {
   echo "<div id='gbrowse_$chr'></div>\n";
   echo <<<EOD
<script type="text/javascript">
    \$j('#gbrowse_$chr')
    .bind('ajaxSend',
	  function () {
	    \$j(this).html("<p>Loading track for chromosome $chr...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>");
	    \$j(this).addClass('inprogress');
	  })
    .bind('ajaxComplete',
	  function () { \$j(this).removeClass('inprogress'); });
  \$j(document).ready(function () {
      var hilite = [], index;
      for (index=0; index<mlist.length; ++index)
	hilite.push(mlist[index] + '@orange');
      loadGbrowse("#gbrowse_$chr", " #details_panel",
		  ["name=" + encodeURIComponent('chr$chr'),
		   "h_feat=" + encodeURIComponent(hilite.join(' ')),
		   'label=' + encodeURIComponent('Marker OWB_2383'),
		   'label=' + encodeURIComponent('Marker UCR04162008'),
           'label=' + encodeURIComponent('Marker SteptoeMorex'),
           'label=' + encodeURIComponent('Marker MorexBarke'),
		   'grid=on', 'show_tooltips=on',
		   '.cgifields=show_tooltips', 'drag_and_drop=on']
		  .join('&'),
		  function () {
		    // \$j("#gbrowse_$chr").find("area[href^='?ref']")
		    //   .each(function () {
		    // 	  \$j(this).removeAttr('href');
		    // 	});
		    \$j("#gbrowse_$chr")
		      .prepend("<p>Chromosome $chr</p>");
		    // \$j("#mlist")
		    //   .change(function () {
		    // 	  \$j("#mlist option:selected")
		    // 	    .map(function () {
		    // 		var outerThis = this;
		    // 		var filter = "#gbrowse_$chr area[href]";
		    // 		\$j(filter)
		    // 		  .filter(function ()
		    // 			  {
		    // 			    return \$j(this).attr('href').indexOf("name=" + \$j(outerThis).text() + ";") != -1; }).trigger('mouseover');
		    // 	      });
		    // 	});
		  });
    });
</script>
EOD;
 }
 }
 print "</td></tr></table>\n";
 print "<p><input type='submit' value='Remove marker' style='color: blue' /></p>";
 print "</form>";

 //* print "<form action='".$config['base_url']."haplotype_search.php'>";
 //* print "<p><input type='submit' value='View haplotypes'></form>"; */

 // store the selected markers into the database
 $username=$_SESSION['username'];
 if (! isset($username) || strlen($username)<1) $username="Public";
 store_session_variables('clicked_buttons', $username);
 store_session_variables('mapids',$username);
 } elseif (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0) && (count($_SESSION['clicked_buttons']) >= 1000)) {
   $count = count($_SESSION['clicked_buttons']);
   print "$count markers selected. ";
   print "<a href=genotyping/display_markers.php>Display Markers</a><br>\n";
 } // end of if Currently Selected
 else print "None<br>";
?>
</div>

<div class="boxContent">
  <h3>Select markers by name</h3>
  <form action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
  <table><tr><td>
  <textarea rows=6 cols=10 name=selMarkerstring></textarea>
  <td>Synonyms will be translated.
  <p><input type=submit value=Select style=color:blue>
  </tr></table>
  </form>
  </div>

  <div id="markerSel" class="boxContent">
  <h3> Select markers in a range of map positions</h3>
  <form id="markerSelForm" action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
  <table id="markeSelTab">
  <thead>
  <tr> <th>Maps</th><th>Range</th><th>Markers</th></tr>
  </thead>
  <tbody>
  <tr><td>
  <select name='mapname' size=10 onClick="DispMapSel(this.value)" onchange="DispMapSel(this.value)">
<?php
$result=mysql_query("select map_name from map") or die(mysql_error);
while ($row=mysql_fetch_assoc($result)) {
  $selval=$row['map_name'];
  print "<option value='$selval'>$selval</option>\n";
 }
?>
</select>
<td>Choose map.
<td>
</tr>
</tbody>
</table>
</form>
</div>

<h3>Select by genotyping platform and experiment</h3>
<form action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
<div class="boxContent" style="float: left; margin-buttom: 1.5em;">
  <table>
  <thead>
  <tr><th>Platform
  <tbody>
  <tr><td>
  <select name='platform[]' size=10 multiple onchange="javascript: update_platform(this.options)">
<?php
$result=mysql_query("select distinct(platform.platform_uid), platform_name from platform, genotype_experiment_info where platform.platform_uid = genotype_experiment_info.platform_uid") or die(mysql_error);
while ($row=mysql_fetch_assoc($result)) {
  $uid = $row['platform_uid'];
  $val = $row['platform_name'];
  print "<option value='$uid'>$val</option>\n";
}
?>
</select>
</table>
</form>
</div>
<div class="boxContent" id="col2" style="float: left; margin-buttom: 1.5em;"></div>
<div class="boxContent" style="clear: both; float: left; width: 100%">
  <h3> Select using GBrowse</h3>
Hover over a marker and click "Select in THT" in the popup balloon.
<br><a href="/cgi-bin/gbrowse/tht">GBrowse</a><br><br>
  </div>

</div>
</div>
</div>
<script type="text/javascript" src="genotyping/marker.js"></script>
  <?php include($config['root_dir'].'theme/footer.php'); ?>
