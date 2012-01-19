<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Allele Conflicts</h1>
  <div class="section">
  <p>Lines that have been genotyped more than once for the same markers, with different results.
  Missing values ("--") are excluded.</p>

  <table>
    <tr>
      <th>Line</th>
      <th>Marker</th>
      <th>Alleles</th>
      <th>Experiment</th>
    </tr>

<?php
$query = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
from allele_conflicts a, line_records l, markers m, experiments e
where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and a.alleles != '--'
order by l.line_record_name, m.marker_name, e.trial_code";
$result = mysql_query($query) or die(mysql_error());
while ($row=mysql_fetch_row($result)) {
  print "<tr>";
  for ($i=0; $i<4; $i++) {
    print "<td>$row[$i]</td>";
  }
  print "</tr>";
}
?>
  </table>


  </div></div></div>
  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
