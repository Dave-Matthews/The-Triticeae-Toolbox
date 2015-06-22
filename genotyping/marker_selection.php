<?php
/**
 * select markers and save in session variable
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/genotyping/marker_selection.php
 *
 * 16mar12 dem Allow selecting markers that are not in maps.
 *            Un-require all marker names to also be in marker_synonyms.value.
 * 9/2/2010   J.Lee modify to add new snippet Gbrowse tracks
 * 8/29/2010  J.Lee modify to not use iframe for link to Gbrowse
 */
$usegbrowse = false;
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
connect();
$mysqli = connecti();
session_start();
require $config['root_dir'].'theme/admin_header.php';
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
 *
 * @return integer
 */
function getSubmittedMapid()
{
    $us_mapname=$_POST['mapname'] or die('No mapname submitted.');
    $sql = "select map_uid from map
    where map_name='" . mysql_real_escape_string($us_mapname) . "'";
    $sqlr = mysql_fetch_assoc(mysql_query($sql));
    return $sqlr['map_uid'];
}

if (isset($_POST['selMarkerstring']) && $_POST['selMarkerstring'] != "") {
    // Handle <space>- and <tab-separated words.
    $s = preg_replace("/\\s+/", "\\r\\n", $_POST['selMarkerstring']);
    $selmkrnames = explode("\\r\\n", $s);
    // Get the marker uids.
    $selmkrs = array();

    if (isset($_POST['wildcard']) && ($_POST['wildcard'] == 'Yes')) {
        set_time_limit(300);
        $mkrnm = $selmkrnames[0];
        $sql = "select marker_uid from marker_synonyms where value REGEXP \"$mkrnm\" UNION
          select marker_uid from markers where marker_name REGEXP \"$mkrnm\"";
        $r = mysqli_query($mysqli, $sql);
        if (mysqli_num_rows($r) == 0) {
            echo "<font color=red>\"$mkrnm\" not found.</font><br>";
        } else {
            while ($row = mysqli_fetch_row($r)) {
                $selmkrs[] = $row[0];
            }
        }
        $_SESSION['clicked_buttons'] = $selmkrs;
    } else {
        foreach ($selmkrnames as $mkrnm) {
            $sql = "select distinct marker_uid from marker_synonyms where value = '$mkrnm' UNION
            select marker_uid from markers where marker_name = '$mkrnm'";
            $r = mysqli_query($mysqli, $sql);
            if (mysqli_num_rows($r) == 0) {
                echo "<font color=red>\"$mkrnm\" not found.</font><br>";
            } else {
                $row = mysqli_fetch_row($r);
                // Trap case where a marker is entered twice, even as synonym, e.g. 11_0090 and 1375-2534.
                if (! in_array($row[0], $selmkrs)) {
                    array_push($selmkrs, $row[0]);
                }
            }
        }
        $clkmkrs=$_SESSION['clicked_buttons'];
        if (!isset($clkmkrs) || ! is_array($clkmkrs)) {
            $clkmkrs=array();
        }
        foreach ($selmkrs as $mkruid) {
            if (! in_array($mkruid, $clkmkrs)) {
                array_push($clkmkrs, $mkruid);
            }
        }
        $_SESSION['clicked_buttons'] = $clkmkrs;
    }

    // Get the uid of a map each of the markers is on.
    //$mapids = $_SESSION['mapids'];
    //if (!isset($mapids) || !is_array($mapids))
    //    $mapids = array();
    //foreach ($selmkrs as $mkr) {
    //    $sql = "select distinct map_uid from markers_in_maps where marker_uid = $mkr";
    //    $r = mysql_query($sql);
    //    $row = mysql_fetch_row($r);
    //    if (! in_array($row[0], $mapids))
    //        array_push($mapids, $row[0]);
    //}
    $_SESSION['mapids'] = $mapids;
    ?>
    <script type="text/javascript">
    update_side_menu();
    </script>
    <?php
}

if (isset($_POST['selMkrs']) || isset($_POST['selbyname'])) {
    $mapid = getSubmittedMapid();
    if (isset($_POST['selMkrs'])) {
        $selmkrs=$_POST['selMkrs'];
    } else {
        $selbyname = $_POST['selbyname'];
        $sql = "select m.marker_uid from markers where
        m.marker_name='" . mysql_real_escape_string($selbyname) . "'";
        $sqlr = mysql_fetch_assoc(mysql_query($sql));
        $selmkrs = array($sqlr['marker_uid']);
    }
    $mapids = $_SESSION['mapids'];
    if (!isset($mapids) || !is_array($mapids)) {
        $mapids = array();
    }
    $clkmkrs=$_SESSION['clicked_buttons'];
    if (!isset($clkmkrs) || ! is_array($clkmkrs)) {
        $clkmkrs=array();
    }
    foreach ($selmkrs as $mkruid) {
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
            array_splice($selmkrs, $mkridx, 1);
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
    if (!isset($mapids) || !is_array($mapids)) {
        $mapids = array();
    }
    reset($mapids);

    $chrlist = array();
    $markerlist = array();
    $count_markers = 0;
    foreach ($_SESSION['clicked_buttons'] as $mkruid) {
        $count_markers++;
        $mapid = current($mapids);
        next($mapids);
        $sql = "select marker_name from markers where marker_uid=$mkruid";
        $result=mysql_query($sql)
          or die(mysql_error());
        while ($row=mysql_fetch_assoc($result)) {
            $selval=$row['marker_name'];
            $selchr=$row['chromosome'];
            if (! in_array($selval, $markerlist)) {
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
    if (! isset($username) || strlen($username)<1) {
        $username="Public";
    }
    store_session_variables('clicked_buttons', $username) or die("Error: did not save\n");
    store_session_variables('mapids', $username);
}
if (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0)) {
    $count = count($_SESSION['clicked_buttons']);
    print "$count markers selected. ";
    print "<a href=genotyping/display_markers.php>Show marker information</a><br>\n";
} elseif (isset($_SESSION['geno_exps_cnt'])) {
    $count = $_SESSION['geno_exps_cnt'];
    print "$count markers selected. ";
    print "<a href=genotyping/display_markers.php>Show marker information</a><br>\n";
} else { // end of if Currently Selected
    print "None<br>";
}
?>
</div>

<div class="boxContent">
  <h3>Select markers by name</h3>
  <table><tr><td>
  <b>one or more markers</b>
  <form action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
  <textarea rows=6 name=selMarkerstring id="selMarkerstring"></textarea>
  <td>Synonyms will be translated.<br>
  <!--input type="checkbox" name="wildcard" value="Yes" onclick="javascript: update_select(this.value)">Use Wildcard.<br-->
  <p><input type=submit value="Select by name"  style=color:blue>
  </form>

  <tr><td><b>search using pattern matching</b>
  <form action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
  <input type="text" name=selMarkerstring id="selMarkerstring"><br>
   . - matches any single character<br>
   * - matches zero or more instances of preceding<br>
   ^ - matches at the beginning of value<br>
   $ - matches at the end of value<br>
  <td>Synonyms will be translated.<br>
  <input type="hidden" name="wildcard" value="Yes">
  <p><input type=submit value="Select by pattern matching"  style=color:blue>
  </tr></table>
  </form>
  </div>

  <div id="markerSel" class="boxContent" style="float: left; margin-bottom: 1.5em;">
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
$result=mysql_query("select map_name from map") or die(mysql_error());
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

<div class="boxContent" style="float: left; margin-botton: 1.5em;">
<?php
$result=mysql_query("select markerpanels_uid, name, marker_ids, comment from markerpanels");
if (mysql_num_rows($result) > 0) {
    ?>
    <h3> Preselected marker sets</h3>
    <form action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
    <table id="markeSetTab">
    <thead><tr><th>Panel</th><th>Markers</th></tr></thead>
    <tbody>
    <tr><td>
    <select name='mapset' size=10 onchange="javascript: DispMarkerSet(this.options)">
    <?php
    if (loginTest2()) {
        $row = loadUser($_SESSION['username']);
        $myid = $row['users_uid'];
        $sql = "SELECT markerpanels_uid, name FROM markerpanels where users_uid = $myid";
        $res = mysql_query($sql) or die(mysql_error());
        while ($row=mysql_fetch_assoc($res)) {
            $name = $row['name'];
            $desc = $row['comment'];
            print "<option value='$name' title='$desc'>$name</option>";
        }
        print "<option disabled>Everybody's:</option>";
    }
    $sql = "select markerpanels_uid, name, marker_ids, comment from markerpanels where users_uid is NULL";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row=mysql_fetch_assoc($res)) {
        $uid = $row['markerpanels_uid'];
        $name = $row['name'];
        $desc = $row['comment'];
        print "<option value='$name' title='$desc'>$name</option>";
    }
    ?>
    </select>
    <td id="markerSet">Choose set.
    </td></tbody></table></form>
    <?php
}
?>
</div>
<div class="boxContent" style="clear: both;"></div>
<h3>Select by genotyping platform and experiment</h3>
<form action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
<div class="boxContent" style="float: left; margin-buttom: 1.5em;">
  <table>
  <thead>
  <tr><th>Platform</th><th>Experiment
  <tbody>
  <tr><td>
  <select name='platform[]' size=10 multiple onchange="javascript: update_platform(this.options)">
<?php
$result=mysql_query("select distinct(platform.platform_uid), platform_name from platform, genotype_experiment_info where platform.platform_uid = genotype_experiment_info.platform_uid") or die(mysql_error());
while ($row=mysql_fetch_assoc($result)) {
    $uid = $row['platform_uid'];
    $val = $row['platform_name'];
    print "<option value='$uid'>$val</option>\n";
}
?>
</select>
<td id="col2">Choose platform
</table>
</form>
</div>
<div class="boxContent" style="float: left; margin-buttom: 1.5em;"></div>
<div class="boxContent" style="clear: both; float: left; width: 100%">
  <h3> Select using GBrowse</h3>
Hover over a marker and click "Select in THT" in the popup balloon.
<br><a href="/cgi-bin/gbrowse/tht">GBrowse</a><br><br>
  </div>

</div>
</div>
</div>
<script type="text/javascript" src="genotyping/marker.js"></script>
<?php require $config['root_dir'].'theme/footer.php'; ?>
