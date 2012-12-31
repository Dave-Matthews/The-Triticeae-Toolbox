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
<li>Some values are checked for
  validity (most are not).  
<li>To make updates or corrections, edit your file and reload.
<li>See specific tutorials for more information.  
</ul>

<p>
 <b><?php filelink("T3/Steps_in_Data_Submission_to_T3.docx", "Instructions") ?></b> 
      - Rules for filling in the templates, and sequence of submission
      <p>

<b>T3 Tutorials</b><br>
<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson1_LineUpload.html" target="_blank">Lesson One.</a> Germplasm file creation and upload<br>
<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson2_Phenotype2012.html" target="_blank">Lesson Two.</a> (<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson2_Phenotype.pdf">.pdf</a>) Phenotype trial annotation and data.  <font size= -2 color=red>(new 27Dec2012)</font></b> <br>
<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Lesson3_GenotypeUpload.html" target="_blank">Lesson Three.</a> Genotype trial annotation and data<br>
<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_Line_Panels.pdf" target="_blank">Lesson Four.</a> How to create germplasm line panels in T3<br>
<p></p>
    
  <p>The Sandbox databases, <a href="http://malt.pw.usda.gov/t3/sandbox/wheat">wheat</a> 
    and <a href="http://malt.pw.usda.gov/t3/sandbox/barley">barley</a>, 
    are available for test-loading your data files.  Once they're ready,
    click below to submit them officially.<br>    
    <input type="Button" value="Submit" onclick="window.open('curator_data/queue.php','_self')">


<p></p>
<b>T3 Data Templates</b><br>

<div style=" text-align: left; text-indent: 0px; padding: 0px 0px 0px 0px; margin: 0px 0px 0px 0px;"><table width="80%" border="" cellpadding="1" cellspacing="1" style="background-color: #ffffff;">

<tr valign="top">
<td style="border-width : 0px;"><b><u>Topic</u></b><br />
</td>

<td style="border-width : 0px;"><b><u>Link</u></b><br />
</td>
<td style="border-width : 0px;"><b><u>Template file name</u></b><br />
</td>
<td style="border-width : 0px;"><b><u>Information</u></b><br />
</td>
</tr>

<tr valign="top">
<td style="border-width : 0px;">Germplasm Lines<br />
</td>

<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/T3/LineSubmissionForm_Wheat.xls'>Wheat</a><br />
</td>
<td style="border-width : 0px;">LineSubmissionForm_Wheat.xls<br />
</td>
<td style="border-width : 0px;">Name, properties, pedigree, GRIN accessions for wheat<br />
</td>
</tr>

<tr valign="top">
<td style="border-width : 0px;"><br />
</td>

<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/T3/LineSubmissionForm_Barley.xls'>Barley</a><br />
</td>
<td style="border-width : 0px;">LineSubmissionForm_Barley.xls <br />
</td>
<td style="border-width : 0px;">Name, properties, pedigree, GRIN accessions for barley<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>

<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/T3/T3NameConversion.xlsm'>Name Conversion Macros</a><br />
</td>
<td style="border-width : 0px;">T3NameConversion.xlsm<br />
</td>
<td style="border-width : 0px;">Excel spreadsheet to convert germplasm names to T3 formatted names.<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;">Phenotyping<br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/T3/TrialSubmissionForm.xls'>Experiment annotation</a><br />
</td>
<td style="border-width : 0px;">TrialSubmissionForm.xls<br />
</td>
<td style="border-width : 0px;">Location, planting date, experimental design... <font size= -2 color=red>(new 04Dec2012)</font><br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/T3/PhenotypeSubmissionForm.xls'>Experiment results</a> <br />
</td>
<td style="border-width : 0px;">PhenotypeSubmissionForm.xls<br />
</td>
<td style="border-width : 0px;">Values for all traits for test lines and checks, summary statistics<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/trait_template.xls'>Traits</a><br />
</td>
<td style="border-width : 0px;">trait_template.xls <br />
</td>
<td style="border-width : 0px;">Trait files are only necessary for new traits.  Please discuss with the <a href="feedback.php">curators</a> before adding a new trait.<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;">Genotyping<br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/Geno_Annotation_Sample.txt'>Experiment annotation</a><br />
</td>
<td style="border-width : 0px;">Geno_Annotation_Sample.txt<br />
</td>
<td style="border-width : 0px;">Trial information, software, manifest file names...<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/LinesTrialCode_Sample.txt'>Line translation</a><br />
</td>
<td style="border-width : 0px;">LinesTrialCode_Sample.txt<br />
</td>
<td style="border-width : 0px;">Line Name and Trial Code<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/TCAPbarley9K-sample.txt'>Experiment results (2D)</a><br />
</td>
<td style="border-width : 0px;">TCAPbarley9K-sample.txt<br />
</td>
<td style="border-width : 0px;">2D table of alleles for lines and markers <b>(Preferred!)</b><br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/genotypeData_T3.txt'>Experiment results (1D)</a><br />
</td>
<td style="border-width : 0px;">genotypeData_T3.txt<br />
</td>
<td style="border-width : 0px;">1D table of alleles for lines and markers<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;">Markers<br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/Generic_SNP.txt'>Sequence</a><br />
</td>
<td style="border-width : 0px;">Generic_SNP.txt<br />
</td>
<td style="border-width : 0px;">Marker sequence and A/B allele calls<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/mapupload_example.txt'>Map location</a><br />
</td>
<td style="border-width : 0px;">mapupload_example.txt<br />
</td>
<td style="border-width : 0px;">Genetic / physical maps for T3<br />
</td>
</tr>
<tr valign="top">
<td style="border-width : 0px;"><br />
</td>
<td style="border-width : 0px;"><a href='http://malt.pw.usda.gov/t3/barley/curator_data/examples/Marker_import_sample4.txt'>Gene function</a> <br />
</td>
<td style="border-width : 0px;">Marker_import_sample4.txt<br />
</td>
<td style="border-width : 0px;">Marker annotations and synonyms<br />
</td>
</tr>



</table>




  <!--  old code


	<li><b>Data templates</b> 
	  <p>
	    <ul>
	      <li><b>Germplasm lines</b>
		- Name, properties, pedigree, GRIN accession... 
		[ <a href="curator_data/tutorial/T3_Lesson1_LineUpload.html" target="_blank"><b>Tutorial</b></a> <i><font color="blue">T3_Lesson1_LineUpload.html</font></i> ]
		<ul>
		  <li><?php filelink("T3/LineSubmissionForm_Wheat.xls", "Wheat") ?> <i><font color="blue">LineSubmissionForm_Wheat.xls </font></i>
		  <li><?php filelink("T3/LineSubmissionForm_Barley.xls", "Barley") ?> <i><font color="blue">LineSubmissionForm_Barley.xls </font></i>
		  <li><?php filelink("T3/T3NameConversion.xlsm", "Macros") ?> <i><font color="blue">T3NameConversion.xlsm</font></i> for converting line names to T3 format
		</ul>
		<p>
		  <li><b>Phenotyping</b>... [ <a href="curator_data/tutorial/T3_Lesson2_Phenotype2012.html" target="_blank"><b>Tutorial</b></a> <i><font color="blue">T3_Lesson2_Phenotype2012.html </font></i>]
		    <ul>
		      <li><?php filelink("T3/TrialSubmissionForm.xls", "Experiment annotation") ?> <i><font color="blue">TrialSubmissionForm.xls</font></i>
			- Location, planting date, experimental design... 
			
		      <li><?php filelink("T3/PhenotypeSubmissionForm.xls", "Experiment results") ?> <i><font color="blue">PhenotypeSubmissionForm.xls </font></i>
			- Values for all traits for test lines and checks, summary statistics
		      <li><?php filelink("trait_template.xls", "Traits") ?> <i><font color="blue">trait_template.xls </font></i>
			- Only necessary for new traits.  Please discuss with the <a href="feedback.php">curators</a> before adding a new trait.
		    </ul>
		    <p>
		      <li><b>Genotyping</b> ... 
			[ <a href="curator_data/tutorial/T3_Lesson3_GenotypeUpload.html" target="_blank"><b>Tutorial</b></a>  <i><font color="blue">T3_Lesson3_GenotypeUpload.html</font></i>]
			<ul>
			  <li><?php filelink("Geno_Annotation_Sample.txt", "Experiment annotation") ?> <i><font color="blue">Geno_Annotation_Sample.txt</font></i>
			    - Description of the assay
 			  <li><?php filelink("LinesTrialCode_Sample.txt", "Line translation") ?> <i><font color="blue">LinesTrialCode_Sample.txt</font></i>
			    - Line Name and Trial Code
			  <li><?php filelink("TCAPbarley9K-sample.txt", "Experiment results") ?> <i><font color="blue">TCAPbarley9K-sample.txt</font></i>
			    - 2D table of alleles for lines and markers
			  <li><?php filelink("genotypeData_T3.txt", "Experiment results") ?> <i><font color="blue">genotypeData_T3.txt</font></i>
			    - 1D version
			</ul> 
			<p>
			  <li><b>Markers</b>
			    <ul>		
			      <li><?php filelink("Generic_SNP.txt", "Sequence") ?>  <i><font color="blue">Generic_SNP.txt</font></i>
			      <li><?php filelink("mapupload_example.txt", "Map location") ?> <i><font color="blue">mapupload_example.txt</font></i>
			      <li><?php filelink("Marker_import_sample4.txt", "Gene function") ?>  <i><font color="blue">Marker_import_sample4.txt</font></i>
				-  Annotations and synonyms 
			    </ul>
	    </ul>
  </ul>

end old code -->



</div>

<?php
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

  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); 

?>
