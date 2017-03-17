<?php

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
loginTest();

$row = loadUser($_SESSION['username']);
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Traits($_GET['function']);

class Traits
{

  // Using the class's constructor to decide which action to perform
  public function __construct($function = null)
  {	
    switch($function) {
    default:
      $this->typeSelect(); /* intial case*/
      break;
    }	
  }

  private function typeSelect()
  {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
    echo "<h2>Add or Edit Canopy Spectral Reflectance (CSR) Information </h2>"; 
    $this->type_func_sel();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }
	
  private function type_func_sel()
  {
    $url1 = $config['base_url'] . "curator_data/input_csr_spect.php";
    $url2 = $config['base_url'] . "curator_data/input_csr_field.php";
    $url3 = $config['base_url'] . "curator_data/input_csr_exper.php";
    $url4 = $config['base_url'] . "curator_data/cal_index.php";
?>
<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

The Phenotype Trial must be loaded before adding CSR data<br><br>
<table>
    <tr><td>Field Book<td><form action="<?php echo $url2; ?>" method="post">
        <input type="submit" value="Add">
      </form><td>plot, line, trial, row, column, and experiment design
    <tr><td>Spectrometer System<td><form action="<?php echo $url1; ?>" method="post">
        <input type="submit" value="Add">
      </form><td>spectrometer system used for recording the measurements
     <tr><td>Phenotype Results<td><form action="<?php echo $url3; ?>" method="post">
	<input type="submit" value="Add"><td>CSR data and Annotation file
      </form>  
     <tr><td>Calculate Index<td><form action="<?php echo $url4; ?>" method="post">
        <input type="submit" value="Add"><td>calculate an index for a loaded phenotype data file using a formula (NDVI, NWI, etc)
      </form>
</table>

<?php
   } /* end of type_func_sel function*/
} /* end of class */
?>
