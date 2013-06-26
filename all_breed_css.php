<?php
# J.lee 2/5/2010 - add trap for the empty CAP name
# DaveM 13jul10: Alphabetize list of data programs.

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();
?>
  <h2>CAP Data Programs</h2>
  <table cellpadding="0" cellspacing="0">
    <tr>
      <th>Data Program
      <th>Code
      <th>Institution
      <th>Collaborator
      <th>Type
<?php
  $sql =  <<< SQL
  SELECT CAPdata_programs_uid, data_program_name,institutions_uid,collaborator_name, data_program_code, program_type
  FROM CAPdata_programs 
  ORDER BY data_program_name   
SQL;
$query = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($query)) {
  $CAP_uid=$row['CAPdata_programs_uid'];
  $name = $row['data_program_name'];
  $insti_uid=$row['institutions_uid'];
  $c_name=$row['collaborator_name'];
  $CAP_code = $row['data_program_code'];
  $progtype = $row[program_type];
  $sql="SELECT institutions_name FROM institutions WHERE institutions_uid='$insti_uid'";
  $result=mysql_query($sql) or die(mysql_error());
  $record=mysql_fetch_array($result);
  $insti_name=$record['institutions_name'];
  echo("<tr><td><a href='search_bp.php?uid=$CAP_uid'>$name</a><td>$CAP_code<td>$insti_name <td>$c_name<td>$progtype");
}
echo "</table>";
$footer_div = 1;
include($config['root_dir'].'theme/footer.php'); 
?>

