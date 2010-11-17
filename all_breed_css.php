<?php
# J.lee 2/5/2010 - add trap for the empty CAP name
# DaveM 13jul10: Alphabetize list of data programs.

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');

connect();

$sql =  <<< SQL
    SELECT
        CAPdata_programs_uid, data_program_name,institutions_uid,collaborator_name, data_program_code 
    FROM
        CAPdata_programs ORDER BY data_program_name   
SQL;

$row=NULL;
$name=NULL;
$query = mysql_query($sql) or die(mysql_error());?>
<h1>List of CAP Data Programs</h1>
<p>
	<table cellpadding="0" cellspacing="0">
	<tr>
		<th>CAP Data Program (CAP Code)</th>
		<th>Location/Institution</th>
		<th>Collaborator Name</th>
	</tr>
	
<?php
if (mysql_num_rows($query) > 0)
{
	while ($row !== FALSE)
	{
		$row = mysql_fetch_assoc($query);
		
		$CAP_uid=$row['CAPdata_programs_uid'];
		if  ($CAP_uid == '')  break;
		$name = $row['data_program_name'];
		$insti_uid=$row['institutions_uid'];
		$c_name=$row['collaborator_name'];
		$CAP_code = $row['data_program_code'];
		$namewithcode = $name." (".$CAP_code.")";
		$sql="SELECT institutions_name FROM institutions WHERE institutions_uid='$insti_uid'";
		$result=mysql_query($sql) or die(mysql_error());
		$record=mysql_fetch_array($result);
		$insti_name=$record['institutions_name'];
		#if  ($insti_name == '') { break};
				echo( "<tr> <td><a href='search_bp.php?uid=$CAP_uid'>$namewithcode</a></td> <td>$insti_name</td> <td>$c_name</td> </tr>");
				

		

	}

}

?>
</table>
<?php include($config['root_dir'].'theme/footer.php'); ?>

