<?php

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();
loginTest();

$row = loadUser($_SESSION['username']);
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Traits($_GET['function']);

class Traits {

  // Using the class's constructor to decide which action to perform
  public function __construct($function = null) {	
    switch($function) {
    default:
      $this->typeSelect(); /* intial case*/
      break;
    }	
  }

  private function typeSelect() {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
    echo "<h2>Add or Edit CSR Information </h2>"; 
    $this->type_func_sel();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }
	
  private function type_func_sel() {
    $url1 = $config['base_url'] . "curator_data/input_csr_spect.php";
    $url2 = $config['base_url'] . "curator_data/input_csr_field.php";
    $url3 = $config['base_url'] . "curator_data/input_csr_exper.php";
    $url4 = $config['base_url'] . "login/edit_traits.php";
?>
<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<table>
    <tr><td>Spectrometer System<td><form action="<?php echo $url1; ?>" method="GET">
        <input type="submit" value="Add">
      </form>
    <tr><td>Field Book<td><form action="<?php echo $url2; ?>" method="GET">
	<input type="submit" value="Add">
      </form>
     <tr><td>Phenotype Results<td><form action="<?php echo $url3; ?>" method="GET">
	<input type="submit" value="Add">
      </form>  
</table>

<?php
   } /* end of type_func_sel function*/
} /* end of class */
?>
