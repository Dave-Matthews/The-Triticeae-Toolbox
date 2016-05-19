<?php
/*
* Logged in page initialization
*
* 9/2/2010   J.Lee modify to add new snippet Gbrowse tracks
* 8/29/2010  J.Lee modify to not use iframe for link to Gbrowse   
*/
$usegbrowse = true;
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();
session_start();
include $config['root_dir'].'theme/admin_header.php';
?>

<div id="primaryContentContainer">
<div id="primaryContent">
<h2> Select Markers</h2>
<br>
<div class="boxContent">
<h3>Currently selected markers</h3>
<?php

function get_submitted_mapid()
{
    $us_mapname=$_POST['mapname'] or die('No mapname submitted.');
    $sql = "select map_uid from map
where map_name='" . mysqli_real_escape_string($us_mapname) . "'";
    $sqlr = mysqli_fetch_assoc(mysqli_query($mysqli, $sql));
    return $sqlr['map_uid'];
}

if (isset($_POST['selMarkerstring']) && $_POST['selMarkerstring'] != "") {
  // Handle <space>- and <tab-separated words.
  //$selmkrnames = preg_split("/\r\n/", $_POST['selMarkerstring']);
  $s = preg_replace("/\\s+/", "\\r\\n", $_POST['selMarkerstring']);
  $selmkrnames = explode("\\r\\n", $s);
  // Get the marker uids.
  $selmkrs = array();
  foreach ($selmkrnames as $mkrnm) {
    $sql = "select distinct marker_uid from marker_synonyms where value = '$mkrnm'";
    $r = mysqli_query($mysqli, $sql);
    if (mysqli_num_rows($r) == 0)
      echo "<font color=red>\"$mkrnm\" not found.</font><br>";
    else {
      $row = mysqli_fetch_row($r);
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
    $r = mysqli_query($mysqli, $sql);
    $row = mysqli_fetch_row($r);
    if (! in_array($row[0], $mapids))
      array_push($mapids, $row[0]);
  }
  $_SESSION['mapids'] = $mapids;
 }

if (isset($_POST['selMkrs']) || isset($_POST['selbyname'])) {
  $mapid = get_submitted_mapid();
    if (isset($_POST['selMkrs'])) 
      $selmkrs=$_POST['selMkrs'];
    else {
      $selbyname = $_POST['selbyname'];
      $sql = "select m.marker_uid from markers as m inner join
markers_in_maps as mm using(marker_uid) where mm.map_uid=$mapid and
m.marker_name='" . mysqli_real_escape_string($selbyname) . "'";
      $sqlr = mysqli_fetch_assoc(mysqli_query($mysqli, $sql));
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
  $_SESSION['clicked_buttons']=$selmkrs;
  $_SESSION['mapids']=$mapids;
 }

// If anything is Currently Selected, show.
if (isset($_SESSION['clicked_buttons']) && count($_SESSION['clicked_buttons']) > 0) { 
  print "<form id='deselMkrsForm' action='".$_SERVER['PHP_SELF']."' method='post'>";
  print "<table><tr><td>\n";
  print "<select id='mlist' name='deselMkrs[]' multiple='multiple' size=10>";
  $mapids = $_SESSION['mapids'];
  if (!isset($mapids) || !is_array($mapids))
    $mapids = array();
  reset($mapids);

  $chrlist = array();
  $markerlist = array();
  foreach ($_SESSION['clicked_buttons'] as $mkruid) {
    $mapid = current($mapids);
    next($mapids);
    $sql = "select m.marker_name, mm.chromosome
from markers as m inner join markers_in_maps as mm using(marker_uid)
where marker_uid=$mkruid" . ($mapid ? " and mm.map_uid=$mapid":"");
    $result=mysqli_query($mysqli, $sql)
      //        or die("invalid marker uid\n");
      or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_assoc($result)) {
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
  $chrlist.sort();
 print "</select>";
 print "</td><td>\n";
 echo "<script type='text/javascript'>
var mlist = \$j('#mlist option').map(function () { return \$j(this).text(); });
</script>";

 // Show GBrowse maps.
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
 print "</td></tr></table>\n";
 print "<p><input type='submit' value='Remove marker' style='color: blue' /></p>";
 print "</form>";

  print "<form action='".$config['base_url']."advanced_search.php'>";
 print "<p><input type='submit' value='View haplotypes'></form>";

 // store the selected markers into the database
 $username=$_SESSION['username'];
 if (! isset($username) || strlen($username)<1) $username="Public";
 store_session_variables('clicked_buttons', $username);
 store_session_variables('mapids',$username);
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
  <select name='mapname' size=10 onfocus="DispMapSel(this.value)" onchange="DispMapSel(this.value)">
<?php
$result=mysqli_query($mysqli, "select map_name from map") or die(mysqli_error($mysqli));
while ($row=mysqli_fetch_assoc($result)) {
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

<div class="boxContent">
  <h3> Select using GBrowse</h3>
Hover over a marker and click "Select in THT" in the popup balloon.
<br><a href="/cgi-bin/gbrowse/tht">GBrowse</a><br><br>
  </div>

</div>
</div>
</div>
  <?php include($config['root_dir'].'theme/footer.php'); ?>
