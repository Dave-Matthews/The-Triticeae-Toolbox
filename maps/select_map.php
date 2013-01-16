<?php
/**
 * Display Map information and save selection in session variable
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/maps/select_map.php
 * 
 */

require_once('config.php');
include_once($config['root_dir'].'includes/bootstrap.inc');
connect();

new Maps($_GET['function']);

/** Using a PHP class to implement the "Select Map" feature
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/maps/select_map.php
 **/
class Maps {

  public function __construct($function = null) {
    switch($function) {
    case 'Save':
      $this->typeMapSave();
      $this->typeMapSet();
      break;
    default:
      $this->typeMapSet(); /* initial case */
      break;
    }
  }

  // The wrapper action for the typeMapset . Handles outputting the header
  // and footer and calls the first real action of the typeMapset .
  private function typeMapSet()
  {
    global $config;
    include($config['root_dir'].'theme/normal_header.php');

    echo "<h2>Map Sets</h2>";
    $this->type_MapSet_Display();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }

  private function type_MapSet_Display()
  {
  ?>
  <style type="text/css">
         th {background: #5B53A6 !important; color: white !important; }
         table {background: none; border-collapse: collapse}
         td {border: 1px solid #eee !important;}
         h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
  </style>
  <form action="maps/select_map.php">
  <?php
    if (isset($_SESSION['selected_map'])) {
      $selected_map = $_SESSION['selected_map'];
    } else {
      $selected_map = 1;
    }
    if (isset($_SESSION['selected_lines'])) {
      $selected_lines = $_SESSION['selected_lines'];
      $selected_lines = implode(",",$selected_lines);
      $sql_exp = "SELECT DISTINCT marker_uid
           FROM allele_cache
           WHERE
           allele_cache.line_record_uid in ($selected_lines)";
           $res = mysql_query($sql_exp) or die(mysql_error() . "<br>" . $sql_exp);
           if (mysql_num_rows($res)>0) {
             while ($row = mysql_fetch_array($res)){
               $uid = $row["marker_uid"];
               $markers[] = $uid;
             }
            }
           $marker_str = implode(',',$markers);
           $num_mark = count($markers);
       $sql = "select count(*) as countm, mapset_name, mapset.mapset_uid as mapuid, mapset.comments as mapcmt from mapset, markers, markers_in_maps as mim, map
       WHERE mim.marker_uid = markers.marker_uid
       AND mim.map_uid = map.map_uid
       AND map.mapset_uid = mapset.mapset_uid
       AND markers.marker_uid IN ($marker_str) 
       GROUP BY mapset.mapset_uid"; 
       echo "Markers in map out of $num_mark markers in selected lines<br>\n";
    } else {
       $sql = "select count(*) as countm, mapset_name, mapset.mapset_uid as mapuid, mapset.comments as mapcmt from mapset, markers, markers_in_maps as mim, map
       WHERE mim.marker_uid = markers.marker_uid
       AND mim.map_uid = map.map_uid
       AND map.mapset_uid = mapset.mapset_uid
       GROUP BY mapset.mapset_uid";
       echo "Markers in map<br>\n";
    }
    $res = mysql_query($sql) or die (mysql_error());
    echo "<table>\n";
    echo "<tr><td>select<td>count<td>name<td>comment\n";
    while ($row = mysql_fetch_assoc($res)) {
      $count = $row["countm"];
      $val = $row["mapset_name"];
      $uid = $row["mapuid"];
      $comment = $row["mapcmt"];
      if ($uid == $selected_map) {
        $checked = "checked=\"checked\"";
      } else {
        $checked = "";
      }
      echo "<tr><td><input type=\"radio\" name=\"map\" value=\"$uid\" $checked><td>$count<td>$val<td nowrap>$comment\n";
    }
    echo "</table>";
    echo "<input type=\"submit\" name=\"function\" value=\"Save\">";
    echo "</form>";
  }

  private function typeMapSave() {
    $map = $_GET['map'];
    $_SESSION['selected_map'] = $map;
  }

}
