<?php
require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';
connect();
require  $config['root_dir'].'theme/normal_header.php';
?>
<title>GBS import instructions</title>
<h1>GBS import instructions</h1>
<br>
These instructions are for importing genotype results from a Variant Call Format (VCF) file when there are over 100K markers.<br>
<br>
When a genotype experiment has over 100K markers it would be slow to retrieve the data when saved as consensus genotype experiment.<br>
These genotype experiments are saved as a text string containing all the measurements for a specific marker.<br>
The key for this text is a combination of the experiment uid and marker uid.<br>
Using the web interface of T3 these experiments can only be selected as a single genotype experiment.<br>
These large genotype experiments should be imported using the command line scripts as described below.<br><br>
1. Prepare the import files with one line for each marker and one column for each line. The files should be tab delimited.<br>
The first line of the file contains the line names. The first column of the files contains the marker names.<br>
The second column contains the chromosome. The third column contains the position.<br><br>
2. The VCF file contains the CHROM, POS, ID, REF, ALT<br>
<table>
<tr><td>CHROM<td>chromosome: An identifier from the reference genome<br>
<tr><td>POS<td>position: The reference position, with the 1st base having position 1<br>
<tr><td>ID<td>identifier: Semi-colon separated list of unique identifiers<br>
<tr><td>REF<td>reference base(s): Each base must be one of A,C,G,T,N<br>
<tr><td>ALT<td>alternate base(s): Comma separated list of alternate non-reference alleles called on at least one of the samples<br>
</table><br>
3. The first file is in TASSEL format and should be coded with ACTGN notation. Use script "get_genotype_tassel.pl".<br>
<table>
<tr><td>genotype from VCF<td>Import format
<tr><td>./.<td>N   N
<tr><td>0/0<td>REF REF 
<tr><td>0/1<td>REF ALT
<tr><td>1/1<td>ALT ALT
<tr><td>0/2<td>REF N 
<tr><td>1/2<td>ALT N
</table><br>
4. The second file is in R Script format and should be coded with -1,0,1. Use script "get_genotype_rrblup.pl".<br>
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
5. Load each of these file with the script <a href=scripts/load_gbs_bymarker.php>load_gbs_bymarker</a>.<br><br>
6. Calculate and load allele frequencies with the script <a href=scripts/load_gbs_frequencies.php>load_gbs_frequencies</a>.<br><br>
