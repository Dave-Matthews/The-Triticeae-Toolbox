<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<style type="text/css">
ul ul ul {list-style-type: circle}
ul {margin-left:0.5em}
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
	<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/Steps_in_Data_Submission_to_T3.docx")) ?>)</font>
	<br>Rules for filling in the templates, and sequence of submission

      <li><b>Data templates</b>
	<ul>
	  <li><b>Germplasm lines</b><br>Name, properties, pedigree, GRIN accession... [ <a href="curator_data/tutorial/T3_Lesson1_LineUpload.html">Tutorial</a> ]
	    <ul>
	      <li><a href="curator_data/examples/T3/LineSubmissionForm_Wheat.xls">Wheat</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/LineSubmissionForm_Wheat.xls")) ?>)</font>
	      <li><a href="curator_data/examples/T3/LineSubmissionForm_Barley.xls">Barley</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/LineSubmissionForm_Barley.xls")) ?>)</font>
	      <li><a href="curator_data/examples/T3/T3NameConversion.xlsm">Macros</a> for converting line names to T3 format
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/T3NameConversion.xlsm")) ?>)</font>
	    </ul>
	  <li><b>Phenotyping</b>
	    <ul>
	      <li><a href="curator_data/examples/T3/TrialSubmissionForm.xls">Experiment annotation</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/TrialSubmissionForm.xls")) ?>)</font>
		<br>Location, planting date, experimental design...
	      <li><a href="curator_data/examples/T3/PhenotypeSubmissionForm.xls">Experiment results</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/PhenotypeSubmissionForm.xls")) ?>)</font>
		<br>Values for all traits for test lines and checks, summary statistics
	      <li><a href="curator_data/examples/trait_template.xls">Traits</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/trait_template.xls")) ?>)</font>
		<br>Within TCAP the traits, protocols, units will be specified by the project.
	    </ul>
	  <li><b>Genotyping</b>
	    <ul>
	      <li><a href="curator_data/examples/T3/Genotyping_ID_Submission_Form.xls">Sample submission</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/T3/Genotyping_ID_Submission_Form.xls")) ?>)</font>
	      <li><a href="curator_data/examples/Geno_Annotation_Sample.txt">Experiment annotation</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/Geno_Annotation_Sample.txt")) ?>)</font>
		<br>Description of the assay
	      <li><a href="curator_data/examples/genotypeData_THT.txt">Experiment results</a>
		<font size= -2>(<?php echo date("dMY", filemtime("examples/genotypeData_THT.txt")) ?>)</font>
		<br>Table of alleles for lines x markers
	      <li>Markers <font size= -2>(<?php echo date("dMY", filemtime("examples/Marker_import_sample4.txt")) ?>)</font>
		<br><a href="curator_data/examples/Generic_SNP.txt">Sequence</a>, 
		<a href="curator_data/examples/mapupload_example.txt">map location</a>, 
		<a href="curator_data/examples/Marker_import_sample4.txt">gene function</a>
	    </ul>
	</ul>
    </ul>

    <p>Once filled in, the resulting Excel files are to be uploaded
      directly into the T3 software. Some values are checked for
      validity, most are not.  Some seemingly optional rows at the top
      aren't.  Errors in data you've already loaded in T3 can probably
      be corrected by editing your file and loading it again.  This
      feature and hasn't been tested for all cases.  If it fails please
      <a href="feedback.php">report the problem</a>.


  </div></div></div>
  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
