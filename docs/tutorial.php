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
software such as <a href="http://www.maizegenetics.net/tassel" target="_new">Tassel</a>, <a href="https://ics.hutton.ac.uk/flapjack/" target="_new">Flapjack</a>, or <a href="https://cran.r-project.org/" target="_new">R script</a>.  In addition
it includes pedigrees of the germplasm lines and map positions of the markers.
<p>
<h3>Topics:</h3>
<ol>
<li>Extracting genotype and phenotype data for external analysis
<li>Viewing maps
<li>Viewing pedigrees
</ol>

<p>
<h4>Extracting genotype and phenotype data for external analysis</h4>

The first step is to select Lines, Markers, and Traits. These can be selected from the <b>Quick Links</b> menu on the left
or the drop down menu on the top. If Markers or Genotype Experiments are not selected then you will get all the genotype data
for the selected lines with the consensus values. After selected you data you can then go to the drop down menu
item for Download / Genotype and Phenotype Data. Options are provide to download data in formats compatible with TASSEL,
R Script, and Flapjack. Extracting the data will take some time. Eventually you'll get a button at the bottom of the page
"Download Zip file of results". For Genotype consensus there will be four files: traits.txt, snpfile.txt/genotype.hmp.txt, allele_conflicts.txt, genotype_experiments.txt. 
For Genotype single experiment there will be two files: traits.txt and genotype.hmp.txt/genotype.vcf.

<h4>Viewing maps</h4>

The genetic and physical maps can be viewed from the drop down menu item Download / Genetic Maps. On this page first select the MapSet Name 
then select one of the Maps. At the bottom of the page there is a download link. You can also select a map from the drop down menu Select / Genetic Map. 
Then when you download genotype and Phenotype Data you will also get a map file with the position of each marker.

<h4>Viewing pedigrees</h4>

The Pedigree for a line record can be found by searching for the line in the <b>Quick search</b> field or the side menu. If more than one record is found you will need to select the "Line Record" Data Class. The pedigree record is in the "Pedigree string" field. To find the pedigree for more than one line, use the drop down menu item Select / Lines by Properties. Select the Passport data then click the search button. Then under the "Lines found" section click on the "Show line information" button.
</div></div>
<br><br>
<?php
  $footer_div=1;
include $config['root_dir'].'theme/footer.php';
