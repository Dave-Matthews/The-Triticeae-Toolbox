<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
include $config['root_dir'].'theme/admin_header.php';
$mysqli = connecti();
?>

<style type=text/css>
  table th { border-top: 2px solid #9a292c; }
</style>

<h1>Trait Descriptions</h1>
<div class="section">
  <table>

<?php
$cats = mysqli_query($mysqli, "select phenotype_category_uid, phenotype_category_name from phenotype_category order by phenotype_category_name");
while ($row = mysqli_fetch_array($cats)) {
    $catid = $row['phenotype_category_uid'];
    $catname = $row['phenotype_category_name'];
    $res = mysqli_query($mysqli, "select phenotypes_name,description,TO_number, unit_uid, min_pheno_value, max_pheno_value
                      from phenotypes where phenotype_category_uid = $catid
                      order by phenotypes_name");
    if (mysqli_num_rows($res) > 0) {
        print "<tr><th>$catname<th>Ontology<th>Description<th>Min<th>Max<th>Unit<th>Unit info";
        while ($trow = mysqli_fetch_array($res)) {
            $name = $trow['phenotypes_name'];
            $name = "<a href='". $config['base_url'] . "view.php?table=phenotypes&name=$name'>$name</a>";
            $desc = $trow['description'];
            $TO = $trow['TO_number'];
            if (preg_match("/TO:/", $TO)) {
                $TO = "<a href='http://browser.planteome.org/amigo/term/$TO'>$TO</a>";
            } elseif (preg_match("/CO_/", $TO)) {
                $TO = "<a href='http://www.cropontology.org/ontology/$TO'>$TO</a>";
            }
      $min = $trow['min_pheno_value'];
      $max = $trow['max_pheno_value'];
      $uuid = $trow['unit_uid'];
      $units = mysqli_query($mysqli, "select unit_name,unit_description from units where unit_uid = $uuid");
      $ures = mysqli_fetch_row($units);
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
include $config['root_dir'].'theme/footer.php';
