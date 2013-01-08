<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
$mysqli = connecti();
loginTest();
$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Instrument($_GET['function']);

class Instrument {
  private $delimiter = "\t";
  // Using the class's constructor to decide which action to perform
  public function __construct($function = null) 	{	
    switch($function) {
    default:
      $this->typeInstruments(); /* initial case*/
      break;
    }	
  }

  private function typeInstruments() {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
    echo "<h2>Add CSR System Description</h2>"; 
    echo "Required before loading CSR results.<br>";
    $this->type_Instrument_Name();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }
	
  private function type_Instrument_Name()  {
    ?>

<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<br>
<form action="curator_data/input_csr_spect_check.php" method="post" enctype="multipart/form-data">
  <table>
    <tr><td><strong>CSR System Description File:</strong><td><input id="file[]" type="file" name="file[]" size="50%" /><td><a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/CSRinT3_SpectrometerSystem.xlsx">Example File</a></tr>
  </table>
  <p><input type="submit" value="Upload" /></p>
</form>
		
<?php
       } /* end of type_Instrument_Name function*/
} /* end of class */
?>
