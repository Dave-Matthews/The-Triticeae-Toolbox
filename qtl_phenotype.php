<?php
header("Content-type:text/plain");
include("includes/bootstrap.inc");
connect();

//include("cookie/cookie.php");
//$mycookie = new MyCookie($_SESSION['username']);

/* Ouput the heading
---------------------*/
echo "Experiment Inbred ";
/* TODO: Don't select all phenotypes */
$sql = "SELECT phenotype_uid, phenotypes_name FROM phenotypes WHERE 1";
$res = mysql_query($sql);

$phenotype_ids = array();
while ($phenotype = mysql_fetch_assoc($res)){
	echo str_replace(' ', '_', trim($phenotype[phenotypes_name])) ." N ";
	array_push($phenotype_ids, $phenotype[phenotype_uid]);
}
echo "\n";
/*---------------------*/

/* The user's selected lines */
$where_lines = 'line_records.line_record_uid = \'207\'';
//$where_lines = $mycookie->gen_where('lines', 'line_records.line_record_uid');

$sql = <<< SQL
SELECT
	line_records.line_record_name,
	experiments.experiment_name,
	line_records.line_record_uid,
	experiments.experiment_uid
FROM
	line_records LEFT JOIN (tht_base, experiments)
ON
	(experiments.experiment_uid = tht_base.experiment_uid
	AND tht_base.line_record_uid = line_records.line_record_uid)
WHERE
    $where_lines
GROUP BY line_records.line_record_uid
SQL;

$res = mysql_query($sql) or die(mysql_error());

while ($line = mysql_fetch_assoc($res)){

	echo str_replace(' ', '_', trim($line[experiment_name])) . " ";
	echo str_replace(' ', '_', trim($line[line_record_name])) . " ";
	foreach ($phenotype_ids as $phenotype_id)
	if ($data = getData($line['line_record_uid'], $line['experiment_uid'], $phenotype_id)){
		echo "$data[0] $data[1] ";
	}
	else
	{
		echo "N/A N/A ";
	}

	echo "\n";
}

function getData($line_record_uid, $experiment_uid, $phenotype_uid){
	$sql = <<< SQL
SELECT
	AVG(phenotype_data.value), COUNT(*)
FROM
	phenotype_data, line_records, tht_base,	experiments
WHERE
	line_records.line_record_uid = $line_record_uid
	AND experiments.experiment_uid = $experiment_uid
	AND tht_base.experiment_uid = experiments.experiment_uid
	AND phenotype_data.tht_base_uid = tht_base.tht_base_uid
	AND phenotype_data.phenotype_uid = $phenotype_uid
GROUP BY
	line_records.line_record_uid
SQL;

	$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
	if (mysql_num_rows($res) > 0){
		return mysql_fetch_array($res);
	}
	return FALSE;
}
?>
