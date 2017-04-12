<?php
/**
 * Home page
 *
 * PHP version 5.3
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/index.html
 *
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap2.inc';
require $config['root_dir'].'theme/admin_header2.php';
$mysqli = connecti();
$name = get_unique_name("datasets");
?>

<h1>Welcome to The Triticeae Toolbox (T3)</h1>

<div style="font-size: 120%">
The Triticeae Toolbox (T3) is a repository for public wheat data. 
<a href="about.php">More details...</a>
</div>

<div id="primary" style="font-size: 120%">
<p><b>
<a href="explore.php">Explore T3</a></b></p>
<ul>
<p>Start to navigate the line information, phenotype trials, genotype experiments and genetic maps available in T3.</p>
</ul>
<p>
<p><b>
<a href="docs/tutorial.php">Select Data</a></b></p>
<ul>
Learn how to use the selection tools found under the "Select" menu, how to download this data and how to analyze it using one of the tools in the "Analyze" menu.
</ul>
<p><b>
<a href="curator_data/instructions.php">Submit Data</a></b></a>
<ul>
Find out how to upload data to T3 using the data submission templates.
</ul>
</div>
<br><br>
<div style="font-size: 120%">
Blake, V., Birkett, C.L., Matthews, D.E., Hane, D., Bradbury, P., Jannink, J. 2015. <a href="https://dl.sciencesocieties.org/publications/tpg/pdfs/0/0/plantgenome2014.12.0099" target="_new">The Triticeae Toolbox: Combining Phenotype and Genotype Data to Advance Small-Grains Breeding</a>. The Plant Genome. doi: 10.3835/PlantGenome2014.12.0099.
</div>
<?php
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
