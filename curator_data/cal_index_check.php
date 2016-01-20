<?php
/**
 * Canopy Spectral Reflectance
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/cal_index_check.php
 *
 */

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysql = connect();
$mysqli = connecti();
?>
<img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
<?php
if (isset($_POST['trial']) && !empty($_POST['trial'])) {
    $trial = $_POST['trial'];
    $sql = "select raw_file_name, trial_code from csr_measurement, experiments
       where experiments.experiment_uid = csr_measurement.experiment_uid and measurement_uid = $trial";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $filename3 = $row[0];
        $trial_code = $row[1];
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
  $formula1 = $_POST['formula1'];
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
  if (isset($_POST['W3']) && !empty($_POST['W3'])) {
      $w3 = $_POST['W3'];
  } elseif (preg_match("/W3/", $index)) {
      die("must specify W3");
  } else {
      $w3 = "NA";
  }
  $zoom = $_POST['xrange'];
  
  $dir = $config['root_dir'] . "raw/phenotype";
  $dir2 = $config['root_dir'] . "raw/phenotype/CSR";
  $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
  mkdir("/tmp/tht/$unique_str");
  $filename1 = "gbe-input.txt";
  $filename2 = "process_error.txt";
  $raw_file = $config['root_dir']."raw/phenotype/csr_data_$unique_str" . ".txt";
  $filename4 = "csr_data_$unique_str" . ".txt";
  $filename5 = "gbe-formula.txt";
  $filename6 = "csr-plot1.png";
  $filename7 = "csr-plot2.png";
  $h = fopen("/tmp/tht/$unique_str/$filename1", "w");
  $png1 = "png(\"/tmp/tht/$unique_str/$filename6\", width=800, height=300)\n";
  $png2 = "png(\"/tmp/tht/$unique_str/$filename7\", width=800, height=300)\n";
  $png3 = "dev.set(2)\n";
  if (file_exists($dir/$filename3)) {
      $cmd1 = "csrData <- read.table(\"$dir/$filename3\", header=FALSE, sep=\"\\t\", skip=5, stringsAsFactors=FALSE)\n";
      $cmd2 = "pltData <- read.table(\"$dir/$filename3\", header=FALSE, sep=\"\\t\", skip=1, nrows=1)\n";
  } elseif (file_exists($dir2/$filename3)) {
      $cmd1 = "csrData <- read.table(\"$dir2/$filename3\", header=FALSE, sep=\"\\t\", skip=5, stringsAsFactors=FALSE)\n";
      $cmd2 = "pltData <- read.table(\"$dir2/$filename3\", header=FALSE, sep=\"\\t\", skip=1, nrows=1)\n";
  } else {
      echo "Error: file $filename3 not found\n";
  }
  $cmd2a = "if (pltData[1,1] != \"Plot\") {\n";
  $cmd2b = "  cat(\"Error - bad file format in $filename3\")\n";
  $cmd2c = "  stop(\"Error - bad file format in $filename3\")\n";
  $cmd2d = "}\n";
  $cmd3 = "file_out <- \"$raw_file\"\n";
  $cmd4 = "file_for <- \"$filename5\"\n";
  $cmd5 = "setwd(\"/tmp/tht/$unique_str\")\n";
  $cmd6 = "W1wav <- $w1\n";
  $cmd7 = "W2wav <- $w2\n";
  $cmd8 = "W3wav <- $w3\n";
  $cmd9 = "smooth <- $smooth\n";
  $cmd10 = "zoom <- \"$zoom\"\n";
  $cmd11 = "trial_code <- \"$trial_code\"\n";
  $cmd12 = "formula1 <- \"$formula1\"\n";
  fwrite($h, $png1); fwrite($h, $png2); fwrite($h, $png3);
  fwrite($h, $cmd1);
  fwrite($h, $cmd2); fwrite($h, $cmd2a); fwrite($h, $cmd2b); fwrite($h, $cmd2c); fwrite($h, $cmd2d);
  fwrite($h, $cmd3);
  fwrite($h, $cmd4);
  fwrite($h, $cmd5);
  fwrite($h, $cmd6);
  fwrite($h, $cmd7);
  fwrite($h, $cmd8);
  fwrite($h, $cmd9);
  fwrite($h, $cmd10);
  fwrite($h, $cmd11);
  fwrite($h, $cmd12);
  fclose($h);
  $h = fopen("/tmp/tht/$unique_str/$filename5", "w");
  fwrite($h, "calIndex <- function(data, idx1, idx2, idx3) {\n");
  fwrite($h, "W1 <- data[idx1]\n");
  fwrite($h, "W2 <- data[idx2]\n");
  fwrite($h, "W3 <- data[idx3]\n");
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
      print "<img src=\"/tmp/tht/$unique_str/$filename6\" /><br>";
  }
  print "<img src=\"/tmp/tht/$unique_str/$filename7\" /><br>";
  //if user is not logged in give them login link
  if (file_exists("$raw_file")) {
      print "<h3>3. Save Results</h3>\n";
      print "<table>";
      $url = "raw/phenotype/$filename4";
      print "<tr><td><input type=\"button\" value=\"Download\" onclick=\"javascript: start_download('$url')\"><td>";
      if (isset($_SESSION['username'])) {
          if (authenticate(array(USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR))) {
              print "<tr><td><form action=\"curator_data/input_experiments_plot_check.php\" method=post>\n";
              print "<input type=\"hidden\" name=\"filename0\" value=\"$filename4\">\n";
              print "<input type=submit value=\"Upload\"><td>Load the index as a trait CSR_$formula1";
              print "</form>";
          } 
          print "</table><br>\n";
          print "<tr><td>To load index on production T3 website as a trait<br>\n";
          print "1. Download the file<br>\n";
          print "2. Use the <a href=curator_data/queue.php>Data Submission form</a> to send the file to the curator\n";
    } else {
        print "</table><br>\n";
        print "<a href=login.php>Login</a> to save data as a trait or submit data to curator<br>\n";
    }
  } else {
      echo "Error: calculation of index failed<br>\n";
  }

?>
