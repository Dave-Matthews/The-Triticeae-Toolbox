<?php
header("Content-type:text/plain");
include("includes/bootstrap.inc");
connect();

echo "line_name";
$select = "line_record_name";
$count = 1;
$query = mysql_query("SELECT marker_name FROM markers");
while ($marker = mysql_fetch_row($query)) {
	//echo " $marker[0]";
	$select .= ", v$count.value";
	$count++;
}
$in = substr($in, 0, -1);

echo $in;



/*$data = array();

$result = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM line_records"));
$init_size = $size = $result[0];
$offset = 0;

echo "line_name ";

while ($size > 0){

	$sql = "SELECT marker_name, marker_uid FROM markers";// WHERE marker_uid < 1000 LIMIT 30";
	$res = mysql_query($sql);
	if ($init_size == $size){
		while($marker = mysql_fetch_assoc($res)){
			array_push($data, intval($marker["marker_uid"]));
			echo $marker["marker_name"] . " ";
		}
		echo "\n";
	}

	$sql = "SELECT line_record_name, line_record_uid FROM line_records WHERE 1 LIMIT $begin, 1";
	$res = mysql_query($sql);

	while($line = mysql_fetch_assoc($res)){
	
	       	echo str_replace(' ', '_', $line["line_record_name"]) . " ";

		foreach ($data as $marker_id){
			echo getValue(intval($line["line_record_uid"]), $marker_id) . " ";
		}
	}

	$size -= 1;
	$offset += 1;
	echo "\n";
}

function getValue($line_id, $marker_id){


	$sql = <<< SQL
	
	SELECT
		alleles.value
	FROM
	        alleles,
	        genotyping_data,
	        tht_base,
	        markers,
	        line_records
	WHERE
	        line_records.line_record_uid = '$line_id'
	        AND markers.marker_uid = '$marker_id'
	        AND tht_base.line_record_uid = line_records.line_record_uid
	        AND genotyping_data.marker_uid = markers.marker_uid
	        AND tht_base.tht_base_uid = genotyping_data.tht_base_uid
                AND genotyping_data.genotyping_data_uid = alleles.genotyping_data_uid
SQL;

	$res = mysql_query($sql);
	
	$num_rows = mysql_num_rows($res);
	if ($num_rows > 0){
		$row = mysql_fetch_assoc($res);
		return convert($row["value"]);
	}
	return "NA";
}

function convert($value){
	if ($value == 'A') return '-1';
	else if ($value == 'B') return '1';
}*/

?>
