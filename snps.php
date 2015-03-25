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
require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

if (isset($_SESSION['geno_exps'])) {
    $geno_exps = $_SESSION['geno_exps'];
    $exp_uid = intval($geno_exps[0]);
    $sql = "select trial_code from experiments where experiment_uid = $exp_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    if ($row = mysqli_fetch_assoc($res)) {
        $trial_code = $row['trial_code'];
        echo "<h2>Marker Alleles and Sequences for $trial_code</h2>";
    } else {
        $trial_code = "";
    }
} elseif (isset($_SESSION['clicked_buttons'])) {
    $markers = $_SESSION['clicked_buttons'];
    $markers_str = implode(",", $markers);
    echo "<h2>Marker Alleles and Sequences for selected markers</h2>";
} else {
    $geno_exps = "";
    $markers_str = "";
    echo "<h2>Marker Alleles and Sequences</h2>";
}

?>
<font size =-1>

Genotyping results are submitted to T3, and can be 
<a href="downloads/downloads.php">downloaded</a> from T3, in a variety
of formats.  To allow this flexibility, all types of markers are
represented internally in a common format.  In this format each marker
has two allele states (plus "missing data"), referred to as <i>A-allele</i> and
<i>B-allele</i>.  Below are the mapping rules used to convert from each
submission format to T3 format.  Following that are the nucleotide calls
corresponding to the A- and B-allele for each marker.  In each <i>Sequence</i>,
the SNP is given in square brackets as "[A-allele/B-allele]".

<h4>Illumina SNPs</h4>
Genotyping results from Illumina GoldenGate and Infinium assays are
submitted to T3 in Illumina's A/B format, and stored as A-allele = A and
B-allele = B.  The actual nucleotide calls corresponding to A and B are
taken from the Illumina manifest files
(<a href="docs/GS0007511-OPA.opa">example</a>), interpreted according to
the documentation in Illumina's Technical
Note <a href="docs/TopBot_TechNote.pdf">'"TOP/BOT" Strand and "A/B"
Allele'</a>.  In short:<ul>
<li>For [A/C] and [A/G] SNPs, Allele A is A.  
<li>For [T/C] and [T/G] SNPs, Allele A is T and the sequence is
designated BOT.
<li>For [A/T] SNPs, when the Illumina Strand
is TOP then Allele A = A and Allele B = T. When the
Strand is BOT, then Allele A = T and Allele B = A.
<li>For [C/G] SNPs, when the Illumina Strand is
TOP then Allele A = C and Allele B = G. When the Strand
is BOT then Allele A = G and Allele B = C.
</ul>
<br>The Sequence shown below is the reverse-complement of the the manifest's
<i>TopGenomicSeq</i> when the Illumina strand (manifest field "<i>Ilmn Strand</i>")
was BOT, so the A-allele and B-allele nucleotides match the bracketed SNP nucleotides
in the Sequence.

<h4>GBS SNPs</h4>
<p>For SNPs assayed by shotgun sequencing downstream from a restriction
site, the Sequence is oriented with the common restriction site at the
5-prime, left end.  The alternative nucleotides for this strand are
shown in square brackets in alphabetical order, A < C < G < T.  The first
nucleotide alphabetically is stored as A-allele, and the second is
B-allele.  Genotyping data are provided to T3 with a single-letter score,
the nucleotide, when homozygous; heterozygotes are indicated with "H", and
missing data with "N".


<h4>DArTs</h4>
<p>For DArT markers, Allele A is 1 (present) and Allele B is 0 (absent).


</font>

<?php
if (($trial_code == "") and ($markers_str == "")) {
    echo "<br><br><font color=red>Please select a <a href=downloads/select_genotype.php>genotype experiment</a> or";
    echo " <a href=genotyping/marker_selection.php>markers</a>."; 
    die();
}
?>

<pre>
<b>Marker,Type,A-allele,B-allele,Sequence</b>
<?php
if ($geno_exps != "") {
    $sql = "select marker_name, marker_type_name, A_allele, B_allele, sequence
    from markers, marker_types, allele_frequencies
    where markers.marker_type_uid = marker_types.marker_type_uid
    and markers.marker_uid = allele_frequencies.marker_uid
    and A_allele is not null
    and experiment_uid = $exp_uid
    order by marker_name";
} elseif ($markers_str != "") {
    $sql = "select marker_name, marker_type_name, A_allele, B_allele, sequence
    from markers, marker_types 
    where markers.marker_type_uid = marker_types.marker_type_uid
    and marker_uid IN ($markers_str)
    order by marker_name";
} else {
    die("Error: bad selection\n");
}
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['marker_name'].",".$row['marker_type_name'].",".$row['A_allele'].",".$row['B_allele'].",";
    echo $row['sequence']."\n";
}
