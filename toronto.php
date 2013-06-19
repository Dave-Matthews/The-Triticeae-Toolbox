<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<h2>Data Usage Policy</h2>

The data on this database is shared according to the Toronto agreement (see below). 

Datasets that are currently released but have not yet been published include:<br /><br />

<table>
  <tr><th>Description<th>Source<th>Datasets<th>Date submitted
  <tr>
    <td>9K wheat iSelect assay
    <td>Eduard Akhunov
    <td><a href="<?php echo $config[base_url] ?>search.php?keywords=NSGCwheat9K">NSGCwheat9K*</a>
      <br>[link is now functional, though wheatplus doesn't have these datasets.]
    <td>Oct 2011
  <tr>
    <td>Stem solidness trial
    <td>Luther Talbert
    <td><a href="http://triticeaetoolbox.org/wheat/display_phenotype.php?trial_code=6x4x_2012_Bozeman">6x4x_2012_Bozeman</a>
      <br>[Clicking when not signed in should give a 
      <br>"private" message instead of blank page.]
    <td>May 2013
</table>


<h3>Toronto Agreement</h3>

<h4>Rapid prepublication data release should be encouraged for projects with the following attributes:</h4>

<ul>
<li>Large scale (requiring significant resources over time)</li>
<li>Broad utility</li>
<li>Creating reference data sets</li>

<li>Associated with community buy-in</li>
</ul>

<h4>Funding agencies should facilitate the specification of data-release policies for relevant projects by:</h4>

<ul>
<li>Explicitly informing applicants of data-release requirements, especially mandatory prepublication data release</li>
<li>Ensuring that evaluation of data release plans is part of the peer-review process</li>
<li>Proactively establishing analysis plans and timelines for projects releasing data prepublication</li>
<li>Fostering investigator-initiated prepublication data release</li>
<li>Helping to develop appropriate consent, security, access and governance mechanisms that protect research participants while encouraging prepublication data release</li>

<li>Providing long-term support of databases</li>
</ul>

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
include($config['root_dir'].'theme/footer.php'); 
?>
