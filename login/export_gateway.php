<?php
require 'config.php';
/******************************
* Logged in page initialization
*
* 6/2/2011 JLee Fix problem with 
*    displaying button
*******************************/
 
 
include($config['root_dir'] . 'includes/bootstrap.inc');

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);
////////////////////////////////////////////////////////////////////////////////

ob_start();
include($config['root_dir'] . 'theme/admin_header.php');
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
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
		<p>Backup all the contents in the database.</p>
		<ul style="list-style-type : none">
			<li><a href="<?php echo $config['base_url']; ?>dbtest/backupDB.php">Full Database Backup</a></li>
		</ul>
	</div>
	<h3 style="text-align:left">Export Individual Tables</h3>
	
	<div class="boxContent">
		<p>Export the content in a table in csv format</p>
		<form id='form_table_sel' method='post' action="<?php echo $config['base_url']; ?>dbtest/exportTbl.php" >
		<p>Choose a table to export</p>
		
		<select name='table_sel' size=5>
		
	<?php
		$result = mysqli_query($mysqli, "show tables") or die(mysqli_error($mysqli));
		while ($row = mysqli_fetch_assoc($result)) {
			$selval=implode("",array_slice($row,0,1));
			print "<option value=\"$selval\">$selval</option>\n";
		}
	?>
	</select>
	<p>
	<input type='submit' value='Select' />
    </p>
    </form>

	
	</div>
	
	<h3 style="text-align:left">Export results of frequently used queries</h3>
	<div class="boxContent">
		<p>Export results from the following queries</p>
		<ul style="list-style-type : none">
		</ul>
	</div>
    <p>
    
    <?php $row = loadUser($_SESSION['username']); ?>
    <?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

</div>
</div>
</div>
</div>
<?php include($config['root_dir'] . '/theme/footer.php');?>
