<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
?>
<h1>About THT</h1>

<h3>Data access policy</h3>
	<div class="section">
	<p>All data on <acronym title="The Hordeum Toolbox">THT</acronym> will be made public within six months of release by the <acronym title="The Hordeum Toolbox">THT</acronym> curators. During the six month period, the data will only be available to members of the Barley <acronym title="Coordinated Agricultural Project">CAP</acronym> Team unless explicitly released by the data creators.
	</div>

<h3>Software availability</h3>
<div class="section">
<p>  The THT software is available under the GNU General Public License (<a href="LICENSE">LICENSE</a>).
It will be distributed on <a href="http://github.com">github.com</a> soon (apr11).  It requires Unix, Apache, MySQL, and PHP.  Details are
in the <a href="INSTALL">INSTALL</a> document.
</div>

<h3>THT team</h3>
<div class="section">
<p>
<style type="text/css">
#thtteamtbl td{text-align:left}
.strong{font-weight:bold}
</style>
<table id="thtteamtbl" border="0" cellpadding="0" cellspacing="0">
<thead>
<tr>
<th>Name</th><th>Affiliation</th><th>Role</th>
</tr>
</thead>
<tbody>
<tr><td class="strong">Julie A. Dickerson</td><td>Electrical and Computer Engineering, Iowa State University</td><td>Principal Investigator</td></tr>
<tr><td class="strong">Roger P. Wise</td><td>USDA-ARS<br/>Department of Plant Pathology<br/>Iowa State University</td><td>Principal Investigator</td></tr>
<tr><td class="strong">Shreyartha Mukherjee</td><td>Bioinformatics and Computational Biology, Iowa State University</td><td>Developer/Bioinformatics</td></tr>
<tr><td class="strong">Kartic Ramesh</td><td>Computer Science, Iowa State University</td><td>Developer</td></tr>
<tr><td class="strong">Gavin Monroe</td><td>Software Engineering, Iowa State University</td><td>Developer</td></tr>
<tr><td class="strong">Ethan Wilder</td><td>Computer Engineering, Iowa State University</td><td>Developer (Alumnus)</td></tr>
<tr><td class="strong">Yong Huang</td><td>Bioinformatics and Computational Biology, Iowa State University</td><td>Developer/Bioinformatics (Alumnus)</td></tr>
<tr><td class="strong">Jennifer Kling</td><td>Dept. of Crop and Soil Science Oregon State University</td><td>Phenotype and pedigree data curation </td></tr>
<tr><td class="strong">Shiaoman Chao</td><td>USDA-ARS Biosciences Research Lab, Fargo, ND</td><td>SNP data production and curation</td></tr>
<tr><td class="strong">Tim Close</td><td>Botany and Plant Sciences<br/>University of California<br/>Riverside, CA</td><td>Assembly and SNP context information from <a href="http://harvest.ucr.edu/" title="">HarvEST: Barley</a></td></tr>
<tr><td class="strong">Peter Bradbury</td><td>USDA-ARS, Cornell University, Ithaca, NY</td><td>Pedigree information and links to <a href="http://www.maizegenetics.net/index.php?page=bioinformatics/tassel/index.html" title="">TASSEL</a></td></tr>
</tbody>
</table>
</p>
</div>


<h3>Collaborators</h3>
<div class="section">
<p>
<a href="http://bioinf.scri.ac.uk/germinate" title="">SCRI Germinate</a> Development Team (David Marshall, Paul Shaw)<br/>
<a href="http://www.plexdb.org/" title="">PLEXdb</a> Development Team at Iowa State University (Ethy Cannon and Sudhansu Dash)<br/>
<a href="http://www.gramene.org/" title="">Gramene</a> Database (Doreen Ware)<br/>
<a href="http://wheat.pw.usda.gov/" title="">GrainGenes</a> Database (David Matthews) USDA/ARS, Cornell University<br/>
</p>
</div>


</div>
<?php include($config['root_dir'].'theme/footer.php');?>
