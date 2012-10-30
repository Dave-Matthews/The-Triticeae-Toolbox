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
      $this->typeTraits(); /* intial case*/
      break;
    }	
  }

  private function typeTraits() {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
    echo "<h2>Add, Edit or Delete Trait Information </h2>"; 
    $this->type_Trait_Name();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }
	
  private function type_Trait_Name() {
    $url1 = $config['base_url'] . "curator_data/traitAdd.php";
    $url2 = $config['base_url'] . "login/edit_traits.php";
?>
<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<table>
  <tr>
    <td>
      <form action="<?php echo $url1; ?>" method="GET">
	<input type="submit" value="Add Traits">
      </form>
    <td colspan="2">
    <td>
      <form action="<?php echo $url2; ?>" method="GET">
	<input type="submit" value="Edit / Delete Traits">
      </form>  
    </td>
  </tr>
</table>

<?php
   } /* end of type_Trait_Name function*/
} /* end of class */
?>
