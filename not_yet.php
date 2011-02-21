<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Under Construction</h1>
  <div class="section">
  <p>Not yet implemented.</p>
  </div></div></div>

  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>