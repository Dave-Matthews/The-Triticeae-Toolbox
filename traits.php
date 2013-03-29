<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<style type=text/css>
  table th { border-top: 2px solid #9a292c; }
</style>

<h1>Trait Descriptions</h1>
<div class="section">
  <table>

<?php
$cats = mysql_query("select phenotype_category_uid, phenotype_category_name from phenotype_category order by phenotype_category_name");
while ($row = mysql_fetch_array($cats)) {
  $catid = $row['phenotype_category_uid'];
  $catname = $row['phenotype_category_name'];
  $res = mysql_query("select phenotypes_name,description,TO_number, unit_uid, min_pheno_value, max_pheno_value
                      from phenotypes where phenotype_category_uid = $catid");
  if (mysql_num_rows($res) > 0) {
    print "<tr><th>$catname<th>Ontology<th>Description<th>Min<th>Max<th>Unit<th>Unit info";
    while ($trow = mysql_fetch_array($res)) {
      $name = $trow['phenotypes_name'];
      $name = "<a href='". $config['base_url'] . "view.php?table=phenotypes&name=$name'>$name</a>";
      $desc = $trow['description'];
      $TO = $trow['TO_number'];
      // Add href to Gramene:
      $TO = "<a href='http://www.gramene.org/db/ontology/search?query=$TO'>$TO</a>";
      $min = $trow['min_pheno_value'];
      $max = $trow['max_pheno_value'];
      $uuid = $trow['unit_uid'];
      $units = mysql_query("select unit_name,unit_description from units where unit_uid = $uuid");
      $ures = mysql_fetch_row($units);
      $uname = $ures[0];
      $udesc = $ures[1];
      print "<tr><td>$name<td>$TO<td>$desc<td style=text-align:center>$min
             <td style=text-align:center>$max<td>$uname<td>$udesc
             </tr>";
    }
  }
}
print "</table>";
print "</div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>

