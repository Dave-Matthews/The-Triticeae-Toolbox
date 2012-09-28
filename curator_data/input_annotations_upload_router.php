<?php
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Annotations($_GET['function']);

class Annotations {

  private $delimiter = "\t";
  // Using the class's constructor to decide which action to perform
  public function __construct($function = null) {	
    switch($function) {
    default:
      $this->typeAnnotations(); /* intial case*/
      break;
    }	
  }

  private function typeAnnotations() {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');

    echo "<h2>Add Phenotype Trial Annotations</h2>"; 
    $this->type_Annotation_Name();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }

  private function type_Annotation_Name() {

    ?>

<style type="text/css">
  a {text-decoration: none;}
</style>

<table>
<tr><td><a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_excel.php"><b>Upload an Excel (.xls) File</b></a>
<tr><td><a href="<?php echo $config['base_url']; ?>login/edit_trials.php"><b>Edit Trial Annotations</b></a><td>Genotype and Phenotype experiments
<tr><td><a href="<?php echo $config['base_url']; ?>login/edit_pheno_expr.php"><b>Edit Phenotype Experiment</b></a><td>Phenotype specific annotation
</table>

<!-- <a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_text.php"><li><b>Upload a Tab Delimited(.txt) File</b></li></a><br> -->
<!-- <a href="<?php echo $config['base_url']; ?>curator_data/delete_experiment.php"><li><b>Delete an experiment</b></li></a> -->
		
<?php
    } /* end of type_Annotation_Name function*/
} /* end of class */
?>
