<?php
/**
 * 11jul2013 dem Add "Delete".
 * 12/14/2010 JLee  Change to use curator bootstrap
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator2.inc';
require $config['root_dir'] . 'theme/admin_header2.php';
$mysqli =  connecti();
loginTest();
$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

// If we're re-entering the script with a mapset to delete:
if (!empty($_GET['mapsetuid'])) {
    $msuid = $_GET['mapsetuid'];
    $sql = "select mapset_name from mapset where mapset_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $msuid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $msname);
        mysqli_stmt_fetch($stmt);
        echo "found mapset $msname<br>\n";
    }
    mysqli_stmt_close($stmt);

    $map_list = array();
    $sql = "select map_uid, map_name from map m, mapset ms
            where m.mapset_uid = ? 
            and m.mapset_uid = ms.mapset_uid";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $msuid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $map_uid, $map_name);
        while (mysqli_stmt_fetch($stmt)) {
            $map_list[] = $map_uid;
            $map_list_name[] = $map_name;
        }
        mysqli_stmt_close($stmt);
    }
    foreach ($map_list as $key => $map_uid) {
        $sql = "delete from markers_in_maps where map_uid = ?";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $map_uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo "deleted map $map_list_name[$key]<br>\n";
        }
    }
    $sql = "delete from map where mapset_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $msuid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    $sql = "delete from mapset where mapset_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $msuid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "deleted mapset $msname<br>\n";
    }
    $deleted = $msname;
}
?>

<style type="text/css">
  table {background: none; border-collapse: collapse}
  table td {border: 0px solid #eee !important; padding-top: 0px;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<div class='section'>
<h1>Add and Upload Map Information </h1>
<form action="curator_data/input_maps_check.php" method="post" enctype="multipart/form-data">
  <input type="hidden" id="mapsetID" name="MapsetID" value="-1">
  <table>
    <tr>
      <td>
    <p><strong>Map Set Name</strong> 
    <br><input type="textbox" name="mapset_name">
    <td>	
    <p><strong>Map Set Prefix</strong>
    <br><input type="textbox" name="mapset_prefix" size=7>
    <td>
    <p><strong>Species</strong>
    <br><input type="textbox" name="species" value="Hordeum" size=9>
      <td>
	<p><strong>Map Type</strong>
	<br><input type="textbox" name="map_type" value="Genetic" size=7>
      <td>	
	<p><strong>Map Unit</strong>
	<br><input type="textbox" name="map_unit" value="cM" size=7>
    </tr>
  </table>
  <strong>Description</strong> 
  <br><textarea name="comments" cols="40" rows="6" > </textarea>
  <p><strong>Map File:</strong> <input id="file" type="file" name="file" size="60%" > &nbsp;&nbsp;&nbsp;   <a href="curator_data/examples/mapupload_example.txt">Example Map File</a>
  <p><input type="submit" value="Upload Map File" >
</div>


  <!-- Delete a map. -->
  <div class='section'>
  <h1>Delete a Map</h1>

<?php if (!empty($deleted)) echo "Mapset <b>'$deleted'</b> deleted.<p>"; ?>

      <select onchange="pickmap(this.options[this.selectedIndex].text, this.options[this.selectedIndex].value)">
	<option value=''>Choose from below...</option>
<?php
  $sql = "select mapset_name as name, mapset_uid as uid from mapset order by mapset_name";
  $r = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) . "<br>$sql");
  while($row = mysqli_fetch_assoc($r)) {
    $name = $row['name'];
    $uid = $row['uid'];
    echo "<option value='$uid'>$name</option>\n";
  } 
?>
      </select>
</div>

<script type=text/javascript>
	function pickmap(name, uid) {
	    var r=confirm('Really delete mapset "'+name+'"?');
	    if (r==true) {
	      window.location.search = 'mapsetuid='+uid;
	    }
	}

</script>

<?php


$footer_div = 1;
include $config['root_dir'].'theme/footer.php';
