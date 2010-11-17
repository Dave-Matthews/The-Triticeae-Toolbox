<?php
include("includes/bootstrap.inc");
connect();
include("cookie/cookie.php");

$cookie_name = $_SESSION['username']."_as_display_lines";
$tempcookie = new MyCookie($cookie_name);

class PhenotypeFilter{} // fixes a bug in PHP
$deformat = str_replace("'", '"', stripslashes($_POST['master_filter']));
$master_filter = unserialize($deformat);
$num_lines = count($master_filter->lines);

$reformat = str_replace('"', "'", $_POST['master_filter']);

$offset = 0;
if (isset($_GET['off'])) $offset = $_GET['off'];
$limit = 10;
if (isset($_GET['lim'])) $limit = $_GET['lim'];

if ($limit > $num_lines) $limit = $num_lines;

$prev_offset = $offset - $limit;
if ($prev_offset < 0) $prev_offset = 0;
$next_offset = $offset;
if (!($next_offset >= $num_lines - $limit)) $next_offset += $limit;

?>
<br />
<h4>Results</h4>
<p>Displaying <?=$limit?> of <?=count($master_filter->lines)?> result(s) starting at result <?=$offset+1?><br/>
Display <input id="limit" type="text" size="4" value="<?=$limit?>" /> lines at a time.
<input type="button" value="GO" onclick="new Effect.Opacity('results_tbl', {duration:0.2, from:1.0, to:0.0}); new Ajax.Updater('lines', 'as_display_lines.php?lim='+$F('limit')+'&off=<?=$offset?>', {asynchronous:true, method:'post', postBody:'master_filter=<?=$reformat?>'});">
</p>
<input <? if ($prev_offset == $offset) echo 'disabled="disabled"'; ?> type="button" value="Previous" onclick="new Effect.Opacity('results_tbl', {duration:0.2, from:1.0, to:0.0}); new Ajax.Updater('lines', 'as_display_lines.php?off=<?=$prev_offset?>', {asynchronous:true, method:'post', postBody:'master_filter=<?=$reformat?>'});"/>&nbsp;
<input <? if ($next_offset == $offset) echo 'disabled="disabled"'; ?> type="button" value="Next" onclick="new Effect.Opacity('results_tbl', {duration:0.2, from:1.0, to:0.0}); new Ajax.Updater('lines', 'as_display_lines.php?off=<?=$next_offset?>', {asynchronous:true, method:'post', postBody:'master_filter=<?=$reformat?>'});"/>
<div style="background: url('images/loading.png') center center no-repeat">
<div id="results_tbl" style="background:white">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<th>&nbsp;</th><th>Line</th><th>Data Set</th><th>Experiment</th><th>Year</th>
<?php

// generate the rest of the table header based on the first line
$line1 = array_shift($master_filter->lines);
$pheno_names = explode('|', $line1['phenotype_name']);
$pheno_vals = explode('|', $line1['value']);
$pheno_units = explode('|', $line1['unit_name']);
for($i=0; $i<count($pheno_names); $i++){
    echo "<th>$pheno_names[$i]<br/>($pheno_units[$i])</th>";
}
echo "</tr>";
array_unshift($master_filter->lines, $line1);

// ignore lines until the offset value is reached
for ($i=0; $i<$offset; $i++){
    array_shift($master_filter->lines);
}

// for every line until 30 lines
$count = 0;
foreach($master_filter->lines as $line){

    $this_offset = $offset + $count;
    
?>
<tr><td><input <? if ($tempcookie->contains_general($this_offset)){ echo 'checked="checked"'; } ?> type="checkbox" onchange="if (this.checked) {new Ajax.Request('select_general.php?name=<?=$cookie_name?>&id=<?=$this_offset?>', {asynchronous:false})} else {new Ajax.Request('select_general.php?delete=1&name=<?=$cookie_name?>&id=<?=$this_offset?>', {asynchronous:false})}" /></td>
<?php
    echo "<td><a href=\"view.php?table=line_records&uid={$line['id']}\">{$line['name']}</a></td>";
    echo "<td><a href=\"view.php?table=datasets&name={$line['dataset_name']}\">{$line['dataset_name']}</a></td>";
    echo "<td><a href=\"view.php?table=experiments&name={$line['experiment_name']}\">{$line['experiment_name']}</a></td>";
    echo "<td>{$line['year']}</td>";
    
    $pheno_cats = explode('|', $line['phenotype_category']);
    $pheno_names = explode('|', $line['phenotype_name']);
    $pheno_vals = explode('|', $line['value']);
    $pheno_units = explode('|', $line['unit_name']);
    
    for($i=0; $i<count($pheno_names); $i++){
        if (! (strtolower($pheno_cats[$i]) == 'year'))
            echo "<td>$pheno_vals[$i]</td>";
    }
    
    echo "</tr>";
    $count++;
    if($count == $limit) break;
}
echo "</table>";
?>
</div>
</div>
<input <? if ($prev_offset == $offset) echo 'disabled="disabled"'; ?> type="button" value="Previous" onclick="new Effect.Opacity('results_tbl', {duration:0.2, from:1.0, to:0.0}); new Ajax.Updater('lines', 'as_display_lines.php?off=<?=$prev_offset?>', {asynchronous:true, method:'post', postBody:'master_filter=<?=$reformat?>'});"/>&nbsp;
<input <? if ($next_offset == $offset) echo 'disabled="disabled"'; ?> type="button" value="Next" onclick="new Effect.Opacity('results_tbl', {duration:0.2, from:1.0, to:0.0}); new Ajax.Updater('lines', 'as_display_lines.php?off=<?=$next_offset?>', {asynchronous:true, method:'post', postBody:'master_filter=<?=$reformat?>'});"/>
