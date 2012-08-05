<?php
/**
 * Download Gateway
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <cbirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 * 
 */

// For live website file
require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
set_include_path(GET_INCLUDE_PATH . PATH_SEPARATOR . '../pear/');
date_default_timezone_set('America/Los_Angeles');

// connect to database
connect();

new Downloads($_GET['function']);

/** Using a PHP class to implement the "Download Gateway" feature
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
class Downloads
{   
    /**
     * delimiter used for output files
     */
    public $delimiter = "\t";
    
    /** 
     * Using the class's constructor to decide which action to perform
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {	
        switch($function)
        {
            case 'type1':
                $this->type1();
                break;
			case 'step1phenotype':
				$this->step1_phenotype();
				break;
			case 'step2phenotype':
				$this->step2_phenotype();
				break;
			case 'step3phenotype':
				$this->step3_phenotype();
				break;
			case 'step4phenotype':
			    $this->step4_phenotype();
			    break;
			case 'step5phenotype':
			    $this->step5_phenotype();
			    break;
			case 'refreshtitle':
			    echo $this->refresh_title();
			    break;
			default:
			    $this->type1_select();
			    break;
		}	
	}

	/**
	 * load header and footer then check session to use existing data selection
	 */
	private function type1_select()
	{
		global $config;
                include($config['root_dir'].'theme/normal_header.php');
		$phenotype = "";
                $lines = "";
		$markers = "";
		$saved_session = "";
		$this->type1_checksession();
		include($config['root_dir'].'theme/footer.php');
	}	
	
	/**
	 * Checks the session variable, if there is lines data saved then go directly to the lines menu
	 */
	private function type1_checksession()
    {
            ?>
            <style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<div id="title">
		<?php
            $phenotype = "";
            $this->refresh_title();
         ?>        
        </div>
		<div id="step1" style="float: left; margin-bottom: 1.5em;">
		<script type="text/javascript" src="phenotype/downloads.js"></script><br>
                <?php 
                $this->type1_phenotype();
                ?>
                </div>
                
                <?php
        }

    /**
     * 1. display a spinning activity image when a slow function is running
     * 2. show button to clear sessin data
     * 3. show button to save current selection
     */    
    private function refresh_title() {
      $command = (isset($_GET['cmd']) && !empty($_GET['cmd'])) ? $_GET['cmd'] : null;
      ?>
      <h2>Select Phenotypes</h2>
      <em>Select multiple options by holding down the Ctrl(PC) Command(Mac) key while clicking.</em><br>
      <em>Selecting traits and trials will NOT affect currently selected lines</em>
      <br><br>
      <?php 
      if ($command == "save") {
        if (isset($_GET['pi'])) {
          $_SESSION['phenotype'] = $_GET['pi'];
          $phenotype_ary = explode(",",$_SESSION['phenotype']);
          $_SESSION['selected_traits'] = $phenotype_ary;
        } else {
          echo "error - no traits selection found";
        }
        if (isset($_GET['exps'])) {
          $trials_ary = explode(",",$_GET['exps']);
          $_SESSION['selected_trials'] = $trials_ary;  
          $_SESSION['experiments'] = $_GET['exps'];
        } else {
          echo "error - no trials selection found";
        }
      } elseif ($command == "deselect") {
        if (isset($_GET['pi'])) {
          $deselect_str = $_GET['pi'];
          $deselect_ary = explode(",",$deselect_str);
          $ntraits=substr_count($_SESSION['phenotype'], ',')+1;
          if ($deselect_str == "") {
          } elseif ($ntraits > 1) {
            $phenotype_ary = explode(",",$_SESSION['phenotype']);
            foreach ($deselect_ary as $uid)
              if (($lineidx = array_search($uid, $phenotype_ary)) !== false) {
              array_splice($phenotype_ary, $lineidx,1);
            }
            $_SESSION['phenotype']=implode(",",$phenotype_ary);
            $_SESSION['selected_traits'] = $phenotype_ary;
          } else {
            unset($_SESSION['phenotype']);
            unset($_SESSION['selected_traits']);
          }
        } 
        if (isset($_GET['exp'])) {
          $deselect_str = $_GET['exp'];
          $deselect_ary = explode(",",$deselect_str);
          $trials_ary = $_SESSION['selected_trials'];
          $ntrials=count($_SESSION['selected_trials']);
          if ($deselect_str == "") {
          } elseif ($ntrials > 1) {
            foreach ($deselect_ary as $uid)
              if (($lineidx = array_search($uid, $trials_ary)) !== false) {
              array_splice($trials_ary, $lineidx,1);
            }
            $_SESSION['selected_trials'] = $trials_ary;
            $_SESSION['experiments'] = implode(',',$trials_ary);
          } else {
            unset($_SESSION['selected_trials']);
            unset($_SESSION['experiments']);
          }
        }
      }
      if (isset($_SESSION['phenotype'])) {
        $ntraits=substr_count($_SESSION['phenotype'], ',')+1;
        echo "<table>";
        echo "<tr><th>Currently selected traits</th><td><th>Currently selected trials</th>";
        print "<tr><td><select name=\"deselLines[]\" multiple=\"multiple\" style=\"width: 13em\" onchange=\"javascript: remove_phenotype_items(this.options)\">";
        //if ($ntraits > 1) {
          $phenotype_ary = explode(",",$_SESSION['phenotype']);
          foreach ($phenotype_ary as $uid) {
            $result=mysql_query("select phenotypes_name from phenotypes where phenotype_uid=$uid") or die("invalid line uid\n");
            while ($row=mysql_fetch_assoc($result)) {
              $selval=$row['phenotypes_name'];
              print "<option value=\"$uid\" >$selval</option>\n";
            }
          }
        //} else {
        //  $uid = $_SESSION['phenotype'];
        //  $result=mysql_query("select phenotypes_name from phenotypes where phenotype_uid=$uid") or die("invalid line uid\n");
        //  if ($row=mysql_fetch_assoc($result)) {
        //    $selval=$row['phenotypes_name'];
        //    print "<option value=\"$lineuid\">$selval</option>\n";
        //  } else {
        //    die("$uid not found\n");
        //  }
       // }
        print "</select>";
        echo "<td><td><select name=\"deseLines[]\" multiple=\"multiple\" style=\"width: 13em\" onchange=\"javascript: remove_trial_items(this.options)\">";
        if (isset($_SESSION['selected_trials'])) {
          $trials_ary = $_SESSION['selected_trials'];
          foreach ($trials_ary as $uid) {
            $result=mysql_query("select trial_code from experiments where experiment_uid=$uid") or die("invalid line uid\n");
            while ($row=mysql_fetch_assoc($result)) {
              $selval=$row['trial_code'];
              print "<option value=\"$uid\" >$selval</option>\n";
            }
          }
        }
        print "</select>";
        ?>
        <tr><td><input type="button" value="Deselect highlighted traits" onclick="javascript:phenotype_deselect();" /></td><td><td>
        <input type="button" value="Deselect highlighted trials" onclick="javascript:trials_deselect();" />
        </table>
  
        <br>
        <?php 
      }
     
      $selection_ready = 0;
      if (isset($_GET['lines']) && !empty($_GET['lines'])) {
        $selection_ready = 1;
      } elseif (isset($_GET['pi']) && !empty($_GET['pi'])) {
        $selection_ready = 1;
      } elseif (isset($_GET['e']) && !empty($_GET['e'])) {
        $selection_ready = 1;
      }
      
    }
    
	/**
	 * starting with phenotype display phenotype categories
	 */
	private function step1_phenotype()
	{
		?>
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Category</th>
		</tr>
		<tr><td>
			<select name="phenotype_categories" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_categories(this.options)">
                <?php
		$sql = "SELECT phenotype_category_uid AS id, phenotype_category_name AS name from phenotype_category";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
				<option value="<?php echo $row['id'] ?>">
					<?php echo $row['name'] ?>
				</option>
				<?php
		}
		?>
		</select>
		</td>
		</table>
		
		<?php
	}

	/**
	 * starting with phenotype display phenotype items
	 */
	private function step2_phenotype()
    {  
		$phen_cat = $_GET['pc'];
		?><br>
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Traits</th>
		</tr>
		<tr><td>
		<select id="traitsbx" name="phenotype_items" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_items(this.options)">
                <?php

		$sql = "SELECT phenotype_uid AS id, phenotypes_name AS name from phenotypes, phenotype_category
		 where phenotypes.phenotype_category_uid = phenotype_category.phenotype_category_uid and phenotype_category.phenotype_category_uid in ($phen_cat)";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		}
		?>
		</select>
		</table>
		
		<?php
    }
    
    /**
     * starting with phenotype display trials
     */
	private function step3_phenotype()
    {  
		$phen_item = $_GET['pi'];
		$trait_cmb = (isset($_GET['trait_cmb']) && !empty($_GET['trait_cmb'])) ? $_GET['trait_cmb'] : null;
		if ($trait_cmb == "all") {
		   $any_ckd = ""; $all_ckd = "checked";
		} else {
		   $trait_cmb = "any";
		   $any_ckd = "checked"; $all_ckd = "";
		}
		?><br>
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Trials</th>
		</tr>
		<tr><td>
                <select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_trial(this.options)">
                <?php

		$sql = "SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name, p.phenotype_uid 
	 FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
	 WHERE
	 e.experiment_uid = tb.experiment_uid
	 AND pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND pd.phenotype_uid IN ($phen_item)";
	 if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
	 $sql .= " and data_public_flag > 0";
	 $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		  $exp_uid = $row['id'];
		  $pi = $row['phenotype_uid'];
		  $sel_list[$exp_uid] = $row['name'];
		  $pi_list[$exp_uid][$pi] = 1;        //*array of traits for each trial
		}
		$phen_array = explode(",",$phen_item);
		foreach ($sel_list as $id=>$name) {
		  $found = 1;
		  foreach ($phen_array as $item) {    //*check if trial contains all trait
		    if (!isset($pi_list[$id][$item])) {
		       $found = 0;
		    }
		  }
		  if ($found || ($trait_cmb == "any")) {
		  ?>
		  <option value="<?php echo $id ?>">
		  <?php echo $name ?>
		  </option>
		  <?php
		  }
		}
		?>
		</select>
		</table>
		<?php 
		$tmp = count($phen_array);
		if ($tmp > 1) {
		  ?>
		  <input type="radio" id="trait_cmb" value="all" <?php echo "$all_ckd"; ?> onclick="javascript: update_phenotype_trialb(this.value)">trials with all traits<br>
		  <input type="radio" id="trait_cmb" value="any" <?php echo "$any_ckd"; ?> onclick="javascript: update_phenotype_trialb(this.value)">trials with any trait<br>
		  <?php
		}
    }
    
    /**
     * starting with phenotype display lines
     */
	private function step4_phenotype()
    {  
    	$phen_item = $_GET['pi'];
		$experiments = $_GET['e'];
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
		$selected_lines = array();
		$_SESSION['phenotype'] = $phen_item; // Empty the session array.
		
		if (count($_SESSION['selected_lines']) > 0) {
		 $sub_ckd = ""; $all_ckd = "checked";
		} else {
		 $sub_ckd = "disabled"; $all_ckd = "checked";
		}
		if ($subset == "yes") {
		 $sub_ckd = "checked"; $all_ckd = "";
		} elseif ($subset == "no") {
		 $sub_ckd = ""; $all_ckd = "checked";
		} elseif ($subset == "comb") {
		 $sub_ckd = ""; $cmb_ckd = "checked";
		}
		?>
		<p>4.
		<select name="select1">
		  <option value="BreedingProgram">Lines</option>
		</select></p>
		
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Lines</th>
		</tr>
		<tr><td>
		<select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
                <?php
        if ($sub_ckd == "checked") {
          $lines_list = array();
          $lines_new = "";
          $selected_lines = $_SESSION['selected_lines'];
          foreach ($selected_lines as $line) {
            $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_assoc($res);
            ?>
            <option selected value="<?php echo $row['id'] ?>">
            <?php echo $row['name'] ?>
            </option>
            <?php
          }
        } elseif ($cmb_ckd == "checked") {
          $selected_lines = $_SESSION['selected_lines'];
          foreach ($selected_lines as $line) {
            $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_assoc($res);
            ?>
           <option selected value="<?php echo $row['id'] ?>">
           <?php echo $row['name'] ?>
           </option>
           <?php
         }
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           $temp1 = $row['name'];
           $temp2 = $row['id'];
           if (isset($lines_list[$temp2])) {
           } else {
             if ($lines_new == "") {
               $lines_new = $temp1;
               ?>
               <option disabled="disabled">--added--
               </option>
               <?php
             }
             ?>
             <option selected value="<?php echo $row['id'] ?>">
             <?php echo $row['name'] ?>
             </option>
             <?php
           }
         }
        } else {
		$sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name 
	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr 
	 WHERE
	 pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND lr.line_record_uid = tb.line_record_uid
	 AND pd.phenotype_uid IN ($phen_item)
	 AND tb.experiment_uid IN ($experiments)
	 ORDER BY lr.line_record_name";
		//$_SESSION['selected_lines'] = array(); // Empty the session array.
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 //array_push($_SESSION['selected_lines'],$row['id']);
		 ?>
				<option selected value="<?php echo $row['id'] ?>">
					<?php echo $row['name'] ?>
				</option>
				<?php
		}
        }
		?>
		</select>
		</table>	
		<?php
		if (count($_SESSION['selected_lines']) > 0) {
		  ?>
		  <input type="radio" name="subset" id="subset" value="yes" <?php echo "$sub_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Include only <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php">currently
		  selected lines</a><br>
		  <input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Use lines with selected <b>Trials</b> and <b>Traits</b><br>
		  <input type="radio" name="subset" id="subset" value="comb" <?php echo "$cmb_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Combine two sets<br>
		  <?php
		}
    }
    
    /**
     * starting with phenotype display marker data
     */
    private function step5_phenotype()
    {
     $phen_item = $_GET['pi'];
     $experiments = $_GET['e'];
     $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;

     ?>
     <input type="button" value="Save Phenotype Selection" onclick="javascript:phenotype_save();" /><br><br>
     <?php

     if (isset($_GET['pi']) && !empty($_GET['pi'])) {
     	$sel_phen = explode(',',$phen_item);
     	$sel_expr = explode(',',$experiments);
        echo "<table><tr><td>Traits<td>Trials<td>Number of Lines";    
     	foreach ($sel_phen as $p_uid) {
     		$sql = "select phenotypes_name from phenotypes where phenotype_uid = $p_uid";
     		$res1 = mysql_query($sql) or die(mysql_error());
     		while ($row1 = mysql_fetch_array($res1)) {
     			$p_name = $row1[0];
     			foreach ($sel_expr as $e_uid) {
     			  $sql = "select trial_code from experiments where experiment_uid = $e_uid";
     			  $res2 = mysql_query($sql) or die(mysql_error());
     			  while ($row2 = mysql_fetch_array($res2)) {
     			    $e_name = $row2[0];
     			    $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
     			    FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
     			    WHERE
     			    pd.tht_base_uid = tb.tht_base_uid
     			    AND p.phenotype_uid = pd.phenotype_uid
     			    AND lr.line_record_uid = tb.line_record_uid
     			    AND pd.phenotype_uid = $p_uid
     			    AND tb.experiment_uid = $e_uid";
     			    $res3 = mysql_query($sql) or die(mysql_error());
     			    $l_count = 0;
     			    while ($row3 = mysql_fetch_array($res3)) {
     			      $l_count++;
     			    }
     			    echo "<tr><td>$p_name<td>$e_name<td>$l_count";
     			  }
     		    }
     	    }
     	}
     	echo "</table><br>";
     }
    }

	/**
	 * main entry point when there is a phenotype selection
	 */
    private function type1_phenotype()
    {
		?>
		<div id="step11">
		<?php
	    $this->step1_phenotype();
		?>
	    </div></div>    
	    <div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step4b" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
	    </div>
	     <?php 	
	}
}	