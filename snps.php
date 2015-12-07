<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();
require_once $config['root_dir'].'theme/normal_header.php';

if (isset($_SESSION['geno_exps'])) {
    $geno_exps = $_SESSION['geno_exps'];
    $count = $_SESSION['geno_exps_cnt'];
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
    $count = count($markers);
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
the documentation in Illumina&apos;s Technical
Note <a href="docs/TopBot_TechNote.pdf">"TOP/BOT" Strand and "A/B"
Allele</a>.  In short:<ul>
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
The Sequence shown in T3 is the reverse-complement of the the manifest&apos;s
<i>TopGenomicSeq</i> when the Illumina strand (manifest field "<i>Ilmn Strand</i>")
was BOT, so the the bracketed SNP nucleotides                                   
in the Sequence are the same as the A-allele and B-allele nucleotides; 
however they aren&apos;t necessarily given in the same order.

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
    echo "</div>";
    include_once $config['root_dir'].'theme/footer.php';
    die();
}
if ($count > 1000) {
    print "<br><br><a href=genotyping/display_markers.php>Download selected markers</a><br>\n";
    echo "</div>";
    include_once $config['root_dir'].'theme/footer.php';
    die();
}
print "<br><br><a href=genotyping/display_markers.php?function=download>Download marker information</a><br>\n";
echo "<br><table><tr><th>Marker<th>Type<th>A_allele<th>B_allele<th>Sequence";
if ($geno_exps != "") {
    $sql = "select marker_name, marker_type_name, A_allele, B_allele, sequence
    from markers, marker_types, allele_frequencies
    where markers.marker_type_uid = marker_types.marker_type_uid
    and markers.marker_uid = allele_frequencies.marker_uid
    and A_allele is not null
    and experiment_uid = $exp_uid
    order by marker_name";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<tr><td>".$row['marker_name']."<td>".$row['marker_type_name']."<td>".$row['A_allele']."<td>".$row['B_allele']."<td>";
        echo $row['sequence']."\n";
    }
    echo "</table>";
} elseif ($markers_str != "") {
    $sql = "select marker_name, marker_type_name, A_allele, B_allele, sequence
    from markers, marker_types 
    where markers.marker_type_uid = marker_types.marker_type_uid
    and marker_uid IN ($markers_str)
    order by marker_name";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<tr><td>".$row['marker_name']."<td>".$row['marker_type_name']."<td>".$row['A_allele']."<td>".$row['B_allele']."<td>";
        echo $row['sequence']."\n";
    }
    print "</table>";
} else {
    echo "Error: bad selection\n";
}
