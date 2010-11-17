<?php
header("Content-type:text/plain");
include("includes/bootstrap.inc");
connect();
$sql =  <<< SQL
    SELECT
        id,
        name,
        parent_id,
        contribution,
        line_record_name parent_name
    FROM
        line_records
    JOIN
        (SELECT
            l.line_record_uid id,
            line_record_name name,
            parent_id,
            contribution
        FROM line_records l
        JOIN pedigree_relations p
        ON l.line_record_uid = p.line_record_uid) t1
    ON t1.parent_id = line_record_uid
    ORDER BY name ASC
SQL;

$query = mysql_query($sql) or die(mysql_error());
if (mysql_num_rows($query) > 0)
{
    $row1 = mysql_fetch_assoc($query);
    $row2 = mysql_fetch_assoc($query);
    while ($row1 !== FALSE || $row2 !== FALSE)
    {
        if ($row1['name'] == $row2['name'])
		{
		$name = str_replace(" ", "_", $row1['name']);
        $p1name = str_replace(" ", "_", $row1['parent_name']);
        $p2name = str_replace(" ", "_", $row2['parent_name']);
        $p1contrib = $row1['contribution'];
        $p2contrib = $row2['contribution'];
        echo "$name $p1name $p2name $p1contrib $p2contrib\n";
        $row1 = mysql_fetch_assoc($query);
        $row2 = mysql_fetch_assoc($query);
		}
		else
		{
		$row1=$row2;
		$row2 = mysql_fetch_assoc($query);
		}
    } 
}
?>
