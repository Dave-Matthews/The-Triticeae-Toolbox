<?php
// dem 23oct2014 Edit contents of the "What's New" box.
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
$mysqli = connecti();
loginTest();

ob_start();
include $config['root_dir'] . 'theme/admin_header.php';
authenticate_redirect(array(USER_TYPE_CURATOR,USER_TYPE_ADMINISTRATOR));
ob_end_flush();

echo "<h2>Edit the Data Usage Policy datasets table in <a href='toronto.php'>toronto.php</a></h2>";
/*
 * Has an update been submitted?
 */
if( !empty($_POST) ) {
  $c = "<!-- empty element for PHP file() to eat -->\n";
  $c .= $_POST[contents];
  // Delete, or it will be there in browser page refreshes.
  $_POST = array();
  // Now decode it back.
  $c = html_entity_decode($c);
  $c = preg_replace('/\\\r/', '', $c );
  $c = preg_replace('/\\\n/', "\n", $c );
  $c = preg_replace("/\\\'/", "'", $c );

  // Open the file and empty it.
  $fname = $config['root_dir'] . 'toronto_table.html';
  $outfile = fopen($fname, "w");
  if ($outfile === FALSE)
    die("Unable to open file toronto_table.html.  Probably lack of write-permission.");
  $status = fwrite($outfile, $c);
  fclose($outfile);  
  /* echo "<p>Refresh this page to see the result in the WhatsNew box."; */
}
// Show the current contents in an edit box.
echo "<form method=POST>";
echo "<textarea name='contents' rows=30 cols=110";
$content = file($config['root_dir'] . 'toronto_table.html');
foreach($content as $linenum=>$str)
  echo($str);
echo "</textarea><br>";
echo "<input type='submit' value='Update'>";
echo "</form>";

echo "</div>";
include $config['root_dir'] . 'theme/footer.php';
?>
