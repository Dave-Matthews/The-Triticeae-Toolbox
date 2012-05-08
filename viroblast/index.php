<?php 
/* header("Location: viroblast.php");  */
?>

<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
    <h1>ViroBLAST for T3</h1>
    <div class="section">

      An enhanced version of <a href="http://indra.mullins.microbiol.washington.edu/viroblast/viroblast.php">ViroBLAST</a>, 
      integrated into T3, is available.
      <p>
      To obtain it, please send a copy of your ViroBLAST license to 
      <a href="mailto:matthews@graingenes.org">Dave Matthews</a>.  
      <br>This is necessary to comply with our license to use ViroBLAST.
      <p>
	Thanks,
	<br>- Dave

</div></div></div>

  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
