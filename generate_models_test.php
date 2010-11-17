<?php
include('includes/bootstrap.inc');
connect();
?>
<table border="1">
<tr><th>Dataset</th><th>Experiments</th></tr>
<?php

// Outputs a list of users
$users = my_users_peer::get_all();
$usersList = implode(', ', $users);
echo "<p><strong>Users:</strong> $usersList</p>";


// Outputs a table of datasets an the related experiments
$datasets = my_datasets_peer::get_all();
foreach ($datasets as $dataset){
	$dataset =& new my_datasets($dataset);  # This line is required to get access to the get_experiments function
	echo "  <tr><td><strong>" . $dataset->get_dataset_name() . ":</strong></td><td>";
	$experiments = $dataset->get_experiments();
	foreach ($experiments as $experiment){
		echo $experiment->get_experiment_name()."&nbsp;";
	}
	unset($experiments);
	echo "</td></tr>\n";
}
unset($datasets);


?>
</table>