<?php

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';

  ?>
  <h2>Quick Links </h2>
  <ul>
  <?php if ( isset( $_SESSION['username'] ) && !isset( $_REQUEST['logout'] ) ):  ?>
    <li>
       <a title="Logout" href="<?php echo $config['base_url']; ?>logout.php">Logout <span style="font-size: 10px">(<?php echo $_SESSION['username'] ?>)</span></a>
            <?php else: ?>
    <li>
      <a title="Login" href="<?php echo $config['base_url']; ?>login.php"><strong>Login/Register</strong></a>
   <?php endif; ?>

   <?php
   echo "<p><li><b>Current selections:</b>";
   echo "<li><a href='".$config['base_url']."pedigree/line_selection.php'>Lines</a>: ". count($_SESSION['selected_lines']);
   echo "<li><a href='".$config['base_url']."genotyping/marker_selection.php'>Markers</a>: ";
   if (isset($_SESSION['clicked_buttons'])) {
     echo count($_SESSION['clicked_buttons']);
   } elseif (isset($_SESSION['filtered_markers'])) {
     echo count($_SESSION['filtered_markers']);
   } else {
     echo "All";
   }
   echo "<li><a href='".$config['base_url']."phenotype/phenotype_selection.php'>Traits</a>: ". count($_SESSION['selected_traits']);
   echo "<li><a href='".$config['base_url']."phenotype/phenotype_selection.php'>Trials</a>: ". count($_SESSION['selected_trials']);
?>
			
			
  </ul>
  <div id="searchbox">
  <form style="margin-bottom:3px" action="search.php" method="post">
  <div style="margin: 0; padding: 0;">
  <input type="hidden" value="Search" >
  <input style="width:170px" type="text" name="keywords" value="Quick search..." onfocus="javascript:this.value=''" onblur="javascript:if(this.value==''){this.value='Quick search...';}" >
  </div>
  </form>
  <br>
<!--  <a href="<?php echo $config['base_url']; ?>advanced_search.php">Advanced Search</a> -->
  </div>
<div id="quicklinks"  style="top:230px;left:0px; width: 170px; padding: 10px 15px;">
<?php include($config['root_dir'].'whatsnew.html'); ?>
</div>

