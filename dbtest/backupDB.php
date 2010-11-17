<?php

	ini_set("memory_limit","128M");

	$server = "lab.bcb.iastate.edu";
	$username = "yhames04";
	$password = "gdcb07";
	$database = "sandbox_yhames04";


	/* Command to Backup the Database */

	//why all the options? don't ask... stupid windows server.
        $passthru = "mysqldump --skip-opt --add-drop-table --add-locks --create-options --disable-keys --extended-insert --quick -h $server -u $username -p$password $database";

	ob_start();
	system($passthru);

	$backup = ob_get_contents();
	ob_end_clean();

	$date = date("m-d-Y-H:i:s");
	$name = "THT-DB-Backup-$date.sql";

	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$name");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo $backup;
	
?>