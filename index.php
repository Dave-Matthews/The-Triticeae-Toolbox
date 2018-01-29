<?php
/**
 * Home page
 *
 * PHP version 5.3
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/index.html
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header2.php';
$mysqli = connecti();
$name = get_unique_name("datasets");
?>
<!--h2 style="color: Black">This USDA website will not be updated during a lapse in federal funding. Content on this website will not be current or maintained until funding issues have been resolved.</h2-->

<h1>Welcome to The Triticeae Toolbox (T3)</h1>

<div style="font-size: 120%">
The Triticeae Toolbox (T3) is a repository for public wheat data generated by the Wheat Coordinated Agricultural Project (<a href="http://www.triticeaecap.org/" target="_new">Wheat CAP</a>).
Funding is provided by the National Institute for Food and Agriculture (<a href="https://nifa.usda.gov/" target="_new">NIFA</a>) and the United States Department of Agriculture (<a href="https://www.usda.gov/" target="_new">USDA</a>). 
The current project is funded through NIFA's International Wheat Yield Partnership (<a href="http://iwyp.org" target="_new">IWYP</a>) and part of the Agriculture and Food Research Initiative (<a href="https://nifa.usda.gov/program/agriculture-and-food-research-initiative-afri">AFRI</a>). 
A Project Description, T3 Team, and Collaborators are <a href="about.php">described here</a>.
</div>

<div id="primary" style="font-size: 120%">
<p><b>
<a href="explore.php">Explore T3</a></b></p>
<ul>
<p>Start to navigate the line information, phenotype trials, genotype experiments and genetic maps available in T3.</p>
</ul>
<p>
<p><b>
<a href="docs/tutorial.php">How to Select Data</a></b></p>
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
Blake, V., Birkett, C.L., Matthews, D.E., Hane, D., Bradbury, P., Jannink, J. 2015. <a href="https://dl.sciencesocieties.org/publications/tpg/pdfs/0/0/plantgenome2014.12.0099" target="_new">The Triticeae Toolbox: Combining Phenotype and Genotype Data to Advance Small-Grains Breeding</a>. The Plant Genome. doi: 10.3835/PlantGenome2014.12.0099.<br>
The T3 software is open source and available under the GNU General Public License
(<a href="docs/LICENSE">LICENSE</a>) and may be downloaded from
<a href="https://github.com/TriticeaeToolbox/T3" target="_new">GitHub</a>.
</div>
<?php
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
