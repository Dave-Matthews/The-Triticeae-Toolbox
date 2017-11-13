<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

include $config['root_dir'].'theme/admin_header.php';

if (!isset($browserLink)) {
    echo "Error: Please define EnsemblLink in directory config.php file";
}

$sql = "select experiment_uid, trial_code from experiments, experiment_types
   where experiments.experiment_type_uid = experiment_types.experiment_type_uid
   order by experiment_uid desc";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row=mysqli_fetch_row($result)) {
    $uid = $row[0];
    $name = $row[1];
    $trial_name_list[$uid] = $name;
}

echo "<h2>Marker Annotation Report</h2>\n";
echo "This analysis shows the results of using  BLAST to match the markers sequence in the T3 database to the the wheat genome sequence.<br>\n";
echo "The BLAST results link to GBrowse URGI and Ensembl genome browsers. The top level page gives a summary of the matching markers for each experiment.<br>\n";
echo "Select the match link to view the BLAST hits for each experiment.<br><br>\n";
echo "Methods: A marker is identified as a match if either<br>\n";
echo "1. the sequence homology is > 99% and aligment length is > 95% of the query sequence.<br>\n";
echo "2. there is only one mismatch and the alignment length is > 95% of the query sequence.<br>\n";
echo "The analysis uses blastn v2.2.28+ with the following arguments \"-outfmt 6 -dust no -word_size 16 -task megablast -evalue 1e-08\".<br><br>\n";

if (isset($_GET['uid'])) {
    $uid = intval($_GET['uid']);
    $assembly = $_GET['asm'];
    echo "BLAST matches for experiment $trial_name_list[$uid]<br>\n";
    echo "Select link to view match in Ensembl Plant.<br><br>\n";
    echo "<table>\n";
    echo "<tr><td>Query<td>Reference Contig<td>position\n";

    $sql = "select marker_name, chrom, bin, pos from marker_report_reference
      inner join allele_frequencies af1
      on marker_report_reference.marker_uid = af1.marker_uid
      and af1.experiment_uid = $uid
      and assembly_name = \"$assembly\"
      order by marker_name";
    $count = 0;
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_row($result)) {
        $marker1_name = $row[0];
        $chrom = $row[1];
        $contig = $row[2];
        $pos = $row[3];
        if (preg_match("/IWGS/", $assembly)) {
            $jbrowse = "<tr><td>$marker1_name<td><a href=\"" . $browserLink[$assembly] . "$contig:$pos\" target=\"_blank\">$contig<td>$pos";
        } else {
            $jbrowse = "<tr><td>$marker1_name<td><a href=\"" . $browserLink[$assembly] . "$chrom:$pos\" target=\"_blank\">$chrom<td>$pos";
        }
        echo "$jbrowse\n";
    }
    echo "</table>";
} else {
    $sql = "select experiment_uid, count(*) from allele_frequencies
    group by experiment_uid";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_row($result)) {
        $uid = $row[0];
        $count = $row[1];
        $total_marker_list[$uid] = $count;
    }

    $assembly_list = array();
    $sql = "select distinct(assembly_name) from marker_report_reference";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_row($result)) {
        $assembly_list[] = $row[0];
    }

    $out_list = array();
    foreach ($assembly_list as $asm) {
        $sql = "select experiment_uid, count(distinct(marker_report_reference.marker_uid)), count(allele_frequencies.marker_uid) from marker_report_reference, allele_frequencies
        where marker_report_reference.marker_uid=allele_frequencies.marker_uid
        and assembly_name = '$asm'
        group by experiment_uid order by experiment_uid desc";
        $count = 1;
        $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row=mysqli_fetch_row($result)) {
            $uid = $row[0];
            $count1 = $row[1];
            $count2 = $row[2];
            $total = $total_marker_list[$uid];
            $perc = round(100*$count1/$total, 0);
            if (!isset($out_list[$uid])) {
                $out_list[$uid] = $total;
            }
            $out_list[$uid] .= "<td><a href=genotyping/marker_report_ref.php?uid=$uid&asm=$asm>$count1</a> ($perc%)";
            $count++;
        }
    }

    echo "<table>";
    echo "<tr><td>Experiment<td>total markers";
    foreach ($assembly_list as $asm) {
        echo "<td>$asm";
    }

    foreach ($out_list as $uid => $val) {
        if (isset($trial_name_list[$uid])) {
            echo "<tr><td>$trial_name_list[$uid]";
        } else {
            echo "<tr><td>UID = $uid";
        }
        echo "<td>$out_list[$uid]";
    }
}
echo "</table></div>";
include $config['root_dir'].'theme/footer.php';
