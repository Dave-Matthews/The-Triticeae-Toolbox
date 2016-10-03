<?php

// 14feb2013 dem Add new property values.  Taken from ./traitAdd.php

require 'config.php';
include $config['root_dir'] . 'includes/bootstrap_curator.inc';
include $config['root_dir'] . 'theme/admin_header.php';

/*
 * Logged in page initialization
 */
$mysqli = connecti();
loginTest();
$row = loadUser($_SESSION['username']);
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

echo "<div class='box'>";

if($error != "") 	//is there an error?
  error(1, $error);
?>

<h2>Add New Genetic Characters</h2>
<div class="boxContent">

<p>Upload an <em>Excel</em> file with the format suggested by
the <em><?php filelink('T3/property_template.xls', 'Property Template') ?></em>.

<form action="<?php echo $config['base_url']; ?>curator_data/uploader.php?type=properties" 
method="post" enctype="multipart/form-data">

<p><input type="file" name="file" size="80%" /></p>
<p><input type="submit" value="Upload" /></p>

</form>
</div>
  <?php

/* Add "(new <date>)" if newer than 30 days. */
function filelink($path, $label) {
  echo "<a href='curator_data/examples/$path'>$label</a>";
  if (time() - filemtime("examples/$path") < 2592000)
    echo " <font size= -2 color=red>(new ". date("dMY", filemtime("examples/$path")) . ")</font>";
}

?>

</div>
</div>

<?php include $config['root_dir'] . '/theme/footer.php';?>
