
<?php

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');


connect();

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



echo "Here are the experiments for the breeding program $bp_name <br></br>";

//echo "$bp_name\n";

$bpuidsql="SELECT CAPdata_programs_uid FROM CAPdata_programs WHERE data_program_name = '$bp_name'";

$bp_rst=mysql_query($bpuidsql);
$bp_row=mysql_fetch_assoc($bp_rst);
$bpuid=$bp_row['CAPdata_programs_uid'];

//echo "$bpuid\n";
$trial_code=NULL;
$sql="SELECT trial_code FROM experiments WHERE CAPdata_programs_uid = '$bpuid'";
$result=mysql_query($sql) or die(mysql_error());
while($row=mysql_fetch_array($result))
{
$trial_code=$row['trial_code'];
$filename="data/".$trial_code.".xls";
echo( "<a href='$filename'>$trial_code</a><br>");
}


	

?>
<?php include($config['root_dir'].'theme/footer.php'); ?>




