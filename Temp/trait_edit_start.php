<?php 
	$basedir="../";
	include($basedir."theme/header.php");
?>

<div style="text-align: center;"><br><h1><span style="font-style: italic;">THT</span> Traits Editing Page</h1>
<form enctype="multipart/form-data" method="post" action="Temp/uploader_traits.php" name="Upload_traits">
<table style="text-align: left; width: 719px; height: 114px;" border="1" cellpadding="2" cellspacing="2">
<tbody>
<tr><td style="width: 244px;">To edit the traits online</td>
<td style="width: 467px;"><a href="trait_edit.php">Edit Traits Online</a></td></tr>
<tr><td style="width: 244px;">To upload an 
<span style="font-style: italic;">Execl</span> 
file with the format suggested by the <a href="tht_trait_template.xls"><span style="font-style: italic;">THT Trait Template</span></a></td>
<td style="width: 467px;"><input name="traitfile" type="file"><input name="traitsubmit" value="Upload" type="submit"></td></tr>
</tbody>
</table>
</form>
</div>
<?php include($basedir."theme/footer.php");?>