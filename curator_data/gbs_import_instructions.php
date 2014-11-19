<?php
require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';
connect();
require  $config['root_dir'].'theme/normal_header.php';
?>
<title>GBS import instructions</title>
<h1>Genotype by Sequencing (GBS) Data, over 100K markers</h1>
How large GBS experiments are processed differently from other genotype data.
<ul>
<li>Genotype data is stored as a text string containing all the measurements for a specific marker.<br>
<li>Genotype data is converted into two formats for quick retrieval (ACTG and -1,0,1).<br>
<li>The chromosome and position information is stored in the same table as the genotype data.<br>
<li>The genotype files (VCF) are imported from the command line instead of through the web interface.<br>
</ul>
How to select large GBS experiments using T3 website
<ul>
<li>GBS experiments are selected by using "Select Lines by Genotype Experiment".
<li>When a genotype experiment is selected the associated lines and marekrs are also selected.
</ul>
Instructions for importing genotype results from a Variant Call Format (VCF) file when there are over 100K markers.
<ol>
<li>Prepare the import files with one line for each marker and one column for each line. The files should be tab delimited.<br>
The first line of the file contains the line names. The first column of the files contains the marker names.<br>
The second column contains the chromosome. The third column contains the position.<br><br>
<li>The VCF file contains the CHROM, POS, ID, REF, ALT<br>
<table>
<tr><td>CHROM<td>chromosome: An identifier from the reference genome<br>
<tr><td>POS<td>position: The reference position, with the 1st base having position 1<br>
<tr><td>ID<td>identifier: Semi-colon separated list of unique identifiers<br>
<tr><td>REF<td>reference base(s): Each base must be one of A,C,G,T,N<br>
<tr><td>ALT<td>alternate base(s): Comma separated list of alternate non-reference alleles called on at least one of the samples<br>
</table><br>
<li>The first file is in TASSEL format and should be coded with ACTGN notation. Use script "get_genotype_tassel.pl".<br>
<table>
<tr><td>genotype from VCF<td>Import format
<tr><td>./.<td>N   N
<tr><td>0/0<td>REF REF 
<tr><td>0/1<td>REF ALT
<tr><td>1/1<td>ALT ALT
<tr><td>0/2<td>REF N 
<tr><td>1/2<td>ALT N
</table><br>
<li>The second file is in R Script format and should be coded with -1,0,1. Use script "get_genotype_rrblup.pl".<br>
<table>
<tr><td>genotype from VCF<td>Import format
<tr><td>./.<td>NA
<tr><td>0/1<td>0
<tr><td>1/0<td>0
<tr><td>0/0<td>1
<tr><td>1/1<td>-1
<tr><td>0/2<td>NA
<tr><td>1/2<td>NA
</table><br>
<li>Load each of these files with the script <a href=scripts/load_gbs_bymarker.php>load_gbs_bymarker</a>.<br><br>
<li>Calculate and load allele frequencies with the script <a href=scripts/load_gbs_frequencies.php>load_gbs_frequencies</a>.<br><br>
</ol>
