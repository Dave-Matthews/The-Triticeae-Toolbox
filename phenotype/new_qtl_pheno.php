<html>
<body>
<?php

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();

include($config['root_dir'] . 'theme/admin_header.php');
/*******************************/

$sql="SELECT unit_uid, phenotypes_name FROM phenotypes";
$res = mysql_query($sql) or die(mysql_error());
while($row=mysql_fetch_assoc($res))
{
$unit_uid=$row['unit_uid'];
$phenotypes_name=$row['phenotypes_name'];
echo $phenotypes_name;
echo "	";
$sql="SELECT unit_name FROM units WHERE unit_uid=$unit_uid";
$res1=mysql_query($sql) or die(mysql_error());
$row1=mysql_fetch_assoc($res1);
$unit_name=$row1['unit_name'];
echo $unit_name;
echo " ;	";
}
echo "<br><br><br><br><br>";

$sql="SELECT tht_base_uid, value FROM phenotype_data";
$res = mysql_query($sql) or die(mysql_error());
$prev=null;
$count=0;
while($row=mysql_fetch_assoc($res))
{
$tht_base_uid=$row['tht_base_uid'];
$value=$row['value'];

if($count==0)
{
$prev=$tht_base_uid;
$sql_thtbase="SELECT line_record_uid, experiment_uid FROM tht_base WHERE tht_base_uid=$tht_base_uid";
	$res_thtbase=mysql_query($sql_thtbase) or die(mysql_error());
	$row_thtbase=mysql_fetch_assoc($res_thtbase);
	$line_record_uid=$row_thtbase['line_record_uid'];
	$experiment_uid=$row_thtbase['experiment_uid'];
	
	$sql_linerecords="SELECT line_record_name FROM line_records WHERE line_record_uid=$line_record_uid";
	$res_linerecords=mysql_query($sql_linerecords) or die(mysql_error());
	$row_linerecords=mysql_fetch_assoc($res_linerecords);
	$line_record_name=$row_linerecords['line_record_name'];
	
	$sql_experiments="SELECT experiment_short_name FROM experiments WHERE experiment_uid=$experiment_uid";
	$res_experiments=mysql_query($sql_experiments) or die(mysql_error());
	$row_experiments=mysql_fetch_assoc($res_experiments);
	$experiment_name=$row_experiments['experiment_short_name'];
	
	echo "<br>";
	echo $line_record_name;
	echo "	";
	echo $experiment_name;
	echo "	";
	echo $value;
	echo "	";
}//end if

else
{
	if($prev==$tht_base_uid)
	{
		echo "$value";
		echo "	";
		continue;
		
	}
	else
	{
	$prev=$tht_base_uid;
	$sql_thtbase="SELECT line_record_uid, experiment_uid FROM tht_base WHERE tht_base_uid=$tht_base_uid";
	$res_thtbase=mysql_query($sql_thtbase) or die(mysql_error());
	$row_thtbase=mysql_fetch_assoc($res_thtbase);
	$line_record_uid=$row_thtbase['line_record_uid'];
	$experiment_uid=$row_thtbase['experiment_uid'];
	
	$sql_linerecords="SELECT line_record_name FROM line_records WHERE line_record_uid=$line_record_uid";
	$res_linerecords=mysql_query($sql_linerecords) or die(mysql_error());
	$row_linerecords=mysql_fetch_assoc($res_linerecords);
	$line_record_name=$row_linerecords['line_record_name'];
	
	$sql_experiments="SELECT experiment_short_name FROM experiments WHERE experiment_uid=$experiment_uid";
	$res_experiments=mysql_query($sql_experiments) or die(mysql_error());
	$row_experiments=mysql_fetch_assoc($res_experiments);
	$experiment_name=$row_experiments['experiment_short_name'];
	
	echo "<br>";
	echo $line_record_name;
	echo "	";
	echo $experiment_name;
	echo "	";
	echo $value;
	echo "	";

	}//end else
}//end else
$count++;
}	
?>
</body>
</html>