<?php

	ini_set("memory_limit","32M");
	include("../includes/bootstrap.inc");
	connect();
	/* Command to Backup the Database */

	//why all the options? don't ask... stupid windows server.
        $passthru = "mysqldump --skip-opt --add-drop-table --add-locks --create-options --disable-keys --extended-insert --quick -h $server -u $username -p$password $database";

	/* process export table*/
	if (isset($_POST['query_string'])) {
		$query_string=base64_decode($_POST['query_string']);
		if (! preg_match('/^\s*select/i', $query_string)) die("Only works with query commands start with select\n");
		$backup = "";
		$result=mysql_query($query_string) or die("Invalid query");
		$count=0;
		ob_start();
		while($row=mysql_fetch_assoc($result)) {
			$rkeys=array_keys($row);
			if ($count==0) {
				$count++;
				for($i=0; $i<count($rkeys); $i++) {
					print mysql_escape_string($rkeys[$i]);
					if ($i!=count($rkeys)-1) print ", "; 
				}
				print "\n";
			}
			for($i=0; $i<count($rkeys); $i++) {
				print mysql_escape_string($row[$rkeys[$i]]);
				if ($i!=count($rkeys)-1) print ", "; 
			}
			print "\n";
		}
		$backup.=ob_get_contents();
		ob_end_clean();
		$date = date("m-d-Y-H:i:s");
		$name = "THT-query-$date.txt";
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=$name");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo $backup;
	}
	
?>
