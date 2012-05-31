<?php
/**
 * Display and modify phenotype selection saved in session variable
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/pedigree/phenotype_selection.php
 * 
 */
session_start();

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();
include($config['root_dir'] . 'theme/admin_header.php');

?>

<style type="text/css">
  table th {background: #5B53A6 !important; color: white !important; text-align: left; padding: 3px;}
  <!-- h3 {border-left: 4px solid #5B53A6; padding-left: .5em;} -->
  table tr {min-height: 0px;}
  table td {padding: 3px;}
</style>

<?php

  // Deselect highlighted cookie phenotype.
  if (isset($_POST['deselLines'])) {
    $selected = $_SESSION['phenotype'];
    $ntraits=substr_count($_SESSION['phenotype'], ',')+1;
    if ($ntraits > 1) {
      $phenotype_ary = explode(",",$_SESSION['phenotype']);
      foreach ($_POST['deselLines'] as $uid)
        if (($lineidx = array_search($uid, $phenotype_ary)) !== false) {
          array_splice($phenotype_ary, $lineidx,1);
        }
      $_SESSION['phenotype']=implode(",",$phenotype_ary);
    } else {
      unset($_SESSION['phenotype']);
    }
  }

  if (!isset($_SESSION['phenotype'])) {
    echo "No selected phenotypes<br>";
    die("");
  }

  // Show "Currently selected penotypes" box.
  $ntraits=substr_count($_SESSION['phenotype'], ',')+1;
  $display = $_SESSION['phenotype'] ? "":" style='display: none;'";
  echo "<div id='squeeze' $display>";
  echo "<td><b><font color=blue>Currently selected traits</font>: $ntraits</b>";
  print "<form id=\"deselLinesForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
  print "<select name=\"deselLines[]\" multiple=\"multiple\" style=\"height: 15em;width: 13em\">";
  if ($ntraits > 1) {
    $phenotype_ary = explode(",",$_SESSION['phenotype']);
    foreach ($phenotype_ary as $uid) {
      $result=mysql_query("select phenotypes_name from phenotypes where phenotype_uid=$uid") or die("invalid line uid\n");
      while ($row=mysql_fetch_assoc($result)) {
        $selval=$row['phenotypes_name'];
        print "<option value=\"$uid\" selected>$selval</option>\n";
      }
    }
  } else {
    $uid = $_SESSION['phenotype'];
    $result=mysql_query("select phenotypes_name from phenotypes where phenotype_uid=$uid") or die("invalid line uid\n");
    $row=mysql_fetch_assoc($result);
    $selval=$row['phenotypes_name'];
    print "<option value=\"$lineuid\" selected>$selval</option>\n";
  }
  print "</select>";
  print "<br><input type='submit' name='WhichBtn' value='Deselect highlighted traits' />";
  print "</form>";
	
  /* print "<form id='showPedigreeInfo' action='pedigree/pedigree_info.php' method='post' $display1>"; */
  /* print "<input type='submit' name='WhichBtn' value='Show line information'></form>"; */
  /* print "<button onclick=\"location.href='".$config['base_url']."pedigree/pedigree_info.php'\">Show line information</button>"; */
  echo "</div>";  // id=squeeze
  print "</td></tr></table>";
  print "</div>";

require $config['root_dir'] . 'theme/footer.php';
?>
