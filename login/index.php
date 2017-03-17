<?php

/*
 * Logged in page initialization
 */
include "../includes/bootstrap.inc";

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();
include "../theme/admin_header.php";
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR, USER_TYPE_PARTICIPANT));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////

?>

<div id="primaryContentContainer">
	<div id="primaryContent">
		<div class="box">
			<h2>THT Stats</h2>
			<div class="boxContent">
	<?php
	print "<table cellspacing=0 cellpadding=0 style=\"background:none;border:none\">";
	$region_count=0;
	$regions=array('general', 'pedigree', 'experiment', 'genotype', 'phenotype');
	for ($i=0; $i<count($regions); $i++) {
		if ($region_count%3===0) {
			print "<tr>";
		}
		$region_count++;
		print "<td valign=top style=border:none><table cellspacing=\"0\" cellpadding=\"0\"><tr><th>";
		$regiontbls=table_by_type($regions[$i]);
		print ucfirst($regions[$i])." Tables</th></tr><tr><td style=\"padding:0;border:0\">";
		print "<table class=\"tableclass1\" style=\"border:0\" cellspacing=\"0\" cellpadding=\"0\"><thead><tr><th style=\"font-style:italic\">Table Name</th><th style=\"font-style:italic\">Entries</th></tr></thead>";
		$tables = mysqli_query($mysqli, "SHOW TABLES");
		while($table = mysqli_fetch_row($tables)) {
			$tablename=$table[0];
			if (! in_array($tablename, $regiontbls)) continue;
			echo "<tr>\n";
			echo "\t<td><strong>".beautifulTableName($table[0], 1)."</strong></td>\n\t<td>" . getNumEntries($table[0]) . "</td>\n"; 
			echo "</tr>\n";
		}
		print "</table></p></td></tr></table>";
		print "</td>";
		if ($region_count%3===0) {
			print "</tr>";
		}
	} 
	print "</table>";
	?>		


			</div>
		</div>
<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>
	</div>
</div>
</div>


<?php include "../theme/footer.php";?>
