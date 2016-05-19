<?php
/**
 *  12/14/2010 JLee  Change to use curator bootstrap
 **/

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
$mysqli = connecti();
loginTest();
$row = loadUser($_SESSION['username']);
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Annotations($_GET['function']);

class Annotations
{
    // Using the class's constructor to decide which action to perform
    public function __construct($function = null)
    {
        switch ($function) {
          default:
            $this->typeAnnotations(); /* intial case*/
            break;
        }	
   }

  private function typeAnnotations() {
    global $config;
    include $config['root_dir'] . 'theme/admin_header.php';
    echo "<h2>Phenotype Trial Description</h2>"; 
    $this->type_Annotation_Name();
    $footer_div = 1;
    include $config['root_dir'].'theme/footer.php';
  }

  private function type_Annotation_Name() {

    /* Add "(new <date>)" if newer than 30 days. */
    function filelink($path, $label) {
      echo "<a href='curator_data/examples/$path'>$label</a>";
      if (time() - filemtime("examples/$path") < 2592000)
	echo " <font size= -2 color=red>(new ". date("dMY", filemtime("examples/$path")) . ")</font>";
    }
    ?>

<style type="text/css">
  a {text-decoration: none;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<div class="section">
<form action="curator_data/input_annotations_check_excel.php" method="post" enctype="multipart/form-data">
<h3>Add</h3>
<!-- <a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_excel.php"><b>Upload an Excel (.xls) File</b></a> -->
  Do you want the data from this trial to be <b>Public</b>? 
    <input type='radio' name='flag' value="1" checked/> Yes &nbsp;&nbsp; 
    <input type='radio' name='flag' value="0"/> No
  <p>Trial description file: <input id="file[]" type="file" name="file[]" size="50%" />
    <?php filelink("T3/TrialSubmissionForm.xls", "Example") ?>
  <br><input type="submit" value="Upload" />
</form>
</div>

<div class="section">
<h3>Edit</h3>
<table>
<tr><td><a href="<?php echo $config['base_url']; ?>login/edit_trials.php">Trial descriptions</a><td>(for Genotype experiments as well as Phenotype)
<tr><td><a href="<?php echo $config['base_url']; ?>login/edit_experiments.php">Experiment descriptions</a><td>Experiment Sets
<tr><td><a href="<?php echo $config['base_url']; ?>login/edit_pheno_expr.php">Trial details</a><td>Phenotype-trial-specific annotations
</table><p>
</div>

<div class="section">
  <h3>Add Field Layout File</h3>
  Required for loading plot-level phenotype data. 
  <p><form action="curator_data/input_csr_field_check.php" method="post" enctype="multipart/form-data">Field layout file:
  <input type="hidden" id="plot" name="plot" value="-1" />
  <input id="file[]" type="file" name="file[]" size="50%" />
  <a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/fieldbook_template.xlsx">Example</a><br>
  <input type="submit" value="Upload" /></p>
  </form>
</div>

<div class="section">
  <h3>Design Field Layout File</h3>
  <a href="curator_data/exp_design.php">Experiment Design and Field Layout</a>
</div>

<!-- <a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_text.php"><li><b>Upload a Tab Delimited(.txt) File</b></li></a><br> -->
<!-- <a href="<?php echo $config['base_url']; ?>curator_data/delete_experiment.php"><li><b>Delete an experiment</b></li></a> -->
		
<?php
    } /* end of type_Annotation_Name function*/
} /* end of class */
?>
