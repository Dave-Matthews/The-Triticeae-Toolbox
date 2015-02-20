<?php

require 'config.php';
/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();

include $config['root_dir'] . 'theme/admin_header.php';
/*******************************/
?>

<div id="primaryContentContainer">
	<div id="primaryContent">

	<h2>Alleles for all lines</h2>

<?php
if (isset($_GET['marker']) && ($_GET['marker'] != "")) {
    $sql = "select marker_name from markers where marker_uid = '".$_GET['marker']."'";
    $stmt = mysqli_prepare($mysqli, "SELECT marker_name from markers where marker_uid = ?");
    mysqli_stmt_bind_param($stmt, "i", $_GET['marker']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $markername);
    mysqli_stmt_fetch($stmt);
} elseif (isset($_GET['markername']) && ($_GET['markername'] != "")) {
    $markername = $_GET['markername'];
}

echo "<h3>Marker $markername</h3>";

if (isset($_GET['sortby']) && isset($_GET['sorttype'])) {
    $orderby = $_GET['sortby'] . " " . $_GET['sorttype'];
    showLineForMarker($markername, $orderby);
} else {
    showLineForMarker($markername);
}
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


<?php include $config['root_dir'] . 'theme/footer.php';
