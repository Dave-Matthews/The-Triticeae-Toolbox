<?php 
	$basedir="../";
	include($basedir."theme/header.php");
	include_once("display_functions.inc");
?>

<h1><em>The Hordeum Toolbox</em> (THT) Admin Console </h1>
   
<!-- Login Top Box -->
<table id="topbox">
<tr>
	<td>Welcome <?php echo $row['name']; ?></td>
	<td>Pedigree Data: <br /> <a href="">add</a> | <a href="">edit</a></td>
	<td>Trait Data: <br /> <a href="">add</a> | <a href="">edit</a></td>
	<td>SNP Markers: <br /> <a href="">add</a> | <a href="">edit</a></td>
	<td><a href="login.php?logout=true">Logout</a></td>
</tr>
</table>

<?php
	// TODO : add upload progress bar here
	$targetpath="Temp/";
	if ($_FILES['traitfile']['error']> 0 ){
		print "File Upload Error". $_FILES['traitfile']['error']."<br>";
	}
	else {
		$uploadfile=$_FILES['traitfile']['name'];
		$uftype=$_FILES['traitfile']['type'];
		if ($uftype != 'application/vnd.ms-excel') {
			print "<h2>Expecting an Excel file. <br> The type of the uploaded file is ".$uftype.".</h2>";
		}
		else {
			if(move_uploaded_file($_FILES['traitfile']['tmp_name'], $target_path.$uploadfile)) {
    			echo "<h2>The file ".basename( $uploadfile)." has been uploaded. </h2>\n";

    			display_uploaded_traits($uploadfile);

		} else{
    			echo "<p>There was an error uploading the file, please try again!</p>";
			}
		}
	}
?>

<?php include($basedir."theme/footer.php");?>