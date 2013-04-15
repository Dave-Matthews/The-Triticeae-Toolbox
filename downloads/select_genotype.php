<?php
/**
 * Download Gateway New
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/downloads/select_genotype.php
 */ 

require_once('config.php');
include($config['root_dir'].'includes/bootstrap.inc');
connect();

new SelectGenotypeExp($_GET['function']);

/** functions specific to genotype experiment
 @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/select_genotype.php
 */
class SelectGenotypeExp
{	
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
		        
			case 'type1experiments':
				$this->type1_experiments(); /* display experiments */
				break;
				
			case 'typeDownload':
				$this->type_Download(); /* display experiments */
				break;
				
			case 'typeDownload2':
			     $this->type_Download2();
			     break;
						
			case 'typeFlapJack':
				$this->type_Flap_Jack(); /* Handle Flap Jack Compatible download */
				break;
				
			case 'typeFlapJack2':
			    $this->type_Flap_Jack2();
			    break;
			    
		    case 'refreshtitle':
				echo $this->refresh_title();
				break;
				
		    case 'step1breedprog':
		        echo $this->step1_breedprog();
		        break;
		      
		    case 'step1lines':
		        echo $this->step1_lines();
		        break;
		        
		    case 'step2lines':
		        echo $this->step2_lines();
		        break;
                      
                    case 'step3lines':
                        echo $this->step3_lines();
                        break;
                 
                    case 'step1yearprog':
                        $this->step1_yearprog();
                        break;

                    case 'type1markers':
                        echo $this->type1_markers();
                        break;
			
			default:
			    $this->type1_select();
				break;
			
		}	
	}

/**
 * 1. display a spinning activity image when a slow function is running
 * 2. show button to clear sessin data
 * 3. show button to save current selection
 */
private function refresh_title()
{
   $command = (isset($_GET['cmd']) && !empty($_GET['cmd'])) ? $_GET['cmd'] : null;
   echo "<h2>Select Lines by Genotype Experiment</h2>";
   echo "<p>After saving, the line selection can be used for analysis or download. Select multiple options by holding down the Ctrl key while selecting.";
   if ($command == "save") {
      if (!empty($_GET['lines'])) {
          $lines_str = $_GET['lines'];
          $lines = explode(',', $lines_str);
          $_SESSION['selected_lines'] = $lines;
      } elseif (!empty($_GET['exps'])) {
          $experiments = $_GET['exps'];
          $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
          FROM tht_base as tb, line_records as lr
          WHERE lr.line_record_uid = tb.line_record_uid
          AND tb.experiment_uid IN ($experiments)
          ORDER BY lr.line_record_name";
          $lines = array();
          $res = mysql_query($sql) or die(mysql_error() . $sql);
          while ($row = mysql_fetch_assoc($res))
          {
            array_push($lines,$row['id']);
          }
          $lines_str = implode(",", $lines);
          $_SESSION['selected_lines'] = $lines;
        } else {
          echo "error - no selection found";
        }
        $username=$_SESSION['username'];
        if ($username) {
          store_session_variables('selected_lines', $username);
        }
      }
      /** if (isset($_GET['exps'])) {
          $trials_ary = explode(",",$_GET['exps']);
          $_SESSION['selected_trials'] = $trials_ary;
          $_SESSION['experiments'] = $_GET['exps'];
      } **/
   if (isset($_SESSION['selected_lines'])) {
     ?>
     <input type="button" value="Clear current selection" onclick="javascript: use_normal();"/>
     <?php
   }
}

/**
 * load header and footer
 */
private function type1_select()
{
     global $config;
     include($config['root_dir'].'theme/normal_header.php');
     $this->type1_checksession();
     include($config['root_dir'].'theme/footer.php');
}

/**
 * this handles the first first menu
 */
private function type1()
{
  unset($_SESSION['selected_lines']);
  unset($_SESSION['phenotype']);
  unset($_SESSION['clicked_buttons']);
  unset($_SESSION['filtered_markers']);

  ?>
  <p>
  <strong>Data Program</strong>
  <div id="step11" style="float: left; margin-bottom: 1.5em;">
  <?php
  $this->step1_breedprog();
  $footer_div = 1;
  ?>
  </div>
  <?php 
}

/**
 * Checks the session variable, then go directly to the data program
 */
private function type1_checksession()
{
  ?>
  <style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
  </style>
  <div id="title">
  <?php 
  if (isset($_SESSION['selected_lines'])) {
    $countLines = count($_SESSION['selected_lines']);
    $lines = $_SESSION['selected_lines'];
  }
  $this->refresh_title(); 
  echo "<img alt='spinner' id='spinner' src='images/ajax-loader.gif' style='display:none;' /></p>";
  ?>
  </div>
  <div id="step1" style="float: left; margin-bottom: 1.5em;">
  <p>
  <strong>Data Program</strong>
  <script type="text/javascript" src="downloads/genotype_flapjack.js"></script>
  <?php 
  $this->type_GenoType_Display();
  echo "</div>";
}

/**
 * display data program
 */
private function step1_breedprog()
{
  ?>
  <table>
  <tr>
  <td>
  <select name="breeding_programs" size="10" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
  <?php
  $sql = "SELECT DISTINCT dp.CAPdata_programs_uid AS id, dp.data_program_name AS name, dp.data_program_code AS code
                                FROM CAPdata_programs as dp, experiments as e, experiment_types as e_t
                                WHERE dp.CAPdata_programs_uid = e.CAPdata_programs_uid
                                AND e.experiment_type_uid = e_t.experiment_type_uid
                                AND e_t.experiment_type_name = 'genotype'
                                AND program_type='data' ORDER BY name";
 
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_assoc($res))
  {
  ?>
  <option value="<?php echo $row['id'] ?>"><?php echo $row['name']."(".$row['code'].")" ?></option>
  <?php
  }
  ?>
  </select>
  </td>
  </table>
  <?php 
}

/**
 * display year
 */
 private function step1_yearprog()
 {
    $CAPdata_programs = $_GET['bp'];
     ?>
    <div id="step21">
    <p>
    <strong>Year</strong>
    <table id="phenotypeSelTab" class="tableclass1">
    <tr><td>
    <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
    <?php
    $sql = "SELECT e.experiment_year AS year 
    FROM experiments AS e, experiment_types AS et
    WHERE e.experiment_type_uid = et.experiment_type_uid
    AND et.experiment_type_name = 'genotype'
    AND e.CAPdata_programs_uid IN ('$CAPdata_programs')
    GROUP BY e.experiment_year DESC";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_assoc($res))
    {
    ?>
    <option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
    <?php
    }
    ?>
    </select>
    </td>
    </table>
    </div>
    <?php
}

/**
 * main entry point when there is a line selection in session variable
 */
private function type1_lines_trial_trait()
{
  ?>
  <div id="step11" style="float: left; margin-bottom: 1.5em;">
  <?php
  $this->step1_lines();
  ?>
  </div></div>
  <div id="step2" style="float: left; margin-bottom: 1.5em;">
  <?php
  $this->step2_lines();
  ?>
  </div>
  <div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
  <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
  <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
  <script type="text/javascript">
      var mm = 99.9;
      var mmaf = 0.01; 
      window.onload = load_markers( mm, mmaf);
  </script>
  </div>
  <?php 
}

/**
 * starting with lines display the selected lines
 */
private function step1_lines()
{
        if (isset($_SESSION['selected_lines'])) {
            $selectedlines= $_SESSION['selected_lines'];
            $count = count($_SESSION['selected_lines']);
            ?>
            <table id="phenotypeSelTab" class="tableclass1">
            <tr><td>
            <select name="lines" multiple="multiple" style="height: 12em;">
            <?php
            foreach($selectedlines as $uid) {
              $sql = "SELECT line_record_name from line_records where line_record_uid = $uid";
              $res = mysql_query($sql) or die(mysql_error());
              $row = mysql_fetch_assoc($res)
              ?>
              <option disabled="disabled" value="
              <?php $uid ?>">
              <?php echo $row['line_record_name'] ?>
              </option>
              <?php
            }
            ?>
            </select>
            </td>
            </table>
            <?php
        } else {
          echo "Please select lines before using this feature.<br>";
          echo "<a href=";
          echo $config['base_url'];
          echo "pedigree/line_selection.php>Select Lines by Properties</a>";
        }
}

/**
 * starting with lines display trials
 */
private function step2_lines()
{
  if (isset($_SESSION['selected_lines'])) {
    $selectedlines= $_SESSION['selected_lines'];
    $count = count($_SESSION['selected_lines']);
    ?>
    <p>2.
    <strong>Experiments</strong>
    <table id="linessel" class="tableclass1">
    <tr><td>
    <select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_line_trial(this.options)">
    <?php
    $selectedlines= $_SESSION['selected_lines'];
    $selectedlines = implode(',', $selectedlines);
    
    $sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year, e.traits AS traits
    FROM experiments AS e, tht_base as tb, line_records as lr, experiment_types AS e_t
    WHERE e.experiment_uid = tb.experiment_uid
    AND lr.line_record_uid = tb.line_record_uid
    AND e.experiment_type_uid = e_t.experiment_type_uid
    AND e_t.experiment_type_name = 'genotype'
    AND lr.line_record_uid IN ($selectedlines)";
    if (!authenticate(array(USER_TYPE_PARTICIPANT,
      USER_TYPE_CURATOR,
      USER_TYPE_ADMINISTRATOR)))
     $sql .= " AND e.data_public_flag > 0";
     $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
     $last_year = NULL;
    
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_assoc($res))
    {
      if ($last_year == NULL) {
      ?>
        <optgroup label="<?php echo $row['year'] ?>">
      <?php
        $last_year = $row['year'];
      } else if ($row['year'] != $last_year) {
      ?>
      </optgroup>
      <optgroup label="<?php echo $row['year'] ?>">
      <?php
         $last_year = $row['year'];
      }
      ?>
    <option value="<?php echo $row['id'] ?>">
    <?php echo $row['name'] ?>
    </option>
    </optgroup>
    <?php
    }
    ?>
    </select></table>
    <?php 
  }
}

/**
 * starting with lines display phenotype items
 */
private function step3_lines() {
  $experiments = $_GET['exps'];
  $datasets = $_GET['dp'];
  ?>
  <p>
  <strong>Lines</strong>
  <table id="phenotypeSelTab">
  <tr><td>
          <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_lines(this.options)">
            <?php
            $sql_option = "";
            if (preg_match("/\d/",$experiments)) {
              $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
            }
            if (preg_match("/\d/",$datasets)) {
              $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
            }
            $sql = "SELECT DISTINCT line_records.line_record_name as name, line_records.line_record_uid as id
            FROM line_records, tht_base
            WHERE line_records.line_record_uid=tht_base.line_record_uid
            $sql_option";
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            while($row = mysql_fetch_array($res)) {
              ?>
              <option selected value="<?php echo $row['id'] ?>">
              <?php echo $row['name'] ?>
              </option>
              <?php
            }
            ?>
          </select>
  <?php
}	

/**
 * starting with data program dispaly data program and year
 */
private function type_GenoType_Display()
{
    ?>
	
	<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
		<div id ="step11" style="float: left; margin-bottom: 1.5em;">
		
			<table>
				<tr>
					<td>
						<select name="breeding_programs" size="10" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
		<?php 
		// Select data programs for the drop down menu
                $sql = "SELECT DISTINCT dp.CAPdata_programs_uid AS id, dp.data_program_name AS name, dp.data_program_code AS code
                                FROM CAPdata_programs as dp, experiments as e, experiment_types as e_t
                                WHERE dp.CAPdata_programs_uid = e.CAPdata_programs_uid
                                AND e.experiment_type_uid = e_t.experiment_type_uid
                                AND e_t.experiment_type_name = 'genotype'
                                AND program_type='data' ORDER BY name";

		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
			?>
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name']."(".$row['code'].")" ?></option>
			<?php
		}
		?>
						</select>
					</td></table>
			        </div></div>
			        <div id="step2" style="float: left; margin-bottom: 1.5em;">
			        <p>
                                <strong>Year</strong>
			        <table>
					<tr><td>
						<select name="year" size="10" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
		<?php

		// set up drop down menu with data showing year
		

		$sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
				WHERE e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
				GROUP BY e.experiment_year ASC";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
				<option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
			<?php
		}
		?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
                <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>

<?php 
	} /* end of type_GenoType_Display function*/
	
/**
 * display genotype experiments
 */
private function type1_experiments()
{
		$CAPdata_programs = $_GET['bp']; 
		$years = $_GET['yrs']; 
	
	
	/* Query for getting experiment id, trial code and year */
        /* AND e.experiment_year IN ($years) */
	$sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year, e.traits AS traits
				FROM experiments AS e, experiment_types AS e_t
                                WHERE e.CAPdata_programs_uid IN ($CAPdata_programs)
                                AND e.experiment_year IN ($years)
				AND e.experiment_type_uid = e_t.experiment_type_uid
				AND e_t.experiment_type_name = 'genotype'";
	if (!authenticate(array(USER_TYPE_PARTICIPANT,
				USER_TYPE_CURATOR,
				USER_TYPE_ADMINISTRATOR)))
				$sql .= " AND e.data_public_flag > 0";

	$sql .= " ORDER BY e.experiment_year DESC";

	
        //echo "$sql<br>";	
	$res = mysql_query($sql) or die(mysql_error());
	$num_mark = mysql_num_rows($res);
	//check if any experiments are visible for this user
	if ($num_mark>0) {
?>

    <p>
    <strong>Experiments</strong>
<table>
	
	<tr><td>
		<!--select name="experiments" multiple="multiple" size="10" style="height: 12em" onchange="javascript:load_tab_delimiter(this.options)"-->
                <select name="experiments" multiple="multiple" size="10" style="height: 12em" onchange="javascript:update_experiments(this.options)">
<?php
	
		while ($row = mysql_fetch_array($res)) {
			?>
			<!-- Display Map names-->		
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
			<?php
		}
		?>
	
		</select>
	</td></tr>
</table>



 

	
	<?php 
	}/* end of if condition */
	else
	{
	?>	<div class="section">
<p> There are no publicly available genotype datasets for this program and year in T3 at this time
 Registered users may see additional datasets after signing in.</p>
            </div>
  <?php 
	}/* end of else */
	} /* end of type1_experiments function */
	
/**
 * display title
 */	
private function type_Flap_Jack()
{	
	    ?>
		<input type="button" value="Save current selection" onclick="javascript: load_title('save');" />
        <?php 	
	}/* end of type_Flap_Jack function */
	
/**
 * display results of search
 */
private function type1_markers() {
  $experiments = $_GET['exps'];
  $datasets = $_GET['dp'];
  if (empty($_GET['lines'])) {
    if ((($subset == "yes") || ($subset == "comb")) && (count($_SESSION['selected_lines'])>0)) {
      $lines = $_SESSION['selected_lines'];
      $lines_str = implode(",", $lines);
      $count = count($_SESSION['selected_lines']);
    } else {
      $sql_option = "";
      $lines = array();
      if (preg_match("/\d/",$experiments)) {
              $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
      }
      if (preg_match("/\d/",$datasets)) {
              $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
      }
      $sql = "SELECT DISTINCT line_records.line_record_name as name, line_records.line_record_uid as id
            FROM line_records, tht_base
            WHERE line_records.line_record_uid=tht_base.line_record_uid
            $sql_option";
      $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
      while($row = mysql_fetch_array($res)) {
        $lines[] = $row['line_record_uid'];
      } 
    }
  } else {
    $lines_str = $_GET['lines'];
    $lines = explode(',', $lines_str);
  }
  $count1 = count($lines);
  $trials = explode(',', $experiments);
  $count2 = count($trials);
  echo "<table><tr><td>";
  if ($count1 == 1) {
    echo "found $count1 line\n";
  } else {
    echo "found $count1 lines\n";
  }
  ?>
  <td>
  <input type="hidden" name="subset" id="subset" value="yes" />
  <input type="button" value="Save" onclick="javascript: load_title('save');"/>
  </table>
  <?php
}

} /* end of class */

?>
