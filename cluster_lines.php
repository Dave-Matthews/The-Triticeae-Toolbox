<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header.php';
$mysqli = connecti();

// Use the incoming value of $time instead of a new one.  Does it work?
if (isset($_GET['time'])) {
    $time = intval($_GET['time']);
} else {
    $time = date("U");
}

// If we entered the script having picked a cluster in cluster_show.php,
// load them into $_SESSION['selected_lines'].
if (isset($_GET['mycluster'])) {
    $mycluster = $_GET['mycluster'];
    $where_in = "";
    $clustertable = file("/tmp/tht/clustertable.txt".$time);
    unlink("/tmp/tht/clustertable.txt".$time);
    $clustertable = preg_replace("/\n/", "", $clustertable);
    // Remove first line, "x".
    array_shift($clustertable);
    for ($i=0; $i<count($clustertable); $i++) {
        for ($j=0; $j<count($mycluster); $j++) {
            $line = explode("\t", $clustertable[$i]);
            if ($line[1] == $mycluster[$j]) {
                // Build query for line_record_uids for these names.
                $where_in .= "'".$line[0]."',";
            }
        }
    }
    $where_in = trim($where_in, ",");
    $query = "select line_record_uid, line_record_name 
       from line_records where line_record_name in (".$where_in.")
       order by line_record_name";
    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    $_SESSION['selected_lines'] = array();
    while ($row = mysqli_fetch_row($result)) {
        array_push($_SESSION['selected_lines'], $row[0]);
    }
}

// If only a few lines are selected, reduce the suggested number of clusters.
$clusters = 5;
if (isset($_SESSION['selected_lines'])) {
  $linecount = count($_SESSION['selected_lines']);
  $clusters = min($clusters, $linecount - 1);
 }

?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Cluster Lines by Genotype</h1>
  <div class="section">

  <p>  This R program will cluster the 
  <a href="<?php echo $config['base_url']; ?>pedigree/line_properties.php">
  <font color=blue>currently selected lines</font></a> according to their 
  alleles for all markers, using "pam" (Partitioning Around Medoids).  

  <p>The results will be displayed as a two-dimensional PCA plot with
  the clusters color-coded.  If you enter some line names below, the legend will indicate
  which cluster they fall into.  

  <p>When you have examined the results you can select the clusters you want to work with.

  <form action="cluster_show.php">
  <p>How many clusters should pam divide the lines into?  &nbsp;&nbsp;
  <input type=text name="clusters" value=<?php echo $clusters ?> size="1">  (Maximum 8.)
  <p>Lines to label in the legend:<br>
  <textarea name="labels" rows=4 cols=12></textarea>
  E.g. MERIT, FEG148-16, ND24205, VA07B-54
  <input type='hidden' name='time' value=<?php echo $time ?> >

  <script type="text/javascript" src="cluster.js"></script>
  <?php

        $min_maf = 5;
        $max_missing = 10;
        $max_miss_line = 10;
        $arg = "$time,$linecount";
        ?>
        <p>Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mmm" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
          <input type="button" value="Filter Lines and Markers" onclick="javascript:filter_lines(<?php echo $arg; ?>);"/>
        <div id='filter'></div>
        <div id='ajaxresult'></div>
        <script type="text/javascript">
        if ( window.addEventListener ) {
               window.addEventListener( "load", filter_lines(<?php echo $arg ?>), false );
        } else if ( window.attachEvent ) {
               window.attachEvent( "onload", filter_lines);
        } else if ( window.onload ) {
               window.onload = filter_lines(<?php echo $arg ?>);
        }
        </script>
<?php
echo "</div></div></div>";
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
