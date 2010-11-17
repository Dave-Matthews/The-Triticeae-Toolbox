
<?php

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');


$conn = mysql_connect("lab.bcb.iastate.edu", "shreymuk", "babi1983");
mysql_select_db("sandbox_shreymuk");

$bp_rst=NULL;
$bp_row=NULL;
$bpuid=NULL;
$ds_rst=NULL;
$ds_row=NULL;
$dsuid=NULL;
$ex_rst=NULL;
$ex_row=NULL;
$exuid=NULL;
$exnam=NULL;

$bp_name = $_GET['bpname'];

//echo "$bp_name\n";

$bpuidsql="SELECT breeding_programs_uid FROM breeding_programs WHERE breeding_programs_name = '$bp_name'";

$bp_rst=mysql_query($bpuidsql);
$bp_row=mysql_fetch_assoc($bp_rst);
$bpuid=$bp_row['breeding_programs_uid'];

//echo "$bpuid\n";


$dsuidsql="SELECT datasets_uid FROM datasets WHERE breeding_programs_uid = '$bpuid'";
$ds_rst=mysql_query($dsuidsql);
$ds_row=mysql_fetch_assoc($ds_rst);
$dsuid=$ds_row['datasets_uid'];

//echo "$dsuid\n";


while ($ds_row !== FALSE)
{
	$exnamsql="SELECT DISTINCT experiment_name FROM experiments WHERE datasets_uid = '$dsuid'" ;
	$ex_rst=mysql_query($exnamsql);
	$ex_row=mysql_fetch_assoc($ex_rst);
	$exname=$ex_row['experiment_name'];
	$num=mysql_num_rows($ex_rst);
	echo $num ;
	
	//echo "$exname\n";
	//echo "Halum\n";
	
	while ($ex_row !== FALSE)
	{
		$exnam=$ex_row['experiment_name'];
		echo "$exnam";
		echo \n;
		$ex_row=mysql_fetch_assoc($ex_rst);
	}
	
	$ds_row=mysql_fetch_assoc($ds_rst);
	$dsuid=$ds_row['datasets_uid'];	
}
	

?>
<?php include($config['root_dir'].'theme/footer.php'); ?>




