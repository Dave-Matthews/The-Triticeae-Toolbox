<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header.php';
$mysqli = connecti();
?>
<script src="<?php echo $config['base_url']?>analyze/boxplot.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $config[base_url]?>analyze/boxplot.css" />

<h1>Boxplot summaries of Trait values in selected Trials</h1>

<?php 
$trials = $_SESSION['selected_trials'];
$traits = $_SESSION['selected_traits'];
if (!$trials OR !$traits) 
  echo "Please select at least one <a href='$config[base_url]phenotype/phenotype_selection.php'>Trait and Trial</a>.<p>";
else {
  foreach ($traits as $trait) {
    $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
    print "<b>$trtname</b>";
    print "<table><tr>";
    foreach ($trials as $trial) {
      $name = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
      $valuecount = mysql_grab("select count(p.value)
			      from tht_base t, phenotype_data p
			      where t.tht_base_uid = p.tht_base_uid
			      and t.experiment_uid = $trial
			      ");
      print "<td style='position:relative; height:250px; vertical-align:top; 
             width:150px'><a href='display_phenotype.php?trial_code=$name'>$name</a><br>";
      print "n = <b>$valuecount</b><p>";
      // Construct a div id for this table cell.
      print "<div id=bp-$trait-$trial></div></td>";
      $sql = "select pd.value
            from tht_base t, phenotype_data pd
            where t.experiment_uid = $trial
            and t.tht_base_uid = pd.tht_base_uid
            and pd.phenotype_uid = $trait";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      $vals = array();
      while ($row = mysqli_fetch_array($res)) {
          $vals[] = round($row[0], 1);
      }
      $vlst = implode(",", $vals);
      $vallist[$trait][$trial] = (string) $vlst;
      ?>
      <script type="text/javascript">
      var data = new Array(<?php echo $vallist[$trait][$trial] ?>);
      var cell = '<?php echo "bp-$trait-$trial" ?>';
      createBoxPlot(data, 200, cell);
      </script>
<?php
  }
    print "</table>";
  }
}
?>
<div class='section' style='font-size:90%'>
<b>Legend</b><br> 
<table><tr><td><img src=images/Boxplot_vs_PDF.png>
<td style='vertical-align:top'>
Median and quartiles as described <a href="http://en.wikipedia.org/wiki/Quartile">here</a>.
If the minimum or maximum value is less than 1.5 x IQR, the whisker is shown at that point instead.<p>
  <a href="http://informationandvisualization.de/blog/box-plot">Display software</a> Copyright (c) 2010, Fabian Dill
<tr><td>"<a href="http://commons.wikimedia.org/wiki/File:Boxplot_vs_PDF.svg#mediaviewer/File:Boxplot_vs_PDF.svg">Boxplot vs PDF</a>" by <a href="//en.wikipedia.org/wiki/User:Jhguch" class="extiw" title="en:User:Jhguch">Jhguch</a> at <a class="external text" href="http://en.wikipedia.org">en.wikipedia</a>. Licensed under <a title="Creative Commons Attribution-Share Alike 2.5" href="http://creativecommons.org/licenses/by-sa/2.5">CC BY-SA 2.5</a> via <a href="//commons.wikimedia.org/wiki/">Wikimedia Commons</a>
</table>
</div>

<?php
$footer_div=1;
require $config['root_dir'].'theme/footer.php'; 
