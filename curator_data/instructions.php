<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<style type="text/css">
ul {padding-left: 1.5em}
ul ul ul {list-style-type: circle}
</style>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Data Submission</h1>
  <div class="section">

    <p>The data templates are Excel worksheets with column headers for
    the data T3 can accept or requires, some example values to be
    replaced with yours, and notes about the restrictions for valid
    data.

      <ul>
      <li><a href="curator_data/examples/T3/Steps_in_Data_Submission_to_T3.docx"><b>Instructions</b></a>
	- Rules for filling in the templates, and sequence of submission

      <li><b>Data templates</b>
	<ul>
	  <li><b>Germplasm lines</b>
	    - Name, properties, pedigree, GRIN accession... [ <a href="curator_data/tutorial/T3_Lesson1_LineUpload.html">Tutorial</a> ]
	    <ul>
	      <li><a href="curator_data/examples/T3/LineSubmissionForm_Wheat.xls">Wheat</a>
	      <li><a href="curator_data/examples/T3/LineSubmissionForm_Barley.xls">Barley</a>
	      <li><a href="curator_data/examples/T3/T3NameConversion.xlsm">Macros</a> for converting line names to T3 format
	    </ul>
	  <li><b>Phenotyping</b>
	    <ul>
	      <li><a href="curator_data/examples/T3/TrialSubmissionForm.xls">Experiment annotation</a>
		- Location, planting date, experimental design...
	      <li><a href="curator_data/examples/T3/PhenotypeSubmissionForm.xls">Experiment results</a>
		- Values for all traits for test lines and checks, summary statistics
	      <li><a href="curator_data/examples/trait_template.xls">Traits</a>
		- Within T-CAP the traits, protocols and units will be specified by the project.
	    </ul>
	  <li><b>Genotyping</b>
	    <ul>
	      <li><a href="curator_data/examples/T3/Genotyping_ID_Submission_Form.xls">Sample submission</a>
	      <li><a href="curator_data/examples/Geno_Annotation_Sample.txt">Experiment annotation</a>
		- Description of the assay
	      <li><a href="curator_data/examples/genotypeData_THT.txt">Experiment results</a>
		- Table of alleles for lines x markers
	      <li>Markers
		<br><a href="curator_data/examples/Generic_SNP.txt">Sequence</a>, 
		<a href="curator_data/examples/mapupload_example.txt">map location</a>, 
		<a href="curator_data/examples/Marker_import_sample4.txt">gene function</a>
	    </ul>
	</ul>
    </ul>

    <p>Once filled in, the resulting Excel files are to be uploaded
      directly into the T3 software. Some values are checked for
      validity, most are not.  Some seemingly optional rows at the top
      aren't.  Errors in data you've already loaded in T3 can usually be
      corrected by editing your file and loading it again.  In cases
      where this doesn't work please <a href="feedback.php">contact
      us</a> to make the corrections.

  </div></div></div>
  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
