<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<style type=text/css>
  table th { border-top: 2px solid #9a292c; }
</style>

<h1>Property Descriptions</h1>
<div class="section">
  <table>

<?php
$cats = mysql_query("select phenotype_category_uid, phenotype_category_name from phenotype_category order by phenotype_category_name");
while ($row = mysql_fetch_array($cats)) {
  $catid = $row['phenotype_category_uid'];
  $catname = $row['phenotype_category_name'];
  $res = mysql_query("select properties_uid, name, description from properties
	  where phenotype_category_uid = $catid order by name");
  if (mysql_num_rows($res) > 0) {
    print "<tr><th>$catname<th>Description<th>Values";
    while ($prow = mysql_fetch_array($res)) {
      $propid = $prow['properties_uid'];
      $name = $prow['name'];
      $desc = $prow['description'];
      $vals = array();
      $valres = mysql_query("select value from property_values where property_uid = $propid");
      while ($vrow = mysql_fetch_row($valres))
	$vals[] = $vrow[0];
      $vallist = implode(',', $vals);
      print "<tr><td>$name<td>$desc<td style=text-align:center>$vallist";
    }
  }
}
print "</table>";
print "</div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>

