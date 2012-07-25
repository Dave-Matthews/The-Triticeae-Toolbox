<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();

// Date-stamp the template files etc. if new.
// $path is relative to curator_data/examples/.
function filelink($path, $label) {
  global $config;
  echo "<a href='".$config['base_url']."curator_data/examples/$path'>$label</a>";
  // Add "(new <date>)" if newer than 30 days.
  $fullpath = $config['root_dir'] . "curator_data/examples/$path";
  if (time() - filemtime($fullpath) < 2592000)
    echo " <font size= -2 color=red>(new ". date("dMY", filemtime($fullpath)) . ")</font>";
}

?>

<style type="text/css">
/* Must specify both margin and padding to work same in IE vs. others. */
ul {margin-left: 0; padding-left: 1.5em}
ul ul ul {list-style-type: disc}
</style>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Data Submission</h1>
  <div class="section">



  <br>The data templates are .csv, .txt or  Excel worksheets with column headers for
    the data T3 can accept or requires, some example values to be
    replaced with yours, and notes about the restrictions for valid
    data are included.



      <ul>
      <li><b><?php filelink("T3/Steps_in_Data_Submission_to_T3.docx", "Instructions") ?></b>
	- Rules for filling in the templates, and sequence of submission
<p>
      <li><b>Data templates</b>
<p>
	<ul>
	  <li><b>Germplasm lines</b>
	    - Name, properties, pedigree, GRIN accession... [ <a href="curator_data/tutorial/T3_Lesson1_LineUpload.html"><b>Tutorial</b></a> ]
	    <ul>
	      <li><?php filelink("T3/LineSubmissionForm_Wheat.xls", "Wheat") ?>
              <li><?php filelink("T3/LineSubmissionForm_Barley.xls", "Barley") ?>
	      <li><?php filelink("T3/T3NameConversion.xlsm", "Macros") ?> for converting line names to T3 format
	    </ul>
<p>
	  <li><b>Phenotyping</b>
	    <ul>
          <li><?php filelink("T3/TrialSubmissionForm.xls", "Experiment annotation") ?>
		- Location, planting date, experimental design... [ <a href="curator_data/tutorial/T3_Lesson2_Phenotype.html"><b>Tutorial</b></a> ]
	      <li><?php filelink("T3/PhenotypeSubmissionForm.xls", "Experiment results") ?>
		- Values for all traits for test lines and checks, summary statistics
	      <li><?php filelink("trait_template.xls", "Traits") ?>
		- Within T-CAP the traits, protocols and units will be specified by the project.
	    </ul>
<p>
	  <li><b>Genotyping</b> ... [ <a href="curator_data/tutorial/T3_Lesson3_GenotypeUpload.html"><b>Tutorial</b></a> ]
	    <ul>
	      <li><?php filelink("Geno_Annotation_Sample.txt", "Experiment annotation") ?>
		- Description of the assay
 	      <li><?php filelink("LinesTrialCode_Sample.txt", "Line translation") ?>
		- Line Name and Trial Code
	      <li><?php filelink("genotypeData_T3.txt", "Experiment results") ?>
		- 1D table of alleles for lines and markers
	      <li><?php filelink("TCAPbarley9K-sample.txt", "Experiment results") ?>
		- 2D table of alleles for lines and markers
              </ul> 
<p>
	      <li><b>Markers</b>
              <ul>		
                <li><?php filelink("Generic_SNP.txt", "Sequence") ?> 
		<li><?php filelink("mapupload_example.txt", "Map location") ?>
		<li><?php filelink("Marker_import_sample4.txt", "Gene function") ?> -  Annotations and synonyms 
	    </ul>
	</ul>
    </ul>

    <p>Once filled in, the resulting Excel files are to be uploaded
      directly into the T3 software. Some values are checked for
      validity, most are not.  Some seemingly optional rows at the top
      aren't.  Errors in data you've already loaded in T3 can usually be
      corrected by editing your file and loading it again.  
      
    <p>The Sandbox databases, <a href="http://malt.pw.usda.gov/t3/sandbox/wheat">wheat</a> 
      and <a href="http://malt.pw.usda.gov/t3/sandbox/barley">barley</a>, 
      are available for test-loading your data files.  Once they're ready,
      click below to submit them officially.<br>    
    <input type="Button" value="Submit" onclick="window.open('curator_data/queue.php','_self')">


  </div></div></div>
  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
