<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();
include($config['root_dir'] . 'theme/admin_header.php');
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR, USER_TYPE_PARTICIPANT));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////

?>

<div id="primaryContentContainer">
<div id="primaryContent">
<div class="box">

	<h2>Data export Methods</h2>
	<br>
	<h3 style="text-align:left">Full Database Backup</h3>
	<div class="boxContent">
		<p>Backup all the contents in the database.
		<ui style="list-style-type : none">
			<li><a href="<?php echo $config['base_url']; ?>dbtest/backupDB.php">Full Database Backup</a></li>
		</ui>
	</div>
	<h3 style="text-align:left">Export Individual Tables</h3>
	<div class="boxContent">
		<p>Export the content in a table in csv format</p>
		<form id='form_table_sel' method='post' action="<?php echo $config['base_url']; ?>dbtest/exportTbl.php">
		<p>Choose a table to export</p>
		<select name='table_sel' size=5>
		<?php
		$result=mysql_query("show tables") or die(mysql_error);
		while ($row=mysql_fetch_assoc($result)) {
			$selval=implode("",array_slice($row,0,1));
			print "<option value=\"$selval\">$selval</option>\n";
		}
		print "</select></p><p>";
print "<input type = 'radio' name='count' value='all' checked>All ";
print "<input type = 'radio' name='count' value='100'>First 100<br>";
		print "<input type='submit' value='Select'>";
		print "</form>";
		?>
	</div>
	<h3 style="text-align:left">Export results of frequently used queries</h3>
	<div class="boxContent">
		<p>Export results from the following queries</p>
		<ui style="list-style-type : none">
		</ui>
	</div>

</div>
</div>
</div>
</div>
<?php include($config['root_dir'] . '/theme/footer.php');?>
