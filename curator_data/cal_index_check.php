<?php
/**
 * Canopy Spectral Reflectance
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/curator_data/cal_index_check.php
 * 
 */
  require_once 'config.php';
  require $config['root_dir'].'includes/bootstrap.inc';
  connect();
  ?>
  <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
  <?php
  if (isset($_POST['trial']) && !empty($_POST['trial'])) {
    $trial = $_POST['trial'];
    $sql = "select raw_file_name from csr_measurement where measurement_uid = $trial";
    $res = mysql_query($sql) or die(mysql_error());
    if ($row = mysql_fetch_array($res)) {
      $filename3 = $row[0];
    } else {
      die("trial $trial not found<br>\n");
    }
  } else {
    die("no trial found");
  }
  if (isset($_POST['smooth']) && !empty($_POST['smooth'])) {
    $smooth = $_POST['smooth'];
  } else {
    $smooth = 0;
    echo "no smoothing<br>\n";
  }
  if (isset($_POST['formula2']) && !empty($_POST['formula2'])) {
    $index = $_POST['formula2'];
    if (preg_match("/system/", $index)) {
    	die("<font color=red>Error: Illegal formula</font>");
    } elseif (preg_match("/shell/", $index)) {
    	die("<font color=red>Error: Illegal formula</font>");
    } elseif (preg_match("/[{}]/", $index)) {
    	die("<font color=red>Error: Illegal formula</font>");
    } elseif (preg_match("/write/", $index)) {
    	die("<font color=red>Error: Illegal formula</font>");
    } elseif (preg_match("/read/", $index)) {
    	die("<font color=red>Error: Illegal formula</font>");
    }
    echo "formula = $index<br>\n";
  } else {
    die("no formula specified<br>\n");
  }
  if (isset($_POST['W1']) && !empty($_POST['W1'])) {
    $w1 = $_POST['W1'];
  } else {
    die("must specify W1");
  }
  if (isset($_POST['W2']) && !empty($_POST['W2'])) {
    $w2 = $_POST['W2'];
  } else {
    die("must specify W2");
  }
  
  $dir = $config['root_dir'] . "raw/phenotype";
  $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
  mkdir("/tmp/tht/$unique_str");
  $filename1 = "gbe-input.txt";
  $filename2 = "process_error.txt";
  $filename4 = "gbe-output.txt";
  $filename5 = "gbe-formula.txt";
  $filename6 = "csr-plot1.png";
  $filename7 = "csr-plot2.png";
  $h = fopen("/tmp/tht/$unique_str/$filename1","w");
  $png1 = "png(\"/tmp/tht/$unique_str/$filename6\", width=800, height=300)\n";
  $png2 = "png(\"/tmp/tht/$unique_str/$filename7\", width=800, height=300)\n";
  $png3 = "dev.set(2)\n";
  $cmd1 = "csrData <- read.table(\"$dir/$filename3\", header=FALSE, sep=\"\\t\", skip=5, stringsAsFactors=FALSE)\n";
  $cmd2 = "pltData <- read.table(\"$dir/$filename3\", header=FALSE, sep=\"\\t\", skip=1, nrows=1)\n";
  $cmd2a = "if (pltData[1,1] != \"Plot\") {\n";
  $cmd2b = "  cat(\"Error - bad file format in $filename3\")\n";
  $cmd2c = "  stop(\"Error - bad file format in $filename3\")\n";
  $cmd2d = "}\n";
  $cmd3 = "file_out <- \"$filename4\"\n";
  $cmd4 = "file_for <- \"$filename5\"\n";
  $cmd5 = "setwd(\"/tmp/tht/$unique_str\")\n";
  $cmd6 = "W1wav <- $w1\n";
  $cmd7 = "W2wav <- $w2\n";
  $cmd8 = "smooth <- $smooth\n";
  fwrite($h, $png1); fwrite($h, $png2); fwrite($h, $png3);
  fwrite($h, $cmd1);
  fwrite($h, $cmd2); fwrite($h, $cmd2a); fwrite($h, $cmd2b); fwrite($h, $cmd2c); fwrite($h, $cmd2d);
  fwrite($h, $cmd3);
  fwrite($h, $cmd4);
  fwrite($h, $cmd5);
  fwrite($h, $cmd6);
  fwrite($h, $cmd7);
  fwrite($h, $cmd8);
  fclose($h);
  $h = fopen("/tmp/tht/$unique_str/$filename5","w");
  fwrite($h, "calIndex <- function(data, idx1, idx2) {\n");
  #if ($smooth == 0) {
    fwrite($h, "W1 <- data[idx1]\n");
  /*} elseif ($smooth == 5) {
  	fwrite($h, "idx1a <- idx1 - 5\n");
  	fwrite($h, "idx1b <- idx1 + 5\n");
  	fwrite($h, "W1 <- (sum(data[idx1a:idx1b]) / 11)\n");
  } elseif ($smooth == 10) {
  	fwrite($h, "idx1a <- idx1 - 10\n");
  	fwrite($h, "idx1b <- idx1 + 10\n");
    fwrite($h, "W1 <- (sum(data[idx1a:idx1b]) / 21)\n");
  } */
  #if ($smooth == 0) {
    fwrite($h, "W2 <- data[idx2]\n");
  /*} elseif ($smooth == 5) {
  	fwrite($h, "idx2a <- idx2 - 5\n");
  	fwrite($h, "idx2b <- idx2 + 5\n");
    fwrite($h, "W2 <- (sum(data[idx2a:idx2b]) / 11)\n");
  } elseif ($smooth == 10) {
  	fwrite($h, "idx2a <- idx2 - 10\n");
  	fwrite($h, "idx2b <- idx2 + 10\n");
  	fwrite($h, "W2 <- (sum(data[idx2a:idx2b]) / 21)\n");
  }
  */
  fwrite($h, "value <- $index\n");
  fwrite($h, "value\n");
  fwrite($h, "}\n");
  fclose($h);
  exec("cat /tmp/tht/$unique_str/$filename1 ../R/csr-index.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$filename2");
  if (file_exists("/tmp/tht/$unique_str/$filename2")) {
    $h = fopen("/tmp/tht/$unique_str/$filename2", "r");
    while ($line=fgets($h)) {
      echo "$line<br>\n";
    }
    fclose($h);
    $h = fopen("/tmp/tht/$unique_str/$filename2", "r");
    while ($line=fgets($h)) {
      echo "$line<br>\n";
    }
    fclose($h);
  }
  if (file_exists("/tmp/tht/$unique_str/$filename6")) {
    print "Plot of CSR Data File, x and y axes are scaled according to W1 and W2 parameters<br>";
    print "<img src=\"/tmp/tht/$unique_str/$filename6\" /><br>";
  }
  print "<img src=\"/tmp/tht/$unique_str/$filename7\" /><br>";
  if (file_exists("/tmp/tht/$unique_str/$filename4")) {
    print "<a href=/tmp/tht/$unique_str/$filename4 target=\"_blank\"type=\"text/csv\">results file of calculated index<br>\n";
  } else {
    echo "Error: calculation of index failed<br>\n";
  }

?>
