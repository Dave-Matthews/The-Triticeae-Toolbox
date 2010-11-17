<?php


require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();

include($config['root_dir'] . 'theme/admin_header.php');
echo "Hi";

$select = "SELECT * FROM map";
$export = mysql_query($select);
$count = mysql_num_fields($export);

for ($i = 0; $i < $count; $i++) {
$header .= mysql_field_name($export, $i)."t";
}


while($row = mysql_fetch_row($export)) {
$line = "";
foreach($row as $value) {
if ((!isset($value)) OR ($value == "")) {
$value = "t";
} else {
$value = str_replace(""", """", $value);
$value = ""‘ . $value . ‘"‘ . “t";
}
$line .= $value;
}
$data .= trim($line)."n";
}
$data = str_replace("r", “", $data);

if ($data == “") {
$data = “n(0) Records Found!n";
}


print “$headern$data";

include($config['root_dir'].'theme/footer.php');
?>
