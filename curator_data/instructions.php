<?php
/**
 * Data submission
 *
 * PHP version 5.3
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap2.inc';
require $config['root_dir'].'theme/admin_header2.php';
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
    <li>Data templates are Excel, .txt, or .csv worksheets with column headers for
      the data T3 requires or accepts.
    <li>The example values can be replaced with your own.  Notes about the restrictions for valid
      data are included in the templates.    
    <li>Once populated, the files can be loaded into T3 using
    the <b>Curate</b> menu, which is available to registered Sandbox
    users.
    <li>To make updates or corrections, edit your file and reload.
    <li>Please use the <a href="http://t3sandbox.org/t3/sandbox/wheat">wheat</a> 
      or <a href="http://t3sandbox.org/t3/sandbox/barley">barley</a> Sandbox database
      for test-loading your files.  When they&apos;re ready,
      click here to submit them to the T3 Curator for loading into the official database.
      <br><input type="Button" value="Submit" onclick="window.open('<?php echo $config['base_url']?>curator_data/queue.php','_self')">
  </ul>

  <p>
    <b><?php filelink2("Instructions", "Steps_in_Data_Submission_to_T3.docx", "T3") ?></b> 
    - Rules for filling in the templates, and sequence of submission
<br>
    <b>Tutorials</b><br>
    <?php
    $files = scandir($config['root_dir'] . "curator_data/tutorial");
    foreach ($files as $item) {
        if (preg_match("/(^[^_]+)_([^\s]+)/", $item, $match)) {
            $item_tag = $match[1];
            $item_des = $match[2];
        } else {
            $item_tag = $item;
            $item_des = $item;
        }
        if (preg_match('/(pdf|html|pptx)/', $item_des)) {
            $item_clean = preg_replace("/\.pdf/", "", $item_des);
            $item_clean = preg_replace("/\.html/", "", $item_clean);
            $item_clean = preg_replace("/\.pptx/", "", $item_clean);
            $item_clean = preg_replace("/_/", " ", $item_clean);
            echo "<a href=\"" . "curator_data/tutorial/" . "$item\">$item_tag</a>  $item_clean<br>\n";
        } else {
            echo "$item_des<br>\n";
        }
    }
    echo "<br>\n";
    ?>

<h3>Data Templates</h3>

<div style=" text-align: left; text-indent: 0px; padding: 0px 0px 0px 0px; margin: 0px 0px 0px 0px;">

  <table width="99%" cellpadding="1" cellspacing="1" style="background-color: #ffffff; border: none;">
    <thead>
      <th>Topic
      <th>Link
      <th>Version
      <!-- <th>Template file name -->
      <th>Contents
    </thead>
    <tr>
      <td><b>Germplasm Lines</b>
      <td><?php filelink("Wheat", "LineSubmissionForm_Wheat.xls", "T3") ?>
      <td>Name, synonyms, pedigree for wheat
    </tr>
    <tr>
      <td>
      <td><?php filelink("Barley", "LineSubmissionForm_Barley.xls", "T3") ?>
      <td>Name, synonyms, pedigree for barley
    </tr>
    <tr>
      <td>
      <td><?php filelink("Genetic Characters", "Line_Properties.xls", "T3") ?>
      <td>Genes, QTLs, trait-linked markers, market class
    </tr>
    <tr>
      <td>
      <td><?php filelink("Name conversion macros", "T3NameConversion.xlsm", "T3") ?>
      <td>Excel spreadsheet to convert germplasm names to T3 formatted names.
    </tr>
    <tr style= "border-top-style: solid; border-top-width: 1px;">
      <td><b>Phenotyping</b>
      <td><?php filelink("Traits", "trait_template.xls", "") ?>
      <td>Please discuss with the <a href="feedback.php">curators</a> before adding a new trait.
    </tr>
    <tr>
      <td>
      <td><?php filelink("Trial description", "TrialSubmissionForm.xls", "T3") ?>
      <td>Location, planting date, experimental design...
    </tr>
    <tr>
      <td>
      <td><?php filelink("Trial means", "PhenotypeSubmissionForm.xls", "T3") ?>
      <td>Values for all traits for test lines and checks, summary statistics
    </tr>
    <tr>
      <td>
      <td><?php filelink("Plot-Level Results", "PlotTemplate.xls", "T3") ?>
      <td>Trait values for each plot
    </tr>
    <tr>
      <td>
      <td><?php filelink("Fieldbook", "fieldbook_template.xlsx", "T3") ?>
      <td>Field map 
    </tr>
 <tr>
 <tr>
      <td><b>Canopy Spectral<br>Reflectance</b>
      <td><?php filelink("CSR System", "CSRinT3_SpectrometerSystem.xlsx", "T3") ?>
      <td>Instrument annotation
    </tr>
      <td>
      <td><?php filelink("CSR description", "CSRinT3_Sp1_Annotation.xlsx", "T3") ?>
      <td>Description of the CSR experiment
    </tr>
    <tr>
      <td>
      <td><?php filelink("CSR results", "CSR_Data_template.txt", "T3") ?>
      <td>Data file format
    </tr>
    <tr style= "border-top-style: solid; border-top-width: 1px;">
      <td><b>Genotyping</b>
      <td><?php filelink("Experiment description", "Geno_Annotation_Sample.txt", "") ?>
      <td>Platform, software, manifest file, experiment details...
    </tr>
    <tr>
      <td>
      <td><?php filelink("Line translation", "LinesTrialCode_Sample.txt", "") ?>
      <td>Line Name and Trial Code
    </tr>
    <tr>
      <td>
      <td><?php filelink("Illumina data", "TCAPbarley9K-sample.txt", "") ?>
      <td>alleles for lines and markers (coded as A or B)
    </tr>
    <tr>
      <td>
      <td><?php filelink("GBS data", "GBS_Genotype_template.txt", "") ?>
      <td>alleles for lines and markers (coded as ACTG, N = missing, H = heterozygous)
    </tr>
    <tr>
      <td>
      <td><a href="curator_data/gbs_import_instructions.php">GBS data, over 100K markers</a><td>07Nov14
      <td>instructions for loading large GBS data sets using the command line
    </tr>
    <tr>
      <td>
      <td><?php filelink("DArT data", "DArT_Genotype_template.txt", "") ?>
      <td>alleles for lines and markers (coded as 1 = present, 0 = absent)
    <tr>
      <td>
      <td><?php filelink("Experiment results (1D)", "genotypeData_T3.txt", "") ?>
      <td>1D table of alleles for lines and markers
    </tr>
    <tr>
      <td><b>Markers</b>
      <td><?php filelink("Sequence Generic", "Generic_SNP.txt", "") ?>
      <td>Marker sequences and allele definitions
    </tr>
    <tr>
      <td>
      <td><?php filelink("Sequence Illumina OPA", "SNP_assay.txt", "") ?>
      <td>Markers from Illumina Manifest, Golden Gate OPA format
    </tr>
    <tr>
      <td>
      <td><?php filelink("Sequence Illumina Infinium", "Marker_import_sample5.txt", "") ?>
      <td>Markers from Illumina Manifest, Infinium HD format
    </tr>
    <tr>
      <td>
      <td><?php filelink("Markers from DArT", "DArT.csv", "") ?>
      <td>Markers from Diversity Array Technologies
    <tr>
      <td>
      <td><?php filelink("Gene function", "Marker_import_sample4.txt", "") ?>
      <td>Sequence annotations and name synonyms
    </tr>
    <tr>
      <td>
      <td><?php filelink("Genetic Character markers", "property_template.xls", "T3") ?>
      <td>Trait-linked markers named for their associated gene or QTL
    <tr>
    </tr>
      <td>
      <td><?php filelink("Map location", "mapupload_example.txt", "") ?>
      <td>Genetic maps
    </tr>
  </table>
</div></div>

<?php
/** Date-stamp the template files, in red if they're new.
 * $subdir is relative to curator_data/examples/.
 */
function filelink($label, $filenm, $subdir)
{
    global $config;
    echo "<a href='".$config['base_url']."curator_data/examples/$subdir/$filenm'>$label</a></td><td>";
    $fullpath = $config['root_dir'] . "curator_data/examples/$subdir/$filenm";
    // Paint in red if newer than 30 days.
    if (time() - filemtime($fullpath) < 2592000) {
        echo "<font  color=red>". date("dMy", filemtime($fullpath)) . "</font>";
    } else {
        echo date("dMy", filemtime($fullpath));
    }
}

/** Variation of filelink(), for items in text instead of in the table.
 */
function filelink2($label, $filenm, $subdir)
{
    global $config;
    echo "<a href='".$config['base_url']."curator_data/examples/$subdir/$filenm'>$label</a></td><td>";
    // Add "(new <date>)" if newer than 30 days.
    $fullpath = $config['root_dir'] . "curator_data/examples/$subdir/$filenm";
    if (time() - filemtime($fullpath) < 2592000) {
        echo " <font size= -2 color=red>(new ". date("dMY", filemtime($fullpath)) . ")</font>";
    }
}

$footer_div=1;
require $config['root_dir'].'theme/footer.php';
