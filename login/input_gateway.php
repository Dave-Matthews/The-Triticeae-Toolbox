<?php
require 'config.php';
/*
* Logged in page initialization
*
*
*  6/20/11 J.Lee  Suppress invalid import features
*/
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

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

	<h2>Data Input Methods</h2>
	<div class="boxContent">
		<p>This is a collection of all the input links.</p>
	</div>

	<h3 style="text-align:left">Individual Tables</h3>
	<div class="boxContent">
		<p>Manually input content for an individual entity correspondent to a table.
		This is for entities such as experiments, institutions, etc.</p>
	</div>

	<ui style="list-style-type : none">
		<li><a href="login/general_table_input.php">Individual Table Input</a></li>
	</ui>

	<br><br>
<!--
	<h3 style="text-align:left">Predifined Excel-based Batch Input</h3>
	<div class="boxContent">
		<p>This is mostly used for input definitions of pedigrees, markers and traits.
		Format the data file according to the examplar excel file.
		(It is also possible to use the following general input methods for this tables.)</p>
	</div>

	<table border=0 cellspacing=0 cellpadding=0>
	<thead>
	<tr>
		<th width="30%">Link</th>
		<th width="30%">Examplar File</th>
		<th width="40%">Description</th>
	</tr>
	</thead>
	<tr>
		<td><a href="login/traitAdd.php">Excel-based Traits (def) Input</a></td>
		<td><a href="downloadlite/index.php?THT_trait_template.xls">Examplar Trait Input Files</a></td>
		<td>&nbsp;
-->
		<!-- Left blank on purpose -->
<!--		&nbsp;</td>

	</tr>
	<tr>
		<td><a href="login/markerAdd.php">Excel-based Markers (def) Input</a></td>
		<td><a href="login/uploads/processed_files/marker_data_def.xls">Examplar Marker Input File</a></td>
		<td>&nbsp;
-->
		<!-- Left blank on purpose -->
<!--		&nbsp;</td>


	</tr>
	</table>

	<br><br>
	<h3 style="text-align:left">General Input</h3>
	<div class="boxContent">
		<p>Create a definition file for your data, and input it into the database.
		The data file will also need some slight formatting.
		List here are the frequenctly used data files (example), and definition files</p>
	</div>
	<p><a href="login/parser_add.php">Start General Input</a></p>
	<table border=0 cellspacing=0 cellpadding=0>
	<thead>
	<tr>
		<th width="10%">Link</th>
		<th width="40%">Data file (excerpt)</th>
		<th width="20%">Definition file</th>
		<th width="30%">Description</th>
	</tr>
	</thead>
	<tr>
		<td><a href="login/parser_add.php">Link</a></td>
		<td><a href="login/uploads/processed_files/capcore_1h.xls">CAP Core Genotypeing Data (1H)</a></td>
		<td><a href="login/uploads/processed_files/capcore_examplar.xls">Definition File</a></td>
		<td>&nbsp;
-->	
		<!-- Left blank on purpose -->
<!--	&nbsp;</td>


	</tr>
	<tr>
		<td><a href="login/parser_add.php">Link</a></td>
		<td><a href="login/uploads/processed_files/VT06Warsaw_mn_fixed.xls">CAP Core Field Trials Data</a></td>
		<td><a href="login/uploads/processed_files/CAP_Fieldtrials_Def.xls">Definition File</a></td>
		<td>&nbsp;-->
		<!-- Left blank on purpose -->
<!--		&nbsp;</td>

	</tr>
		<tr>
		<td><a href="login/parser_add.php">Link</a></td>
		<td><a href="login/uploads/THT_Traits_All_Table.xls">CAP Trait Names</a></td>
		<td><a href="login/uploads/processed_files/phenotypes_definition.xls">Definition File</a></td>
		<td>&nbsp;-->
		<!-- Left blank on purpose -->
<!--	&nbsp;</td>

	</tr>
		<tr>
		<td><a href="login/parser_add.php">Link</a></td>
		<td><a href="login/uploads/processed_files/THT_harvest_map.xls">Barley Harvest Markers</a></td>
		<td><a href="login/uploads/processed_files/marker_data_def.xls">Definition File</a></td>
		<td>&nbsp;-->
		<!-- Left blank on purpose -->
<!--		&nbsp;</td>
	</tr>
-->
	</table>


<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

</div>
</div>
</div>
</div>
<?php include($config['root_dir'] . 'theme/footer.php');?>
