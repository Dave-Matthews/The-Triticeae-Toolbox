<div id="select-map" title="Select Genetic Map">
<form name="myForm" action="maps/select_map.php">
<?php
if (isset($_SESSION['selected_map'])) {
    $selected_map = $_SESSION['selected_map'];
}

$sql = "select count(*) as countm, mapset_name, mapset.mapset_uid as mapuid, mapset.comments as mapcmt
      from mapset, markers_in_maps as mim, map
      WHERE mim.map_uid = map.map_uid
      AND map.mapset_uid = mapset.mapset_uid
      GROUP BY mapset.mapset_uid";
echo "This table lists the total markers in each map.\n";
echo "If a marker is not in the the selected map set then it will be assigned to chromosome 0.<br><br>\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
echo "<table>\n";
echo "<tr><td>select<td>markers<br>(total)<td>map name<td>comment (mouse over item for complete text)\n";
while ($row = mysqli_fetch_assoc($res)) {
    $count = $row["countm"];
    $val = $row["mapset_name"];
    $uid = $row["mapuid"];
    $comment = $row["mapcmt"];
    $comm = substr($comment, 0, 100);
    if ($uid == $selected_map) {
        $checked = "checked=\"checked\"";
    } else {
        $checked = "";
    }
    echo "<tr><td>
          <input type=\"radio\" name=\"map\" value=\"$uid\" $checked onchange=\"javascript: save_map(this.value)\">
          <td>$count<td>$val<td><article title=\"$comment\">$comm</article>\n";
}
echo "</table>";
echo "</form>";
echo "<div id=\"select-map2\">";
echo "</div>";
echo "</div>";
