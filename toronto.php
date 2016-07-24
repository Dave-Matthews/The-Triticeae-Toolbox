<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
include $config['root_dir'].'theme/admin_header.php';
?>

<style type=text/css>
p { width:90% }
</style>


<h2>Data Usage Policy</h2>

<p>In 2009 the Toronto International Data Release
Workshop agreed on a policy statement about prepublication data sharing
(<a href="http://www.nature.com/nature/journal/v461/n7261/full/461168a.html"><i>Nature</i> 
<b>461</b>, 168-170</a>).  Prepublication data release was
recommended for genetic association studies involving "genomewide
association analysis of thousands of samples", among other kinds of
studies.

<p>Accordingly, many of the datasets in T3 are being made available prior to
publication of a global analysis by the data producers.  Guidelines for
appropriate sharing of these data are given in the excerpt from
the <a href="http://www.nature.com/nature/journal/v461/n7261/box/461168a_BX1.html">Toronto
Statement</a> below.

<p><h3>Producers' information about specific datasets</h3>

<?php require 'toronto_table.html' ?>

<h3>Toronto Statement</h3>

<h4>Data producers should state their intentions and enable analyses of their data by:</h4>

<ul>
<li>Informing data users about the data being generated, data standards and quality, planned analyses, timelines, and relevant contact information, ideally through publication of a citable marker paper near the start of the project or by provision of a citable URL at the project or funding-agency website</li>
<li>Providing relevant metadata (e.g., questionnaires, phenotypes, environmental conditions, and laboratory methods) that will assist other researchers in reproducing and/or independently analysing the data, while protecting interests of individuals enrolled in studies focusing on humans</li>
<li>Ensuring that research participants are informed that their data will be shared with other scientists in the research community</li>
<li>Publishing their initial global analyses, as stated in the marker paper or citable URL, in a timely fashion</li>
<li>Creating databases designed to archive all data (including underlying raw data) in an easily retrievable form and facilitate usage of both pre-processed and processed data</li>

</ul>

<h4>Data analysts/users should freely analyse released prepublication data and act responsibly in publishing analyses of those data by:</h4>

<ul>
<li>Respecting the scientific etiquette that allows data producers to publish the first global analyses of their data set</li>
<li>Reading the citeable document associated with the project</li>
<li>Accurately and completely citing the source of prepublication data, including the version of the data set (if appropriate)</li>
<li>Being aware that released prepublication data may be associated with quality issues that will be later rectified by the data producers</li>
<li>Contacting the data producers to discuss publication plans in the case of overlap between planned analyses</li>
<li>Ensuring that use of data does not harm research participants and is in conformity with ethical approvals</li>

<li>Scientific journal editors should engage the research community about issues related to prepublication data release and provide guidance to authors and reviewers on the third-party use of prepublication data in manuscripts</li>
</ul>


</tr></table>


</tr></table>

<?php
$footer_div=1;
include $config['root_dir'].'theme/footer.php'; 
?>
