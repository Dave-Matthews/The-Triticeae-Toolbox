<?php
$usegbrowse = True;
require 'config.php';
/*
 * Logged in page initialization
 */

include($config['root_dir'].'includes/bootstrap.inc');
connect();
session_start();

include($config['root_dir'].'theme/normal_header.php');
?>
<iframe id="gbrowse" src="/cgi-bin/gbrowse/tht/"
  width="100%" height="1000pt">
  <p>You need to find a browser that supports IFRAME</p>
</iframe>

<?php
  $footer_div = 1;
 include($config['root_dir'].'theme/footer.php');
?>
