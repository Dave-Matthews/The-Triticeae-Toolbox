<?php
require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
include($config['root_dir'].'theme/normal_header.php');
?>
<script type="text/javascript" src="theme/new.js"></script>
<?php
if (isset($_GET['clearSel'])) {
    unset($_SESSION['selected_lines']);
    unset($_SESSION['training_lines']);
    unset($_SESSION['phenotype']);
    unset($_SESSION['selected_traits']);
    unset($_SESSION['selected_trials']);
    unset($_SESSION['clicked_buttons']);
    unset($_SESSION['filtered_markers']);
    unset($_SESSION['filtered_lines']);
    $_SESSION['training_traits'] = NULL; unset($_SESSION['training_traits']);
    $_SESSION['training_trials'] = NULL; unset($_SESSION['training_trials']);
    ?>
    <script type="text/javascript">
      update_side_menu();
    </script>
    Finished clearing selection
    <?php
} else {
   ?>
   Clear selection of Lines, Markers, Traits, and Trials<br><br>
   <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="GET">
   <input type=hidden name=clearSel>
   <input type="submit" value="Clear current selection"/>
   </form>
   <?php
}

