<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header2.php';

?>
<title>BLAST Analysis</title>
<h1>BLAST Analysis</h1>

The BLAST page allows you to view the blast hits in JBrowse. Previously you were given the coordinates and you would have to find the  JBrowse with the correct reference assembly and manually copy the coordinates.<br>

The available BLAST database and access restrictions are described below and on the <a href=https://triticeaetoolbox.org/wheat/viroblast/docs/blast_databases.html>Database(s)</a> link on the BLAST page.<br><br>

<table>
<tr><td><b>Database</b><td>Use<td>Results Link
<tr><td>Wheat Markers in T3
<td>find T3 markers that match query sequence
<td>Marker Report page in T3
<tr><td>Wheat TGACv1
<td>view genome aligned to Ensembl Plant
<td>Ensembl Plant
<tr><td>Wheat Pangenome
<td>view genome aligned to Wheat Pangenome
<td>GBrowse
<tr><td>RefSeq_v1 (login required)
<td>view genome aligned to RefSeq_v1
<td>T3 JBrowse and URGI JBrowse (login required)
</table><br>
Example:
<ol>
<li><a href=https://triticeaetoolbox.org/wheat/login.php>Login</a> to T3 Wheat.
<li>Past in the sequence of<br>
<pre style="font-size: 12px">>AGACGGTGCCCTAGACCCTGTTGTATCCCACGTTTTTACTGAACAGGCGGCGGATGTAAACGACTTGCAACGCACTGGCGGCCATGAAAGCCGCGTACTCT</pre>
<li>Select the RefSeq_v1 database</li>
<li>Select <b>Basic search</b>
<li>On the BLAST results page select chr5A under the T3 JBrowse column
<li>The BlastHSP Results track show you the BLAST match
<li>The Variations in T3 track shows you nearby markers
<li>The gene model HC track shows high confidence gene predictions

</ol>
</html>
