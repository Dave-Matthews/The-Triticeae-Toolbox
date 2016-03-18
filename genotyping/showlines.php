<?php

require 'config.php';
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();

require $config['root_dir'] . 'theme/admin_header.php';
/*******************************/
?>

<div id="primaryContentContainer">
	<div id="primaryContent">

	<h2>Alleles for all lines</h2>

<?php
if (isset($_GET['marker']) && ($_GET['marker'] != "")) {
    $marker_uid = $_GET['marker'];
    $sql = "select marker_name from markers where marker_uid = $marker_uid";
    $stmt = mysqli_prepare($mysqli, "SELECT marker_name from markers where marker_uid = ?");
    mysqli_stmt_bind_param($stmt, "i", $marker_uid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $markername);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
} elseif (isset($_GET['markername']) && ($_GET['markername'] != "")) {
    $markername = $_GET['markername'];
}

echo "<h3>Marker $markername</h3>";

/* check for blind sql injection */
if (preg_match("/[^0-9,]/", $marker_uid)) {
    echo "Invalid query\n";
    die();
}
if (isset($_GET['sortby']) && isset($_GET['sorttype'])) {
    $sortby = $_GET['sortby'];
    $sorttype = $_GET['sorttype'];
    if (($sortby != "line_record_name") && ($sortby != "alleles") && ($sortby != "trial_code")) {
        echo "Error: invalid selection\n";
        return;
    }
    if (($sorttype != "DESC") && ($sorttype != "ASC")) {
        echo "Error: invalid selection\n";
        return;
    }
    $orderby = $_GET['sortby'] . " " . $_GET['sorttype'];
    showLineForMarker($marker_uid, $orderby);
} elseif (isset($_GET['marker'])) {
    showLineForMarker($marker_uid);
} else {
    echo "Error: no marker selected\n";
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

<?php
mysqli_close($mysqli);
require $config['root_dir'] . 'theme/footer.php';
