<?php
/**
 * Genotype trials
 *
 * PHP version 5.3
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/t3_report.php
 */

require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';

$mysqli = connecti();

require $config['root_dir'].'theme/normal_header.php';
check_session();
display_list();
require $config['root_dir'].'theme/footer.php';

function check_session()
{
    global $mysqli;
    if (!isset($_SESSION['geno_exps'])) {
        return;
    }
    print "<h2>Selected genotype experiments</h2>\n";
    print "<table border=0>";
    print "<tr><td>Trial Code<td>year<td>description\n";
    $selected = $_SESSION['geno_exps'];
    foreach ($selected as $uid) {
        $sql = $sql = "select trial_code, experiment_year, experiment_desc_name from experiments
            where experiment_uid = $uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_row($res)) {
            $trial_code = $row[0];
            $date = $row[1];
            $desc = $row[2];
            print "<tr><td><a href='".$config['base_url']."display_genotype.php?trial_code=$trial_code'>$trial_code</a><td>$date<td>$desc\n";
        }
    }
    echo "</table><br><br>";
}
    
function display_list()
{
    global $mysqli;
    ?>
    <h2>Genotype experiments ordered by creation date</h2>
    <table border=0>
    <tr><td>Trial Code<td>year<td>number of<br>markers<td>description
    <?php
    $sql = "select CAPdata_programs_uid, data_program_name from CAPdata_programs";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $name = $row[1];
        $cap_prog_name[$uid] = $name;
    }
    $sql = "select experiment_uid, CAPdata_programs_uid, trial_code, experiment_short_name, experiment_year, experiment_desc_name
        from experiments, experiment_types
        where experiments.experiment_type_uid = experiment_types.experiment_type_uid and experiment_types.experiment_type_name = 'genotype'";
    if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR))) {
        $sql .= " and data_public_flag > 0";
    }
    $sql .= " order by experiments.experiment_year desc";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $experiment_uid = $row[0];
        $trial_code = $row[2];
        $short_name = $row[3];
        $date = $row[4];
        $desc = $row[5];
        $sql = "select marker_count from genotype_experiment_info where experiment_uid = $experiment_uid";
        $res2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row2 = mysqli_fetch_row($res2)) {
            $count = number_format($row2[0]);
        }
        print "<tr><td><a href='".$config['base_url']."display_genotype.php?trial_code=$trial_code'>$trial_code</a><td>$date<td style=\"text-align:right\">$count<td>$desc\n";
    }
    echo "</table></div>";
}
