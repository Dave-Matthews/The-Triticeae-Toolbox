<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

  <h1>Under Construction</h1>
  <div class="section">
  <p>Not yet implemented.</p>
  </div>

<?php 
/* echo "session_id = ". session_id()."<br>"; */
/* echo "session_name = ". session_name()."<br>"; */
/* echo "COOKIE:"; */
/* print_h($_COOKIE); */
/* echo "SESSION:"; */
/* print_h($_SESSION); */

$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
