<?
include_once("includes/bootstrap.inc");
connect();

?>
<style>
.fav_filter{
	height: 20px;
	border: 1px solid #5B53A6;
	padding: 5px;
	margin-bottom: 10px;
	background: url('theme/images/tblhdg.png') repeat-x;
}
.fav_filter * {margin: 0; padding:0;}
.fav_filter span{
	color: black;
	display:block;
}
</style>
<?

if(isset($_GET['delete'])){
	mysql_query("DELETE FROM fav_filters WHERE fav_filters_uid = '{$_GET['delete']}'"); 	
}


$sql = "SELECT f.fav_filters_uid as id, f.name FROM fav_filters as f, users as u WHERE f.users_uid = u.users_uid AND u.users_name = '{$_SESSION['username']}'";
$query = mysql_query($sql) or die(mysql_error());

if (mysql_num_rows($query) > 0 ){
	while ($filter = mysql_fetch_array($query)){
		echo '<div id="fav_filter_'.$filter['id'].'" class="fav_filter"><span style="width: auto; display: block">';
		echo '<table style="background: 0; border: 0; margin: 0; padding: 0;" width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="border: 0; margin: 0; padding: 0; width: 100%; text-align: left" width="100%">';
        echo '<input type="button" value="Load" onclick="document.location=\'as_phenotype.php?load_fav_filter='.$filter['name'].'\'" />&nbsp;';
		echo "<input type=\"button\" value=\"Delete\" onclick=\"var conf = confirm('Are you sure you want to delete this favorite filter set?'); if(conf){ new Effect.Fade('fav_filter_".$filter['id']."',{duration:0.5}); new Ajax.Updater('favorite_filters', 'fav_get_filters.php?delete={$filter['id']}', {asynchronous:true}); }\" />&nbsp;&nbsp;&nbsp;";
		echo '<b>' . $filter['name'] . '</b>';
		echo '</td></tr></table>';
		echo '</span></div><br/>';
	}
}else{
	echo "<p style=\"margin-left: 25px\"><i>You don't have any saved filter sets.</i></p>";
}

?>
