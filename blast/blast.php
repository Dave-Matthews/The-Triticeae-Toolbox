<?php
//BLAST marker sequence

require 'config.php';

include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'] . 'theme/admin_header.php');
?>
<h2>Select Marker by Nucleotide Sequence</h2>
<FORM ACTION="/cgi-bin/blast/blast_link_result_wheat.cgi" METHOD = POST NAME="MainBlastForm" ENCTYPE= "multipart/form-data">
Database:<br>
<select name = "DATALIB">
<option VALUE = "wheat-markers" selected> Wheat Markers
</select>
<br><br>
<table border=0>
<tr>
	<td>Enter Sequence Data:</td>
</tr>
<tr>
	<td><textarea name="SEQUENCE" rows=6 cols=60></textarea></td>
</tr>
</table>
<P>
<INPUT TYPE="button" VALUE="Clear sequence" onClick="MainBlastForm.SEQUENCE.value='';MainBlastForm.SEQFILE.value='';MainBlastForm.SEQUENCE.focus();">
<INPUT TYPE="submit" VALUE="Search">
</FORM>

<?php

$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
