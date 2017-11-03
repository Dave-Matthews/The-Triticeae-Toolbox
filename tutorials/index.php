<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header2.php';
?>

<h1>Tutorials</h1>
<h2>Analysis, Reports</h2>
<ol>
<li><a href=tutorials/variant_effect.php>Variant Effects Report</a>
<li><a href=tutorials/blast.php>BLAST Analysis</a>
</ol>
<h2>Data Submission</h2>
<ol>
<?php
$files = scandir($config['root_dir'] . "curator_data/tutorial");
foreach ($files as $item) {
    if (preg_match("/(^[^_]+)_([^\s]+)/", $item, $match)) {
        $item_tag = $match[1];
        $item_des = $match[2];
    } else {
        $item_tag = $item;
        $item_des = $item;
    }
    if (preg_match('/(pdf|html|pptx)/', $item_des)) {
        $item_clean = preg_replace("/\.pdf/", "", $item_des);
        $item_clean = preg_replace("/\.html/", "", $item_clean);
        $item_clean = preg_replace("/\.pptx/", "", $item_clean);
        $item_clean = preg_replace("/_/", " ", $item_clean);
        echo "<li><a href=\"" . "curator_data/tutorial/" . "$item\">$item_clean</a>\n";
    }
}
?>
</ol>
</div>
<?php
require $config['root_dir'].'theme/footer.php';
