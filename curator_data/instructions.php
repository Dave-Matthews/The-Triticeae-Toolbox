<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<style type="text/css">
ul ul ul {list-style-type: circle}
</style>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Data Submission</h1>
  <div class="section">

    <h3>Participant Guide</h3>

    The formats for uploading data to T3 are not yet finalized, so timestamps
    are shown below for each file.  

    <p>The data templates are Excel worksheets with column headers
    for the data T3 can accept or requires, some example values to be replaced with yours,
    and notes about the restrictions for valid data.  

    <p>Once filled in, the resulting Excel files are to be uploaded
      directly into the T3 software, which is limited in how much it can
      do.  Some values are checked for validity, most are not.  Some
      seemingly optional rows at the top aren't.

    <p>Errors in data you've already loaded in T3 can be corrected by
      editing your file and loading it again.  Probably.  This is a
      power feature and hasn't been tested for all cases.  If it fails
      please report the problem.

    <ul>
      <li><a href="curator_data/examples/T3/Steps_in_Data_Submission_to_T3.docx"><b>Instructions</b></a>
	<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/Steps_in_Data_Submission_to_T3.docx")) ?>)</font>
	<br>Rules for filling in the templates, and sequence of submission

      <p><li><b>Data templates</b>
	<ul>
	  <p><li><b>Germplasm lines</b><br>Name, properties, pedigree, GRIN accession...
	    <ul>
	      <li><a href="curator_data/examples/T3/LineSubmissionForm_Wheat.xls">Wheat</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/LineSubmissionForm_Wheat.xls")) ?>)</font>
	      <li><a href="curator_data/examples/T3/LineSubmissionForm_Barley.xls">Barley</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/LineSubmissionForm_Barley.xls")) ?>)</font>
	    </ul>
	  <p><li><b>Phenotyping</b>
	    <ul>
	      <li><a href="curator_data/examples/T3/TrialSubmissionForm.xls">Experiment annotation</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/TrialSubmissionForm.xls")) ?>)</font>
		<br>Location, planting date, experimental design...
	      <li><a href="curator_data/examples/T3/PhenotypeSubmissionForm.xls">Experiment results</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/PhenotypeSubmissionForm.xls")) ?>)</font>
		<br>Values for all traits for test lines and checks, summary statistics
	      <li><a href="curator_data/examples/THT_trait_template.xls">Traits</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/THT_trait_template.xls")) ?>)</font>
		<br>Within TCAP the traits, protocols, units will be specified by the project.
	    </ul>
	  <p><li><b>Genotyping</b>
	    <ul>
	      <li><a href="curator_data/examples/T3/Genotyping_ID_Submission_Form.xls">Sample submission</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/Genotyping_ID_Submission_Form.xls")) ?>)</font>
	      <li>Experiment annotation <font size= -2>(not yet)</font>
		<br>Description of the assay
	      <li>Experiment results <font size= -2>(not yet)</font>
		<br>Table of alleles for lines x markers
	      <li>Markers <font size= -2>(not yet)</font>
		<br>Sequence, map location, gene function...
	    </ul>
	</ul>
    </ul>



  </div></div></div>
  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
