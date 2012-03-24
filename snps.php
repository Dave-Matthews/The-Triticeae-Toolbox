<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
<!-- <Content-Type: text/plain>
<Content-Disposition: inline; filename=THT_SNPs.txt>
-->
<title>SNPs_from_T3.txt</title>
<style type=text/css>
body { font-family: helvetica,arial,sans-serif; }
</style>
</head>
<body bgcolor=white>

<?php
require_once('config.php');
include_once($config['root_dir'].'includes/bootstrap.inc');
//include($config['root_dir'].'theme/new.css');
connect();
?>


<h3>Illumina SNP Alleles</h3>

<font size =-1>
Genotyping results from Illumina GoldenGate assays were submitted to T3
in Illumina's A/B format.  The actual nucleotide calls corresponding to
alleles A and B are given below for each marker.  The information is
taken from the Illumina manifest (.opa) files
(<a href="docs/GS0007511-OPA.opa">example</a>), interpreted according to
the documentation in
Illumina's <a href="docs/TopBot_TechNote.pdf">Technical Note</a>.  In 
short:
<li>To designate Allele for an [A/T] SNP, when the Illumina Strand
is TOP then Allele A = A and Allele B = T. When the
Strand is BOT, then Allele A = T and Allele B = A.
<li>To designate Allele for a [C/G] SNP, when the Illumina Strand is
TOP then Allele A = C and Allele B = G. When the Strand
is BOT then Allele A = G and Allele B = C.

<p>The Sequence shown below is the reverse-complement of the the manifest's
TopGenomicSeq when the Illumina strand (manifest field "Ilmn Strand")
was BOT, so the A and B nucleotides match the bracketed SNP nucleotides
in the Sequence.
</font>

<!-- Try comma-delimited:
<P>The table below is tab-delimited. Save this page as a .txt file and open it with Excel.<br>
<pre>
Marker	A allele	B allele	Sequence
-->
<pre>
Marker,A-allele,B-allele,Sequence
<?php
$sql = "select marker_name, A_allele, B_allele, sequence
from markers 
where A_allele is not null
order by marker_name";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($res)) {
  //echo $row['marker_name']."	".$row['A_allele']."	".$row['B_allele']."	".$row['sequence']."\n";
  echo $row['marker_name'].",".$row['A_allele'].",".$row['B_allele'].",".$row['sequence']."\n";
 }

?>
