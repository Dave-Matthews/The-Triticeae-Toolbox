<?php
// 20100629 JLee - make link to edit_line.php relative
// 12/14/2010 JLee  Change to use curator bootstrap
// 13feb2012 dem: Add link to edit_synonym.php

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
connect();
loginTest();
$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

/* Add "(new <date>)" if newer than 30 days. */
function filelink($path, $label) {
  echo "<a href='curator_data/examples/$path'>$label</a>";
  if (time() - filemtime("examples/$path") < 2592000)
    echo " <font size= -2 color=red>(new ". date("dMY", filemtime("examples/$path")) . ")</font>";
}

include($config['root_dir'] . 'theme/admin_header.php');
?>

<div class="box">
  <div class="boxContent">
    <h2>Add New Lines</h2>
    <form action="<?php echo $config['base_url'] ?>curator_data/input_line_names_check.php" method="post" 
	  enctype="multipart/form-data">
      <p><strong>File:</strong> <input value="file" type="file" name="file" /> 
  <?php filelink("T3/LineSubmissionForm_Wheat.xls", "Example template") ?>
      <p><input type="submit" value="Upload Line File" /></p>
    </form>
  </div>
</div>

<div class="box">
  <div class="boxContent">
    <h2>Add Genetic Characters for Existing Lines</h2>
    <form action="<?php echo $config['base_url'] ?>curator_data/line_properties.php" method="post" 
	  enctype="multipart/form-data">
      <p><strong>File:</strong> <input value="file" type="file" name="file" /> 
  <?php filelink("T3/Line_Properties.xls", "Example template") ?>
      <p><input type="submit" value="Upload Line File" /></p>
    </form>
  </div>
</div>

<div class="box">
  <div class="boxContent">
    <h2>Edit Lines</h2>
<a href="<?php echo $config['base_url'] ?>login/edit_line.php">Line names and basic properties</a><p>
<a href="<?php echo $config['base_url'] ?>login/edit_synonym.php">Identity</a>: Merging, synonyms, GRIN accessions<p>
<a href="<?php echo $config['base_url'] ?>login/edit_genchars.php">Genetic Characters</a>
  </div>
</div>
		
<div class="box">
  <div class="boxContent">
    <h2>Line Panels</h2>
    <a href="<?php echo $config['base_url'] ?>login/line_panels.php">Add, edit, or delete</a>
  </div>
</div>
		
<?php
$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
