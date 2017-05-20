<?php
/**
 * Home page
 *
 * PHP version 5.3
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/index.html
 *
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header2.php';
$mysqli = connecti();
$name = get_unique_name("datasets");
?>

<h1>Explore T3</h1>

  <!-- Box Table B -->
  <p>
  <table cellpadding="0" cellspacing="0"><tbody>
  <tr>
  <th>Search Trials</th>
  <th>&nbsp;</th>
  </tr>

  <tr><td>
  <select onchange="window.open('<?php echo $config['base_url']; ?>search_bp.php?table=CAPdata_programs&uid='+this.options[this.selectedIndex].value,'_top')">
  <option selected value=''>Search by Breeding Program</option>
    <?php
  // dem jan13: Only include programs that have phenotype experiment trials.
  // dem sep14: Only include programs whose lines are in phenotype experiment trials.
  /* $sql = "select distinct */
  /*    data_program_name, data_program_code, cp.CAPdata_programs_uid as uid */
  /*    FROM CAPdata_programs cp, experiments e */
  /*    WHERE program_type = 'breeding' */
  /*    AND cp.CAPdata_programs_uid = e.CAPdata_programs_uid */
  /*    order by data_program_name asc;"; */
    $sql = "SELECT DISTINCT
	  data_program_name, data_program_code, cp.CAPdata_programs_uid as uid
	  FROM CAPdata_programs cp, experiments e, tht_base tb, line_records lr
	  WHERE program_type = 'breeding'
          AND e.CAPdata_programs_uid = cp.CAPdata_programs_uid
	  AND lr.breeding_program_code = data_program_code
	  AND tb.experiment_uid = e.experiment_uid
	  AND tb.line_record_uid = lr.line_record_uid
	  ORDER BY data_program_name asc;";
    $r = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) . "\n$sql");
    while ($row = mysqli_fetch_assoc($r)) {
        $progname = $row['data_program_name']." - ".$row['data_program_code'];
        $uid = $row['uid'];
        echo "<option value='$uid'>$progname</option>\n";
    }
?>
  </select>
  <td>Trials containing data from the program&#39;s lines
  </tr>
  <tr><td>
  <select onchange="window.open('<?php echo $config['base_url']; ?>search_phenotype.php?table=experiments&pheno_name='+this.options[this.selectedIndex].value,'_top')">
  <option selected value="">Search by Trait</option>
    <?php
    $sql = "select distinct phenotypes_name from phenotypes
    order by phenotypes_name";
    $r = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) . "\n$sql");
    while ($row = mysqli_fetch_assoc($r)) {
        $pheno_name = $row['phenotypes_name'];
        echo "<option value='$pheno_name'>$pheno_name</option>\n";
    }
?>
</select></td>
<td></td></tr>

  <tr><td>
  <select onchange="window.open('<?php echo $config['base_url']; ?>view_search_yr2.php?table=experiments&year='+this.options[this.selectedIndex].value,'_top')">
  <option selected value=''>Search by Year</option>
  <?php
  $sql = "select distinct experiment_year from experiments
  order by experiment_year desc";
  $r = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) . "\n$sql");
  while ($row = mysqli_fetch_assoc($r)) {
      $year = $row['experiment_year'];
      echo "<option value='$year'>$year</option>\n";
  }
?>
</select></td>
<td></td></tr>

  <tr><td>
  <select onchange="window.open('<?php echo $config['base_url']; ?>search_expt.php?expt='+this.options[this.selectedIndex].value,'_top')">
  <option selected value=''>Search Phenotype Trials by Experiment</option>
  <?php
  $sql = "select experiment_set_uid, experiment_set_name from experiment_set
          order by experiment_set_name";
  $r = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) . "\n$sql");
  while ($row = mysqli_fetch_assoc($r)) {
      $euid = $row['experiment_set_uid'];
      $ename = $row['experiment_set_name'];
      echo "<option value=$euid>$ename</option>\n";
  }
?>
</select></td><td>An experiment may have several trials with similar or identical entry lists, performed at different locations and/or different years. For a description of each experiment see this <a href="t3_report.php?query=PExps">list</a>.
</td></tr>


</tbody></table>

<?php
mysqli_close($mysqli);
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
