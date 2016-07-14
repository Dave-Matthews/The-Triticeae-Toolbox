<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header.php';
$mysqli = connecti();
?>

<style type=text/css>
  table th { border-top: 2px solid #9a292c; }
</style>

<h1>Property Descriptions</h1>
<div class="section">
  <table>

<?php
$cats = mysqli_query($mysqli, "select phenotype_category_uid, phenotype_category_name from phenotype_category order by phenotype_category_name");
while ($row = mysqli_fetch_array($cats)) {
    $catid = $row['phenotype_category_uid'];
    $catname = $row['phenotype_category_name'];
    $res = mysqli_query($mysqli, "select properties_uid, name, description from properties
	  where phenotype_category_uid = $catid order by name");
    if (mysqli_num_rows($res) > 0) {
        print "<tr><th>$catname<th>Description<th>Values";
        while ($prow = mysqli_fetch_array($res)) {
          $propid = $prow['properties_uid'];
          $name = $prow['name'];
          $desc = $prow['description'];
          $vals = array();
          $valres = mysqli_query($mysqli, "select value from property_values where property_uid = $propid");
          while ($vrow = mysqli_fetch_row($valres)) {
              $vals[] = $vrow[0];
          }
          $vallist = implode(',', $vals);
          print "<tr><td>$name<td>$desc<td style=text-align:center>$vallist";
        }
    }
}
print "</table>";
print "</div>";
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
