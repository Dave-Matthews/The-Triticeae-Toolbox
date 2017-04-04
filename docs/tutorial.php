<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
include $config['root_dir'].'theme/normal_header.php';
$mysqli = connecti();
$name = get_unique_name("datasets");
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>T3 Tutorial</h1>
T3 is a database of phenotypes and molecular alleles for a large set of germplasm
lines, to be extracted for association mapping of traits to markers using analysis 
software such as <a href="http://www.maizegenetics.net/tassel">Tassel</a>.  In addition
it includes pedigrees of the germplasm lines and map positions of the markers.
<p>
<h3>Topics:</h3>
<ol>
<li>Extracting genotype and phenotype data for external analysis
<li>Viewing maps
<li>Viewing pedigrees
<li>...
</ol>

<p>
<h4>Extracting genotype and phenotype data for external analysis</h4>

In the Quick Links menu on the left, click "Download Genotype/Phenotype Data (Tassel format)".
Choose any set of breeding programs, years, trials, and traits.  (Some of the menus don't appear
until you've selected items in the first menus.)  To select multiple items hold down the Ctrl key
while you click.
<p>
<img src="tutorial/TASSELdata.gif" alt="TASSELdata" border=1>
<p>
Then click the button below, "Download for Tassel".  Extracting the data will
take some time.  Please don't be patient, complain!  Eventually you'll get a popup box
"Do you want to open or save this file?" for a file in .zip format.  There should be three files,
traits.txt, snpfile.txt, and annotated_alignment.txt, suitable for loading into TASSEL.

<h4>Viewing maps</h4>
...
<?php 
  $footer_div=1;
include $config['root_dir'].'theme/footer.php';
