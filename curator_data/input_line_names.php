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

include($config['root_dir'] . 'theme/admin_header.php');
?>

<div class="box">
  <div class="boxContent">
    <h2>Add New Lines</h2>
    <form action="<?php echo $config['base_url'] ?>curator_data/input_line_names_check.php" method="post" 
	  enctype="multipart/form-data">
      <p><strong>File:</strong> <input id="file" type="file" name="file" />
	<a href="<?php echo $config['base_url'] ?>curator_data/examples/T3/LineSubmissionForm_Wheat.xls">
	  Example line input file</a></p>
      <p><input type="submit" value="Upload Line File" /></p>
    </form>
  </div>
</div>

<div class="box">
  <div class="boxContent">
    <h2>Edit Lines</h2>
    <ul>
      <li><a href="<?php echo $config['base_url'] ?>login/edit_line.php">Line names and properties</a>
      <li><a href="<?php echo $config['base_url'] ?>login/edit_synonym.php">Synonyms and GRIN Accessions</a>
    </ul>
  </div>
</div>
		
<?php
$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
