<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
require $config['root_dir'].'theme/admin_header.php';
require_once $config['root_dir'] . 'includes/email.inc';
connect();
?>

<style type=text/css>
  input {vertical-align: middle; margin: 0px;}
  ul {margin-left: 0; padding-left: 1.5em}
  ul ul ul {list-style-type: disc}
</style>

  <h1>Data Submission</h1>
  <div class="section">
  <p>

<?php 
if (!empty($_POST['dtype']) OR !empty($_FILES)) {
    // The Upload button was clicked. Handle user's submission.
    $useremail = $_SESSION['username'];
    $row = loadUser($_SESSION['username']);
    $username = $row['name'];
    $date = date('dMY');
    $dir= $config['root_dir']."curator_data/uploads/".str_replace(' ', '_', $username)."_".$date."/";
    umask(0);
    if (!file_exists($dir) || !is_dir($dir)) {
        mkdir($dir, 0777) or die("<br>Error: Can not create directory $dir\n");
    }
    if ($_FILES['file']['name'] == "") {
        error(1, "No File Uploaded");
        print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    } else {
        $uploadfile=$_FILES['file']['name'];
        $error_found = 0;
        if (preg_match("/zip/", $uploadfile)) {
            //echo "found zip file $uploadfile<br>\n";
            if ($zip = zip_open($dir.$uploadfile)) {
                while ($entry = zip_read($zip)) {
                    $name = zip_entry_name($entry);
                    if (preg_match("/\s/", $name)) {
                        $error_found = 1;
                        echo "Error: Illegal file name $name<br>\n";
                        echo "Please remove spaces from file names in zip file<br>\n";
                    }
                }
                zip_close($zip);
            } else {
                echo "$dir.$uploadfile not found<br>\n";
            }
        }
        if ($error_found) {
            error(1, "No File Uploaded");
            print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
        } else if (move_uploaded_file($_FILES['file']['tmp_name'], $dir.$uploadfile)) {
            // Successful upload.
            echo "Thank you for submitting file \"<b>$uploadfile</b>\"!<br>The curators have been notified.<p>";
            print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
            // Save info in table input_file_log.
            $sql = "insert into input_file_log (file_name, users_name) values ('$uploadfile', '$username')";
            mysql_query($sql) or die(mysql_error());
            // Send email to curator.
            $dt = $_POST['dtype'];
            $dtype = array(lines => "germplasm lines",
                pannot => "phenotype experiment annotation",
                presult => "phenotype results",
                gannot => "genotype experiment annotation",
                gresult => "genotype results",
                "" => unspecified);
      $comments = str_replace('\r\n', "\n", $_POST['comments']);
      $tst = $_POST['tested'];
      $tested = array('DOES', 'does NOT');
      $host = $_SERVER['SERVER_NAME'];
      $private = $_POST['private'];
      $mesg = "$username, $useremail, has submitted a data file.
\nData type: $dtype[$dt]
Location: $host
Directory: $dir
Filename: $uploadfile
Comments: 
$comments
\nThis file $tested[$tst] load successfully in the Sandbox.\n";
      if ($private == 'on')
	$mesg .= "This user wishes this data to be PRIVATE to the project.\n";
      //print_h($mesg);
      send_email(setting('capmail'), 'Data submitted to T3', $mesg);
    } else {
      error(1, "File not stored.");
      print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    }
  }
} else {
// Nothing submitted yet.
// Require that the user be signed in.
$user = $_SESSION['username'];
if (empty($user)) {
  echo "Please sign in before sending data files to the curator
        for loading into the production database.<br>
        <button type=submit onClick=\"location.href='login.php'\">Sign in</button>";
} else if ($user == "t3user@graingenes.org") {
  echo "Please sign out and login as yourself instead of the public 't3user'.<br>
        <button type=submit onClick=\"location.href='logout.php'\">Sign out</button>";
} else {
  echo "Please submit a data file for the curator to load
        into the production database. File names should not contain spaces.";
  ?>

<h3>Data Type</h3>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
  <ul>
    <li><input type=radio name=dtype value=lines> <b>Germplasm lines</b>
    <li><b>Phenotyping</b>
      <ul>
  	<li><input type=radio name=dtype value=pannot> Experiment annotation
  	<li><input type=radio name=dtype value=presult> Results
      </ul>
    <li><b>Genotyping</b>
      <ul>
  	<li><input type=radio name=dtype value=gannot> Experiment annotation
  	<li><input type=radio name=dtype value=gresult> Results
      </ul>
  </ul>
  <b>Comments</b><br>
  <textarea name="comments" cols="80" rows="5" ></textarea><br>
  This file loads successfully in the Sandbox. 
  <input type=radio name=tested value='0'> Yes 
  <input type=radio name=tested value='1' checked> No
  <br><input type=checkbox name=private onclick="document.getElementById('info').style.visibility = 
					     this.checked ? 'visible' : 'hidden'"> 
  This file contains phenotype data private to project members only.
  <!-- If box is checked, show Toronto link. -->
  <div id="info" style="visibility:hidden">
    <br>Please include in the <b>Comments</b> the information about the dataset
    needed for the table on the <a href="toronto.php">Data Usage Policy</a> page.
  </div>

  <br><strong>File:</strong> <input type=file name="file"> 
  <p><input type="submit" value="Upload">
</form>

<?php
     }
}
echo "</div>";
$footer_div=1;
include $config['root_dir'].'theme/footer.php'; 

// Note: For uploading multiple files see genotype_annotations_check.php.
?>
