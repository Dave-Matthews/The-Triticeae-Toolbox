<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header2.php';

?>
<title>Variant Effects</title>
<h1>Variant Effects</h1>
This tool determines the variant effect of markers loaded into T3 database. To use the report you must first select a list of markers (limit the selection to under 1000). It will accept a mix of markers from different genotype experiments or a single genotype experiment. The position of the markers on the genome assembly have been identified either by BLAST or from the coordinates provided when the genotype results were loaded into the database. If the marker position cannot be identified then it will be listed at the bottom of the page as not found.<br><br>
The 2017_WheatCAP genotype experiment has only been mapped to the RefSeq_v1 assembly. You also must login to the T3 website as a project participant to see information from the RefSeq_v1 assembly.<br><br>
 For the Wheat_TGACv1 assembly the gene link will take you to Ensembl Plant where they have precalculated the SIFT score for all the markers located at that gene.<br><br>

Example:<br>

<ol>
<li><a href="https://triticeaetoolbox.org/wheat/login.php">Login/Register</a>: This is necessary to view all assemblies.
<br><br>
<li><a href="https://triticeaetoolbox.org/wheat/genotyping/marker_selection.php">Select markers</a>: Go to Select->Markers.<br>
Scroll down to Select by genotyping platform. Select <b>GBS sequence capture</b>, select <b>2017_WheatCAP</b>, and then click the Select button.
<br><br>
<li><a href="https://triticeaetoolbox.org/wheat/pedigree/line_properties.php">Restrict the line selection</a>: Go to Select->Lines by Properties.<br>
In the Currently selected lines, highlight all but two lines, and then click on <b>Deselect highlighted lines</b>.
<br><br>
<li><a href="https://triticeaetoolbox.org/wheat/genotyping/pop-poly.php">Restrict the marker selection</a>: Go to Select->Subset by Marker polymorphisms.
Select a chromosome, start and stop position, then click the Query button. Then click on Save marker selection. If you have over 1000 markers it will not cause a problem, the list will just be truncated.
<br><br>
<li><a href="https://triticeaetoolbox.org/wheat/genotyping/variations.php">View Variant Effects</a>: Go to Reports->Variant Effects.
The marker column links to T3 pages describing the markers. The region column links to T3 JBrowse. The gene column links to a report showing results of the ensembl-vep program to determine consequence of all the variants for that gene.
</ol>
</html>
