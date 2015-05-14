<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
//connect();
?>
<style type="text/css">
  pre {font-size: 1.1em}
  #main h2 {color:#de5414; font-size: 1.4em; }
</style>

<h1>T3 Web Application Programming Interface</h1>
<div class="section">

  <h1>Breeding API</h1>
  See the documentation at <a href="http://docs.breeding.apiary.io/">http://docs.breeding.apiary.io/</a><p>

  <h2>Markerprofile</h2>

  <h3>Count</h3>
  Allele Count for a Germplasm, by Run<br>
  <b>GET</b> /brapi/v1/markerprofile/{id}/count?analysisMethod={platform}<br>
  <b>Returns:</b> JSON containing Germplasm Id, experiment Id, analysis method, and number of allele calls<br>
  <b>Example:</b> <a href="http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/markerprofile/993_84/count?analysisMethod=GoldenGate">http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/markerprofile/993_84/count?analysisMethod=GoldenGate</a>

  <h3>Alleles By Germplasm Id</h3>
  <b>GET</b> /brapi/v1/markerprofile/{id}?runId={runId}&analysisMethod={method}&pageSize={pageSize}&page={page}<br>
  <b>Returns:</b> JSON containing Germplasm Id, experiment Id, analysis method, encoding, and allele calls<br>
  &nbsp;&nbsp;&nbsp;by marker.  Missing data are omitted.  Analysis method is T3's "platform."<br>
  <b>Example:</b> <a href="http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/markerprofile/993_84">http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/markerprofile/993_84</a><br>
  <br>

  <h2>Germplasm</h2>

  <h3>Germplasm ID by Name</h3>
  <b>GET</b> /brapi/v1/germplasm/find?q={name}&matchMethod={matchMethod}&include={synonyms}&page={page}&pageSize={pageSize}<br>
  <b>Example: </b><a href="http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/germplasm/find?q=STEPTOE">http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/germplasm/find?q=STEPTOE</a>
  <br><br>

  <h2>Field Data Collection</h2>

  <h3>List Study Summaries</h3>
  Experiment Design<br>
  <b>GET</b> http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/study<br>
  <b>Example:</b> <a href="http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/study">http://malt.pw.usda.gov/t3/wheatplus/brapi/v1/study</a>

<p>
</div>

<div class="section">

  <h2>Ideas</h2>

  <h3>Keyword search ("Quick search")</h3>
  URL: http://triticeaetoolbox.org/wheat/search.php?keywords=&lt;query><br>
  Method: GET<br>
  Returns: List of href links, HTML format<br>
  Example: <a href="http://triticeaetoolbox.org/wheat/search.php?keywords=iwa55">http://triticeaetoolbox.org/wheat/search.php?keywords=iwa55</a><br>


  <h3>Retrieve description of a Line (germplasm accession, taxon)</h3>
  URL: http://triticeaetoolbox.org/wheat/view.php?table=line_records&name=&lt;name><br>
  Method: GET<br>
  Returns: Report page, HTML format<br>
  Example: <a href="http://triticeaetoolbox.org/wheat/view.php?table=line_records&name=caledonia">http://triticeaetoolbox.org/wheat/view.php?table=line_records&name=caledonia</a><br>


  <h3>Retrieve description of a Marker</h3>
  URL: http://triticeaetoolbox.org/wheat/view.php?table=markers&name=&lt;name><br>
  Method: GET<br>
  Returns: Report page, HTML format<br>
  Example: <a href="http://triticeaetoolbox.org/wheat/view.php?table=markers&name=iwa55">http://triticeaetoolbox.org/wheat/view.php?table=markers&name=iwa55</a>
  <p>

  <h3>Arbitrary SQL SELECT query (cf. <a href="http://wheat.pw.usda.gov/cgi-bin/graingenes/sql.cgi">GrainGenes</a>)</h3>
  URL: http://triticeaetoolbox.org/wheat/api/sql.php?q=&lt;query><br>
  Method: GET<br>
  Parameter: &lt;query> = SELECT..FROM..WHERE statement, according to the <a href="http://triticeaetoolbox.org/wheat/docs/T3wheat_schema.sql">T3 schema</a>, as URL-encoded string<br>
  Returns: JSON Array (rows) of [Array (columns) of [Object: {"column name":"column value for that row"}]]<br>
  Example: http://triticeaetoolbox.org/wheat/api/sql.php?q=select+line_record_name%2C+pedigree_string+from+line_records<br>
  Sample output:
  <pre>
    [
    [{"line_record_name":"CAYUGA"},{"pedigree_string":"GENEVA/CLARK\222SCREAM//GENEVA"}],
    [{"line_record_name":"CIMMYT-A01"},{"pedigree_string":"SERI*3//RL6010/4*YR/3/PASTOR/4/BAV92"}],
    [{"line_record_name":"RED_FIFE"},{"pedigree_string":"Land race- unknown parentage?"}],
    [{"line_record_name":"VISTA"},{"pedigree_string":"Warrior//Atlas66/Comanche/3/Comanche/Ottawa (NE68513)/5/(NE68457) Ponca/2*Cheyenne/3/Illinois No. 1//2* Chinese Spring/T. timopheevii/4/Cheyenne/Tenmaq// Mediterranean/Hope/3/Sando60/6/Centurk/Brule"}],
    ...
    ]

  </pre>

  </div>
<?php 
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
