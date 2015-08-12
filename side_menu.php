<?php

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';

?>
<h2>Quick Links </h2>
<ul>
<?php
if (isset($_SESSION['username']) && !isset( $_REQUEST['logout'])) :
    ?>
    <li>
    <a title="Logout" href="<?php echo $config['base_url']; ?>logout.php">Logout <span style="font-size: 10px">(<?php echo $_SESSION['username'] ?>)</span></a>
    <?php
else :
    ?>
    <li>
    <a title="Login" href="<?php echo $config['base_url_ssl']; ?>login.php"><strong>Login/Register</strong></a>
    <?php
endif;
echo "<p><li><b>Current selections:</b>";
echo "<li><a href='".$config['base_url']."pedigree/line_properties.php'>Lines</a>: ". count($_SESSION['selected_lines']);
echo "<li><a href='".$config['base_url']."genotyping/marker_selection.php'>Markers</a>: ";
if (isset($_SESSION['clicked_buttons'])) {
    echo count($_SESSION['clicked_buttons']);
} elseif (isset($_SESSION['geno_exps_cnt'])) {
    echo number_format($_SESSION['geno_exps_cnt']);
} else {
    echo "All";
}
echo "<li><a href='".$config['base_url']."phenotype/phenotype_selection.php'>Traits</a>: ". count($_SESSION['selected_traits']);
echo "<li><a href='".$config['base_url']."phenotype/phenotype_selection.php'>Phenotype Trials</a>";
if (isset($_SESSION['selected_trials'])) {
    echo ": " . count($_SESSION['selected_trials']);
}
echo "<li><a href='".$config['base_url']."genotyping/genotype_selection.php'>Genotype Experiments</a>";
if (isset($_SESSION['geno_exps'])) {
    echo ": " . count($_SESSION['geno_exps']);
}
?>
			
			
  <br><br><li>
  <form style="margin-bottom:3px" action="search.php" method="post">
  <input type="hidden" value="Search" >
  <input style="width:170px" type="text" name="keywords" value="Quick search..." onfocus="javascript:this.value=''" onblur="javascript:if(this.value==''){this.value='Quick search...';}" >
  </form>
  <br>
  </div>
<div  style="margin-left: -25px; width: 170px; padding: 10px 15px;">
<?php require $config['root_dir'].'whatsnew.html'; ?>
</div>

