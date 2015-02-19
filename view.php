<?php
require "includes/bootstrap.inc";
$mysqli = connecti();
require "theme/normal_header.php";

// Will take both uids and names
// DEM sep2014: But treat them differently!

$table = $_REQUEST['table'];
$pkey = get_pkey($table);
$name = get_unique_name($table);
$prettified = beautifulTableName($table, 0);
// CLB feb2015: Can not do a prepared statement for select * query so next best thing is to sanitize input
$id = intval($_REQUEST['uid']);
$nm = $_REQUEST['name'];
if ($id) {
    // Argument is a record uid.
    $sql = "SELECT * FROM $table WHERE $pkey = $id";
    $record = mysqli_query($mysqli, $sql);
    if (mysqli_num_rows($record) > 0) {
        $row = mysqli_fetch_assoc($record);
        $n = $row[$name];
        echo "<h1>$prettified $n</h1>";
        echo "<div class=boxContent>";
        $func = "show_" . $table;
        // Is there a custom function for that table in includes/general.inc
        // or includes/pedigree.inc?  examples:
        //  line_records = includes/pedigree.inc/show_line_records()
        //  markers = includes/general.inc/show_markers()
        //  breeding_programs = includes/general.inc/show_breeding_programs()
        if (function_exists($func)) {
            call_user_func($func, $row[$pkey]);
        } else {
            // Default to raw table dump using includes/general.inc:show_general().
            show_general($table, $row[$pkey]);
        }
        echo "</div>";
    } else {
        error(1, "No Record Found");
    }
} elseif ($nm) {
    // Argument is a record name.
    $sql = "SELECT $pkey FROM $table WHERE $name = '$nm'";
    if ($stmt = mysqli_prepare($mysqli, "SELECT $pkey FROM $table WHERE $name = ?")) {
        mysqli_stmt_bind_param($stmt, "s", $nm);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $rec);
        mysqli_stmt_fetch($stmt);
        echo "<h1>$prettified $nm</h1>";
        echo "<div class=boxContent>";
        $func = "show_" . $table;
        if (function_exists($func)) {
            call_user_func($func, $rec);
        } else {
            show_general($table, $rec);
        }
        echo "</div>";
    } else {
        error(1, "No Record Found");
    }
}

echo "</div>";
require"theme/footer.php";
