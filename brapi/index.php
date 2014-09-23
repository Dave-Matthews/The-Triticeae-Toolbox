<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>
<style type="text/css">
  pre {font-size: 1.1em}
</style>

<h1>T3 Web Application Programming Interface</h1>
<div class="section">

  <h1>Breeding API</h1>
  See the documentation at <a href="http://docs.breeding.apiary.io/">http://docs.breeding.apiary.io/</a>

  <h3>Allele Count By Germplasm Id</h3>
  <b>GET</b> /brapi/0.1/genotype/{id}/count?analysisMethod={platform}<br>
  <b>Returns:</b> JSON containing Germplasm Id, experiment Id, analysis method, and number of allele calls<br>
  <b>Example:</b> <a href="http://malt.pw.usda.gov/t3/wheatplus/brapi/0.1/genotype/993/count?analysisMethod=GoldenGate">http://malt.pw.usda.gov/t3/wheatplus/brapi/0.1/genotype/993/count?analysisMethod=GoldenGate</a>

  <h3>Alleles By Germplasm Id</h3>
  <b>GET</b> /brapi/0.1/genotype/{id}?runId={runId}&analysisMethod={method}&pageSize={pageSize}&page={page}<br>
  <b>Returns:</b> JSON containing Germplasm Id, experiment Id, analysis method, encoding, and allele calls<br>
  &nbsp;&nbsp;&nbsp;by marker.  Missing data are omitted.  Analysis method is T3's "platform."<br>
  <b>Example:</b> <a href="http://malt.pw.usda.gov/t3/wheatplus/brapi/0.1/genotype/993?analysisMethod=GoldenGate&pagesize=2000&page=1">http://malt.pw.usda.gov/t3/wheatplus/brapi/0.1/genotype/993?analysisMethod=GoldenGate&pagesize=2000&page=1</a>

  <h3>Experiment Design, for Fieldbooks</h3>
  <b>GET</b> http://malt.pw.usda.gov/cbirkett/t3/wheatplus/brapi/0.1/design/list
  <b>Example:</b> <a href="http://malt.pw.usda.gov/cbirkett/t3/wheatplus/brapi/0.1/design/list">http://malt.pw.usda.gov/cbirkett/t3/wheatplus/brapi/0.1/design/list</a>

<p>
</div>

<div class="section">
  <h1>Other</h1>
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

</div>

<div class="section">

  <h1>Ideas</h1>

  <h3>Retrieve all Allele data for a Line</h3>
  URL: http://triticeaetoolbox.org/wheat/api/allelesbyline.php?name=&lt;name><br>
  Method: GET<br>
  Parameter: &lt;name> = T3 primary name or T3 synonym<br>
  Returns: JSON Array of [String: "T3 primary name", Object: {"Marker name":"corresponding consensus Allele call"}]<br>
  Example: http://triticeaetoolbox.org/wheat/api/allelesbyline.php?name=caledonia<br>
  Sample output: 
  <pre>
    ["CALEDONIA",
    {
    "BobWhite_c10015_641":"B,B"
    "BobWhite_c10016_302":"A,A"
    ...
    "Kukri_c65663_642":"-,-"
    ...
    "wsnp_CF133109A_Ta_2_1":"A,A"
    }]
  </pre>

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
