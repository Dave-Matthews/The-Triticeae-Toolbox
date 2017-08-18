<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

$rest = $_REQUEST[restrict];
$dl = $_REQUEST[download];
if (empty($rest)) {
    $rest = "No";
}
// Show conflicts only for Currently Selected Lines?
if ($rest == 'Yes') {
    $lineids = implode(",", $_SESSION['selected_lines']);
    if (empty($lineids)) {
        $lineids = "''";
    }
    $restriction = "and l.line_record_uid in ($lineids)";
}
$query = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
from allele_conflicts a, line_records l, markers m, experiments e
where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and a.alleles != '--'
  $restriction
order by l.line_record_name, m.marker_name, e.trial_code";

// Downloading?
if (!empty($_REQUEST[download])) {
    header('Content-disposition: attachment;filename=allele_conflicts.csv');
    header('Content-Type: text/csv');
    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    print "Line,Marker,Alleles,Experiment\n";
    while ($row=mysqli_fetch_row($result)) {
        $rowstring = implode(",", $row);
        print $rowstring."\n";
    }
} else {
    include $config['root_dir'].'theme/admin_header.php';
?>

<style type=text/css>
table td {padding-top:1px; padding-bottom:1px}
table th {text-align:left}
</style>

  <h1>Allele Conflicts</h1>
  <div class="section">
  <form>
    Lines that have been genotyped more than once for the same markers, with different results.
    Missing values ("--") are excluded.<br>
    Restrict to <a href="<?php echo $config[base_url] ?>pedigree/line_properties.php">currently selected lines</a>? 
    <input type=submit name=restrict value=Yes> 
    <input type=submit name=restrict value=No>
  </form>
  <form>
    <input type=hidden name=restrict value=<?php echo $rest ?>>
    <input type=submit name=download value=Download>
  </form>
  <p>
  <table>
    <tr>
      <th>Line</th>
      <th>Marker</th>
      <th>Alleles</th>
      <th>Experiment</th>
    </tr>

<?php
$result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
while ($row=mysqli_fetch_row($result)) {
    print "<tr>";
    for ($i=0; $i<4; $i++) {
        print "<td>$row[$i]</td>";
    }
    print "</tr>";
}
print "</table>";

print "</div>";
$footer_div=1;
include $config['root_dir'].'theme/footer.php';
}
?>
