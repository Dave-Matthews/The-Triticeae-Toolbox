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

  /**
   * Using the class's constructor to decide which action to perform
   * @param string $function action to perform
   */
  public function __construct($function = null) {
    switch($function) {
    case 'Save':
      $this->type_MapSet_Display();
      break;
    case 'Markers':
      $this->typeMapMarker(); /* this is called by javascript using ajax because it can be slow */
      break;
    default:
      $this->typeMapSet(); /* initial case */
      break;
    }
  }

  /**
   * The wrapper action for the typeMapset . Handles outputting the header
   * and footer and calls the first real action of the typeMapset .
   */
  private function typeMapSet()
  {
    global $config;
    include($config['root_dir'].'theme/normal_header.php');

    echo "<h2>Map Sets</h2>";
    echo "<div id=\"step1\">";
    $this->type_MapSet_Display();
    echo "</div>";
    echo "<div id=\"step2\">";
    echo "<img id=\"spinner\" src=\"images/ajax-loader.gif\" style=\"display:none;\" />";
    echo "</div>";
    echo "<div id=\"step3\"></div>";
    if (isset($_SESSION['selected_lines']) or isset($_SESSION['clicked_buttons'])) {
    ?>
    <script type="text/javascript">
      window.onload = load_markersInMap();
    </script>
    <?php
    }
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }

  /**
   * Display a table of available maps
   */
  private function type_MapSet_Display()
  {
      if (isset($_GET['map'])) {
          $map = $_GET['map'];
          $_SESSION['selected_map'] = $map;
          echo "Map selection saved.<br><br>\n";
      }
  ?>
  <style type="text/css">
         th {background: #5B53A6 !important; color: white !important; }
         table {background: none; border-collapse: collapse; }
         td {border: 1px solid #eee !important;}
         h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
  </style>
  <script type="text/javascript" src="maps/select_map.js">
  </script>
  <form name="myForm" action="maps/select_map.php">
  <?php
    if (isset($_SESSION['selected_map'])) {
      $selected_map = $_SESSION['selected_map'];
    }
    $sql = "select count(*) as countm, mapset_name, mapset.mapset_uid as mapuid, mapset.comments as mapcmt from mapset, markers, markers_in_maps as mim, map
      WHERE mim.marker_uid = markers.marker_uid
      AND mim.map_uid = map.map_uid
      AND map.mapset_uid = mapset.mapset_uid
      GROUP BY mapset.mapset_uid";
      echo "This table lists the total markers in each map.\n";
      echo "If a marker is not in the the selected map set then it will be assigned to chromosome 0.<br><br>\n";
    $res = mysql_query($sql) or die (mysql_error());
    echo "<table>\n";
    echo "<tr><td>select<td>markers<br>(total)<td>map name<td>comment (mouse over item for complete text)\n";
    while ($row = mysql_fetch_assoc($res)) {
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
      echo "<tr><td><input type=\"radio\" name=\"map\" value=\"$uid\" $checked onchange=\"javascript: save_map(this.value)\"><td>$count<td>$val<td><article title=\"$comment\">$comm</article>\n";
    }
    echo "</table>";
    #echo "<input type=\"submit\" name=\"function\" value=\"Save\">";
    #echo "<input type=\"button\" value=\"Save\" onclick=\"javascript:save_map()\">";
    echo "</form>";
  }

  /**
   * called through ajax to display how many of the selected markers are in each map set
   */
  private function typeMapMarker()
  {
    if (isset($_SESSION['clicked_buttons'])) {
      $markers = $_SESSION['clicked_buttons'];
      $marker_str = implode(',',$markers);
      $num_mark = count($markers);
      $msg = "This table lists the portion of the $num_mark markers included in each map";
    } elseif (isset($_SESSION['selected_lines'])) {
      $selected_lines = $_SESSION['selected_lines'];
      $num_line = count($selected_lines);
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
      $msg = "There  are $num_mark markers that have genotype data for the selected $num_line lines.<br>This table lists the portion of markers included in each map.<br>Selecting the map with the largest count will give the best coverage.<br><br>";
    } else {
      die("Error - must select lines or markers<br>\n");
    }
    $found = 0;
    $sql = "select count(*) as countm, mapset_name, mapset.mapset_uid as mapuid, mapset.comments as mapcmt from mapset, markers, markers_in_maps as mim, map
       WHERE mim.marker_uid = markers.marker_uid
       AND mim.map_uid = map.map_uid
       AND map.mapset_uid = mapset.mapset_uid
       AND markers.marker_uid IN ($marker_str) 
       GROUP BY mapset.mapset_uid";
    $res = mysql_query($sql) or die (mysql_error());
    while ($row = mysql_fetch_assoc($res)) {
      if ($found == 0 ) {
        echo "<br><br>$msg\n";
        echo "<table><tr><td>markers<br>(in selected lines)<td>map name\n";
        $found = 1;
      }
      $count = $row["countm"];
      $val = $row["mapset_name"];
      $uid = $row["mapuid"];
      echo "<tr><td>$count<td>$val\n";
    }
    echo "</table>";
  }

  /**
   * save map in session variable
   */
  private function typeMapSave() {
    $map = $_GET['map'];
    $_SESSION['selected_map'] = $map;
  }

}
