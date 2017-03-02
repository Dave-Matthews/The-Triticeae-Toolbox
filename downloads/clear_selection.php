<?php
/**
 * Clear saved selection
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/clear_selection.php
 * 
 */

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/normal_header.php';
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
    unset($_SESSION['selected_map']);
    unset($_SESSION['check_lines']);
    unset($_SESSION['geno_exps']);
    unset($_SESSION['geno_exps_cnt']);
    $_SESSION['training_traits'] = null;
    unset($_SESSION['training_traits']);
    $_SESSION['training_trials'] = null;
    unset($_SESSION['training_trials']);
    if (isset( $_SESSION['username'] ) && !isset( $_REQUEST['logout'] )) {
        $user = $_SESSION['username'];
        clearSessionVariables($user);
    }
    ?>
    Finished clearing selection    
    <script type="text/javascript">
      update_side_menu();
    </script>
    <?php
} elseif (isset($_GET['clearSes'])) {
    clear_session_variables($user);
} else {
    ?>
    Clear selection of Lines, Markers, Traits, and Trials<br><br>
    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="GET">
    <input type=hidden name=clearSel>
    <input type="submit" value="Clear current selection"/>
    </form>
    <?php
}

