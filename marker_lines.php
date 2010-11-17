<?php
session_start();
require_once('config.php');
include($config['root_dir'].'includes/bootstrap.inc');
connect();
include($config['root_dir'].'theme/normal_header.php');

function marker_lines($marker_uid) {
  // finds all lines for a given marker
  $sql = "select distinctrow
line_records.line_record_uid, line_records.line_record_name
from genotyping_data
inner join tht_base using(tht_base_uid)
inner join line_records using(line_record_uid)
where genotyping_data.marker_uid = $marker_uid";
  $sqlr = mysql_query($sql) or die(mysql_error());
  $rv = array();
  while ($row = mysql_fetch_assoc($sqlr))
    array_push($rv, $row);
  return $rv;
}
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <?php $marker_uid = intval($_GET['id']); ?>
<h1>Lines for Marker <?php echo $marker_uid; ?></h1>
  <ol>
  <?php
  foreach (marker_lines($marker_uid) as $line) {
    extract($line);
    echo "<li>
<a href='./view.php?table=line_records&uid=$line_record_uid'>
$line_record_name</a></li>\n";
  }
  ?>
  </ol>
  </div>
</div>

<?php 
$footer_div = 1;
include($config['root_dir'].'theme/footer.php');?>
