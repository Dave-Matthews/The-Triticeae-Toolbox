<?php
	$basedir="../";
	require_once($basedir."includes/excel/reader.php");


	function display_uploaded_traits ($filename) {

		/* Creating the object */
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('CP1251');
		$data->read($filename);
		$data->trimCols(0); 	//new function that I added to trim columns

		/* Setting Error Reporting */
		error_reporting(E_ALL ^ E_NOTICE);
		
		/* Show in table format */
		print "<table class=\"preview\">";
		for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {

			/* Determine Row Colors */
			if($i < 3) 
				$class = "dark";
			else if($i % 2 == 0)
				$class = "medium";
			else
				$class = "light";

			/* Print Row */
			print "\n\t<tr>";
			for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
				echo "\n\t\t<td class=\"$class\">".$data->sheets[0]['cells'][$i][$j]."</td>";
			}
			echo "\n\t</tr>";
		}
		print "\n</table>";
	}
?>
