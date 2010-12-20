<?php

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();

include($config['root_dir'] . 'theme/admin_header.php');
/*******************************/
?>

<div id="primaryContentContainer">
	<div id="primaryContent">

	<h2>Alleles for all lines</h2>

<?php
  if(isset($_GET['marker']) && ($_GET['marker'] != "")) {
  $sql = "select marker_name from markers where marker_uid = '".$_GET['marker']."'";
  $res = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($res);
  $markername = $row[0];
  }
elseif(isset($_GET['markername']) && ($_GET['markername'] != "")) {
  $markername = $_GET['markername'];
}

echo "<h3>Marker $markername</h3>";

if(isset($_GET['sortby']) && isset($_GET['sorttype'])) {
  $orderby = $_GET['sortby'] . " " . $_GET['sorttype'];
  showLineForMarker($markername, $orderby);
 }
 else
   showLineForMarker($markername);
?>

<div class="boxContent">


   <form action="<?php echo $config['base_url']; ?>genotyping/showlines.php" method="get">
   <p><strong>Marker: </strong>
   <input type="text" name="markername" value="" />&nbsp;&nbsp;&nbsp; Example: 12_11047<br>
<input type="submit" value="Get Data" />
</form>


			</div>
	</div>
</div>
</div>


<?php include($config['root_dir'] . 'theme/footer.php'); ?>
