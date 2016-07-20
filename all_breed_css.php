<?php
# J.lee 2/5/2010 - add trap for the empty CAP name
# DaveM 13jul10: Alphabetize list of data programs.

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
$mysqli = connecti();
?>
  <h2>Contributing Programs</h2>

<?php
// Table 1, breeding programs
$sql = "
  SELECT CAPdata_programs_uid, data_program_name, data_program_code, collaborator_name, description, institutions_uid
  FROM CAPdata_programs 
  where program_type = 'breeding'
  ORDER BY data_program_name";
$query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if (mysqli_num_rows($query) > 0) {
  echo "<p><b>Breeding</b> programs are contributors of both lines and phenotype trials.";
  echo "<table><tr><th>Breeding Program<th>Code<th>Collaborator<th>Description<th>Institution";
  while ($row = mysqli_fetch_assoc($query)) {
    $CAP_uid=$row['CAPdata_programs_uid'];
    $name = $row['data_program_name'];
    $CAP_code = $row['data_program_code'];
    $c_name=$row['collaborator_name'];
    $desc = $row['description'];
    $insti_uid=$row['institutions_uid'];
    $insti_name = mysql_grab("SELECT institutions_name FROM institutions WHERE institutions_uid='$insti_uid'");
    echo("<tr><td><a href='search_bp.php?uid=$CAP_uid'>$name</a><td>$CAP_code<td>$c_name<td>$desc<td>$insti_name");
  }
  echo "</table><p>";
}

// Table 2, data programs
$sql = "
  SELECT CAPdata_programs_uid, data_program_name, data_program_code, collaborator_name, description, institutions_uid
  FROM CAPdata_programs 
  where program_type = 'data'
  ORDER BY data_program_name";
$query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if (mysqli_num_rows($query) > 0) {
  echo "<br><p><b>Data</b> programs contribute results of phenotype trials or genotyping experiments.";
  echo "<table><tr><th>Data Program<th>Code<th>Collaborator<th>Description<th>Institution";
  while ($row = mysqli_fetch_assoc($query)) {
    $CAP_uid=$row['CAPdata_programs_uid'];
    $name = $row['data_program_name'];
    $CAP_code = $row['data_program_code'];
    $c_name=$row['collaborator_name'];
    $desc = $row['description'];
    $insti_uid=$row['institutions_uid'];
    $insti_name = mysql_grab("SELECT institutions_name FROM institutions WHERE institutions_uid='$insti_uid'");
    echo("<tr><td><a href='search_bp.php?uid=$CAP_uid'>$name</a><td>$CAP_code<td>$c_name<td>$desc<td>$insti_name");
  }
  echo "</table><p>";
}

// Table 3, mapping programs
$sql = "
  SELECT CAPdata_programs_uid, data_program_name, data_program_code, collaborator_name, description, institutions_uid
  FROM CAPdata_programs 
  where program_type = 'mapping'
  ORDER BY data_program_name";
$query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if (mysqli_num_rows($query) > 0) {
  echo "<br><p><b>Mapping</b> programs contribute results of genotyping experiments on mapping populations.";
  echo "<table><tr><th>Mapping Program<th>Code<th>Collaborator<th>Description<th>Institution";
  while ($row = mysqli_fetch_assoc($query)) {
    $CAP_uid=$row['CAPdata_programs_uid'];
    $name = $row['data_program_name'];
    $CAP_code = $row['data_program_code'];
    $c_name=$row['collaborator_name'];
    $desc = $row['description'];
    $insti_uid=$row['institutions_uid'];
    $insti_name = mysql_grab("SELECT institutions_name FROM institutions WHERE institutions_uid='$insti_uid'");
    echo("<tr><td><a href='search_bp.php?uid=$CAP_uid'>$name</a><td>$CAP_code<td>$c_name<td>$desc<td>$insti_name");
  }
  echo "</table><p>";
}


$footer_div = 1;
include($config['root_dir'].'theme/footer.php'); 
?>

