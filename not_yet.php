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

$browser = $_SERVER['HTTP_USER_AGENT'];
echo "browser = $browser<P>";
/*
Firefox: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:39.0) Gecko/20100101 Firefox/39.0
         Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0
Chrome:  Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36
         Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.125 Safari/537.36
Safari:  Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/7.1.7 Safari/537.85.16
IE9:     Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)
 */

echo "JOE";

$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
