<?php
require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';
require  $config['root_dir'].'theme/normal_header.php';
?>
<title>GBS import instructions</title>
<h1>Genotype by Sequencing (GBS) Data, over 100K markers</h1>
<b>Genotype Experiment</b>
<ul>
<li>For loading into  the T3 website the submitter should include a description of the experiment, how the GBS data was processed, a marker load file,<br>
and a genotype results file.
<li>Additional files like the TagsByTaxa and VCF can be included as raw files and will be available for download from the T3 website.
</ul>

<b>Markers aligned to reference</b>
<div style="width:80%">
<ul>
<li>The  marker_name and sequence should be unique.
<li>The marker sequence should be checked for synonyms to existing entries in the database (there is a BLAST tool to check sequence synonyms on the import page).
<li>We will BLAST newly submitted marker flanking sequences to flanking sequences previously submitted. For sequence pairs that are identical along<br>
 the full length of the shorter of the two sequences, we will assume identity of the SNP.  Thus, the newly submitted marker will become a synonym of<br>
 the previously submitted marker.
<li>The sequence for each marker should be long enough to uniquely define the marker within the genome. For the wheat genome anchored to the IWGSC assembly we use a marker sequence of 128 bases.
<li>file format - comma separated
<pre style="overflow: auto">
WCSS1_marker_name,marker_type,A_allele,B_allele,sequence
WCSS1_contig3917765_1al-5470,GBS,G,A,GCCGGACTGAGGCGGCAACTTGATGCGGCGGATGCCAACATTGCGCTTGTGAACAAGCGGCTTG[G/A]CGAGGCACAGGGTATGTATTTTCGGGTGGTCAACAAATATTAAGAGGAGCATGATGCTAGTAT
WCSS1_contig3917765_1al-5481,GBS,G,T,GCGGCAACTTGATGCGGCGGATGCCAACATTGCGCTTGTGAACAAGCGGCTTGGCGAGGCACAG[G/T]GTATGTATTTTCGGGTGGTCAACAAATATTAAGAGGAGCATGATGCTAGTATCTATAATATGC
WCSS1_contig3917765_1al-5493,GBS,C,T,TGCGGCGGATGCCAACATTGCGCTTGTGAACAAGCGGCTTGGCGAGGCACAGGGTATGTATTTT[C/T]GGGTGGTCAACAAATATTAAGAGGAGCATGATGCTAGTATCTATAATATGCTGTGACTGCAGA
</pre>
<li>fields<pre>
marker_name = valid characters are alphanumeric and “_-.“
marker_type = GBS
A_allele = reference allele
B_allele = alternate allele
sequence = ACTG, the SNP should be embedded in the sequence with the reference allele first and the alternate allele second</pre>
</ul>
</div>

<b>Markers not aligned to reference</b>
<ul>
<li>The  marker_name and sequence should be unique.
<li>The A and B alleles should be ordered alphabetically (there is a tool to order the alleles on the import page).
<li>The marker sequence should be check for synonyms to existing entries in the database (there is a BLAST tool to check sequence synonyms on the import page).
<li>We will BLAST newly submitted marker flanking sequences to flanking sequences previously submitted. For sequence pairs that are identical along the full length<br>
of the shorter of the two sequences, we will assume identity of the SNP.  Thus, the newly submitted marker will become a synonym of the previously submitted marker.
<li>The sequence for each marker should be long enough to uniquely define the marker within the genome. For markers not anchored to a reference assembly<br>
 the markers are typically less than 64 bases.
<li>file format - comma separated<pre>
marker_name,marker_type,A_allele,B_allele,sequence
gbsCNLmaster1,GBS,A,G,TGCAGAAAAAAAAACT[A/G]CAATAAGACATGTGTTGTGATGGTGGAGGGTGCCGCTCGGCCATTCG
gbsCNLmaster2,GBS,A,G,TGCAGAAAAAAAAACTACAATAAG[A/G]CATGTGTTGTGATGGTGGAGGGTGCCGCTCGGCCATTCG
gbsCNLmaster3,GBS,C,G,TGCAGAAAAAAAACAACT[C/G]GCAGGTTCTCAAAGTAGGATCCAGAAGACTCAGGGAGGTGGCCGC
gbsCNLmaster4,GBS,A,G,TGCAGAAAAAAAACTGTTAGACACGTGTAAATGTAGAACCAATTGATTGGATGCAC[A/G]AGGAAGG
</pre>
<li>fields<pre>
marker_name = valid characters are alphanumeric and “_-.“
marker_type = GBS
A_allele = ACTG 
B_allele = ACTG 
sequence = ACTG, the SNP should be embedded in the sequence with the reference allele first and the alternate allele second</pre>
</ul>

<b>Genotype Results</b>
<ul>
<li>The import file is tab delimited similar to HapMap.
<li>The columns contain the lines and the rows contain the markers.
<li>Each cell of the matrix should be an IUPAC nucleotide (A, T, C, G) for a homozygote or (K, Y, W, S, R, M) for a heterozygote, and "N" for missing data.
<li>The Chrom and Pos fields can be blank if not available.
<li>Genotype files with over 100K markers should be imported via the command line as described in the GBS import instructions
<li>file format - tab separated<pre>
SNP     Chrom	Pos	2174-05 2180    Above   Agate   Alice   Alliance
WCSS1_contig3917765_1AL-5470    1AL     5905    N      A      N      N
WCSS1_contig3917765_1AL-5481    1AL     5916    N      G      G      T
WCSS1_contig3917765_1AL-5493    1AL     5928    N      C      C      C</pre>
<li>fields<pre>
SNP = identifier
Chrom = chromosome that SNP maps to (optional)
Pos = chromosome position of the SNP (optional), for example the position on the map of ordered contigs
Col4 and on = observed genotypes of samples</pre>
</ul>
<b>Command line import instructions</b>
<ol>
<li>Load file for tassel and rrBLUP format with the script <a href=scripts/>load_gbs_bymarker</a>.
<li>Calculate and load allele frequencies with the script <a href=scripts/>load_gbs_frequencies</a>.
</ol></div>
<?php
require  $config['root_dir'].'theme/footer.php';
