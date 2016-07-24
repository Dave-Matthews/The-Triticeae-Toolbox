<?php
/**
 * browse.php, DEM apr2015
 * Display the hits from Quick Search in a tidy pageable table.
 */

require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header.php';
$mysqli = connecti();

$table = mysqli_real_escape_string($mysqli, $_GET['table']);
$column = mysqli_real_escape_string($mysqli, $_GET['col']);
$keywords = mysqli_real_escape_string($mysqli, $_GET['keywords']);
$page = 1;
if ($_GET['page']) {
    $page = $_GET['page'];
}
// Print the hit names in 5 columns, top to bottom first.
$numcols = 5;
$numrows = 10;
$pagesize = $numcols * $numrows;

// Make the searchTree with the names of the columns to be searched.
/* $names = explode(',', $column); */
/* $searchTree[$table] = $names; */
$searchTree[$table] = array($column);

// Process the $keywords.
// Remove the \ characters inserted before quotes by magic_quotes_gpc.
$keywords = stripslashes($keywords);
// If the input is doublequoted, don't split at <space>s.
if (preg_match('/^".*"$/', $keywords)) {
    $keywords = trim($keywords, "\"");
    $found = generalTermSearch($searchTree, $keywords);
} else {
    /* Break into separate words and query for each. */
    $words = explode(" ", $keywords);
    for ($i=0; $i<count($words); $i++) {
        if (trim($words[$i]) != "") {
            // Return only items that contain _all_ words (AND) instead of _any_ of them (OR).
            $partial[$i] = generalTermSearch($searchTree, $words[$i]);
        }
    }
    $found = $partial[0];
    for ($i = 1; $i < count($words); $i++) {
        $found = array_intersect($found, $partial[$i]);
        // Reset the (numeric) key of the array to start at [0].
        $found = array_merge($found);
    }
}

if (is_array($found)) {
    foreach ($found as $v) {
        // $v is "<table>@@<column>@@<uid>".
        $line = explode("@@", $v);
        // Omit marker synonyms that are identical to marker name.
        $skip = "";
        if (($line[0] == "marker_synonyms") && ($line[1] == "value")) {
            $msquery = mysqli_query($mysqli, "select marker_name 
                    from markers, marker_synonyms 
                    where marker_synonym_uid = '$line[2]'
                    and markers.marker_uid = marker_synonyms.marker_uid
                    and markers.marker_name = marker_synonyms.value");
            if (mysqli_num_rows($msquery) > 0) {
                $skip = "yes";
            }
        }
        if (! $skip) {
            if ($table == 'phenotype_experiment_info') {
                // Fetch the info about the parent record in table experiments.
                $uids[] = mysql_grab("select experiment_uid from $table where phenotype_experiment_info_uid = $line[2]");
                $table = 'experiments';
            } elseif ($table == 'genotype_experiment_info') {
                $uids[] = mysql_grab("select experiment_uid from $table where genotype_experiment_info_uid = $line[2]");
                $table = 'experiments';
            } else {
                $uids[] = $line[2];
            }
        }
        $key = get_pkey($table);
        $uniqname = get_unique_name($table);
    }
}
if (is_array($uids)) {
    $uidlist = implode(',', $uids);
} else {
    die("Error: no matching entries found");
}

$tablelabel = beautifulTableName($table)."s"; // for display
// Rename phenotype experiments as "Trials".
if ($table == "experiments") {
    $tablelabel = "Trials";
}
// Use a better class name than the table name:
if ($tablelabel == 'Experiment Sets') {
    $tablelabel = 'Experiments';
}

// Alphabetize
$sql = "select $key, $uniqname from $table where $key in ($uidlist) order by $uniqname";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli)."<br>Query was:<br>$sql");
while ($record = mysqli_fetch_row($res)) {
    $records[] = array($record[0], $record[1]);
};
$numrecords = count($records);

// Begin output to the webpage.
print "<div class='section'>";
print "<h1>$tablelabel</h1>";
print "<table>";
// row, column and cell count from 0; page counts from 1.
for ($rw = 0; $rw < $numrows; $rw++) {
    print "<tr>";
    for ($clm = 0; $clm < $numcols; $clm++) {
        $cell = (($page - 1) * $pagesize) + ($clm * $numrows + $rw);
        if ($cell < $numrecords) {
            $uid = $records[$cell][0];
            $name = $records[$cell][1];
            // Intercept experiments and route to display_phenotype.php or display_genotype.php.
            if ($table == "experiments") {
                $sql = "select experiment_type_uid from experiments where trial_code = \"$name\"";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                $record = mysqli_fetch_row($res);
                $expttype = $record[0];
                if ($expttype == 1) {
                    print "<td><a href='display_phenotype.php?trial_code=$name'>$name</a>";
                } else {
                    print "<td><a href='display_genotype.php?trial_code=$name'>$name</a>";
                }
            } else {
                print "<td><a href='view.php?table=$table&uid=$uid'>$name</a>";
            }
        }
    }
}
print "</table>";

// Pager
if ($numrecords > $pagesize) {
  $numpages = ceil($numrecords / $pagesize);
  print "Page ";
  print "<input type=text size=3 value=$page onchange=\"window.open('browse.php?table=$table&page='+this.value+'&col=$_GET[col]&keywords=$_GET[keywords]', '_self')\">";
  print " of <b>$numpages</b> <input type=button value='Go'><br>";
  print "<select onchange=\"window.open('browse.php?table=$table&page='+this.options[this.selectedIndex].value+'&col=$_GET[col]&keywords=$_GET[keywords]', '_self')\">";
  // Divide the number of pages into at most 20 lumps.
  $lumps = $numpages;
  while ($lumps > 20)
    $lumps = ceil($lumps / 3);
  $lumpsize = floor($numpages / $lumps);
  for ($i = 0; $i < $lumps; $i++) {
    // Calculate the $records[] index of the top left item on the first page of this lump.
    $upperleft = $i * $lumpsize * $pagesize;
    $lumpname = $records[$upperleft][1];
    $lumppage = $i * $lumpsize + 1;
    if ($lumppage == $page)
      print "<option value=$lumppage selected>$lumpname ...</option>";
    else
      print "<option value=$lumppage>$lumpname ...</option>";
  }
  print "</select><p>";
}

print "</div>";
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
