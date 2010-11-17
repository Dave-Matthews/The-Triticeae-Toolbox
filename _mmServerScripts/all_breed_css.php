
<?php

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');


$conn = mysql_connect("lab.bcb.iastate.edu", "shreymuk", "babi1983");
mysql_select_db("sandbox_shreymuk");

$sql =  <<< SQL
    SELECT
        breeding_programs_name 
    FROM
        breeding_programs    
SQL;

$row=NULL;
$name=NULL;

$query = mysql_query($sql) or die(mysql_error());

if (mysql_num_rows($query) > 0)
{
	while ($row !== FALSE)
	{
		$row = mysql_fetch_assoc($query);
		
		$name = $row['breeding_programs_name'];
		
		
				echo( "<a href='fetch_exname_css.php?bpname=$name'>$name</a><br>");

		

	}

}

?>

<?php include($config['root_dir'].'theme/footer.php'); ?>

