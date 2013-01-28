<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<style type="text/css">
  /* Must specify both margin and padding to work same in IE vs. others. */
  ul {margin-left: 0; padding-left: 1.5em}
  ul ul ul {list-style-type: disc}
  tr {vertical-align: top}
  table td {border-width: 0px}
</style>

<h1>Data Submission</h1>
<div class="section">
  <ul>
    <li>Data templates are .csv, .txt or  Excel worksheets with column headers for
      the data T3 requires or accepts.
    <li>Most example values can simply be replaced with your own, and notes about the restrictions for valid
      data are included on the templates.    
    <li>Once populated, the complete Excel or text files can be uploaded
      directly into the T3 software. 
    <li>CAP participants should use the Sandbox database for your crop to test load data.
    <li>Some values are checked for validity (most are not).  
    <li>To make updates or corrections, edit your file and reload.
    <li>See specific tutorials for more information.  
  </ul>

  <p>
    <b><?php filelink2("Instructions","Steps_in_Data_Submission_to_T3.docx", "T3") ?></b> 
    - Rules for filling in the templates, and sequence of submission
  <p>
    <b>Tutorials</b><br>
    <a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson1_LineUpload.html" target="_blank">Lesson One.</a> Germplasm file creation and upload<br>
    <a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson2_Phenotype2012.html" target="_blank">Lesson Two.</a>
    (<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson2_Phenotype.pdf">.pdf</a>)
    Phenotype trial annotation and data. <font size=-2 color=red>(new 27Dec2012)</font></b><br>
<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson3_GenotypeUpload.html" target="_blank">Lesson Three.</a> Genotype trial annotation and data<br>
<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Line_Panels.pdf" target="_blank">Lesson Four.</a> How to create germplasm line panels in T3<br>

<p>The Sandbox databases, <a href="http://malt.pw.usda.gov/t3/sandbox/wheat">wheat</a> 
  and <a href="http://malt.pw.usda.gov/t3/sandbox/barley">barley</a>, 
  are available for test-loading your data files.  Once they&apos;re ready,
  click below to submit them officially.<br>
  <input type="Button" value="Submit" onclick="window.open('curator_data/queue.php','_self')">
</p>

<b>Data Templates</b>

<div style=" text-align: left; text-indent: 0px; padding: 0px 0px 0px 0px; margin: 0px 0px 0px 0px;">

  <table width="99%" border="none" cellpadding="1" cellspacing="1" style="background-color: #ffffff;">
    <thead>
      <th>Topic
      <th>Link
      <th>Version
      <th>Template file name
      <th>Information
    </thead>
    <tr>
      <td><b>Germplasm Lines</b>
      <td><?php filelink("Wheat","LineSubmissionForm_Wheat.xls", "T3") ?>
      <td>Name, properties, pedigree, GRIN accessions for wheat
    </tr>
    <tr>
      <td>
      <td><?php filelink("Barley","LineSubmissionForm_Barley.xls", "T3") ?>
      <td>Name, properties, pedigree, GRIN accessions for barley
    </tr>
    <tr>
      <td>
      <td><?php filelink("Name conversion macros","T3NameConversion.xlsm", "T3") ?>
      <td>Excel spreadsheet to convert germplasm names to T3 formatted names.
    </tr>
    <tr>
      <td><b>Phenotyping</b>
      <td><?php filelink("Experiment annotation","TrialSubmissionForm.xls", "T3") ?>
      <td>Location, planting date, experimental design...
    </tr>
    <tr>
      <td>
      <td><?php filelink("Experiment results","PhenotypeSubmissionForm.xls", "T3") ?>
      <td>Values for all traits for test lines and checks, summary statistics
    </tr>
    <tr>
      <td>
      <td><?php filelink("Traits","trait_template.xls", "") ?>
      <td>Trait files are only necessary for new traits.  
	Please discuss with the <a href="feedback.php">curators</a> before adding a new trait.
    </tr>
    <tr>
      <td><b>Genotyping</b>
      <td><?php filelink("Experiment annotation","Geno_Annotation_Sample.txt", "") ?>
      <td>Trial information, software, manifest file names...
    </tr>
    <tr>
      <td>
      <td><?php filelink("Line translation","LinesTrialCode_Sample.txt", "") ?>
      <td>Line Name and Trial Code
    </tr>
    <tr>
      <td>
      <td><?php filelink("Experiment results","TCAPbarley9K-sample.txt", "") ?>
      <td>2D table of alleles for lines and markers <b>(Preferred!)</b>
    </tr>
    <tr>
      <td>
      <td><?php filelink("Experiment results (1D)","genotypeData_T3.txt", "") ?>
      <td>1D table of alleles for lines and markers
    </tr>
    <tr>
      <td><b>Markers</b>
      <td><?php filelink("Sequence","Generic_SNP.txt", "") ?>
      <td>Marker sequence and A/B allele calls
    </tr>
    <tr>
      <td>
      <td><?php filelink("Map location","mapupload_example.txt", "") ?>
      <td>Genetic / physical maps for T3
    </tr>
    <tr>
      <td>
      <td><?php filelink("Gene function","Marker_import_sample4.txt", "") ?>
      <td>Marker annotations and synonyms
    </tr>
  </table>
</div></div>

<?php
// Date-stamp the template files, in red if they're new.
// $subdir is relative to curator_data/examples/.
function filelink($label, $filenm, $subdir) {
  global $config;
  echo "<a href='".$config['base_url']."curator_data/examples/$subdir/$filenm'>$label</a></td><td>";
  $fullpath = $config['root_dir'] . "curator_data/examples/$subdir/$filenm";
  // Paint in red if newer than 30 days.
  if (time() - filemtime($fullpath) < 2592000)
    echo "<font  color=red>". date("dMy", filemtime($fullpath)) . "</font>";
  else
    echo date("dMy", filemtime($fullpath));
  echo "</td><td>$filenm";
}

// Variation of filelink(), for items in text instead of in the table.
function filelink2($label, $filenm, $subdir) {
  global $config;
  echo "<a href='".$config['base_url']."curator_data/examples/$subdir/$filenm'>$label</a></td><td>";
  // Add "(new <date>)" if newer than 30 days.
  $fullpath = $config['root_dir'] . "curator_data/examples/$subdir/$filenm";
  if (time() - filemtime($fullpath) < 2592000)
    echo " <font size= -2 color=red>(new ". date("dMY", filemtime($fullpath)) . ")</font>";
}

$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
