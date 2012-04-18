<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap_curator.inc');
include($config['root_dir'].'theme/admin_header.php');
require_once($config['root_dir'] . 'includes/email.inc');
connect();
?>

<style type=text/css>
  input {vertical-align: middle; margin: 0px;}
  ul {margin-left: 0; padding-left: 1.5em}
  ul ul ul {list-style-type: disc}
</style>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Data Submission</h1>
  <div class="section">
  <p>

<?php 
  if (!empty($_POST) OR !empty($_FILES)) {
  // The Upload button was clicked. Handle user's submission.
  $row = loadUser($_SESSION['username']);
  $username = $row['name'];
  $date = date('dMY');
  $dir= $config['root_dir']."curator_data/uploads/".str_replace(' ', '_', $username)."_".$date."/";
  umask(0);
  if(!file_exists($dir) || !is_dir($dir)) 
    mkdir($dir, 0777);
  if ($_FILES['file']['name'] == "") {
    error(1, "No File Uploaded");
    print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
  }
  else {
    $uploadfile=$_FILES['file']['name'];
    if(move_uploaded_file($_FILES['file']['tmp_name'], $dir.$uploadfile)) {
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
      $host = $_SERVER['SERVER_NAME'];
      $mesg = "$username has submitted a data file.
Data type: $dtype[$dt]
Location: $host
Directory: $dir
Filename: $uploadfile";
      send_email(setting('capmail'), 'Data submitted to T3', $mesg);
    }
    else {
      error(1, "File not stored.");
      print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    }
  }
}

else {
// Nothing submitted yet.
// Require that the user be signed in.
$user = $_SESSION['username'];
if (empty($user)) 
  echo "Please sign in before sending data files to the curator
        for loading into the production database.<br>
        <button type=submit onClick=\"location.href='login.php'\">Sign in</button>";
else 
  echo "Please submit a data file for  the curator to load
        into the production database.";
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
  <p><strong>File:</strong> <input type=file name="file"> 
  <p><input type="submit" value="Upload">
</form>

<?php
// For uploading multiple files see genotype_annotations_check.php.
}

echo "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
