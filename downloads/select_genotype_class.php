<?php

/** functions specific to genotype experiment
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/select_genotype.php
 */

namespace T3;

class SelectGenotypeExp
{
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'type1':
                $this->type1();
                break;
     
            case 'type1experiments':
                $this->type1_experiments(); /* display experiments */
                break;
                        
            case 'type2experiments':
                $this->type2_experiments(); /* display experiments */
                break;

            case 'typeDownload':
                $this->type_Download(); /* display experiments */
                break;

            case 'typeDownload2':
                $this->type_Download2();
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
                
            case 'step1platform':
                echo $this->step1_platform();
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
   global $mysqli;
   $command = (isset($_GET['cmd']) && !empty($_GET['cmd'])) ? $_GET['cmd'] : null;
   $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
   // $subset = no (Replace), comb (Add, OR), yes (Intersect, AND) 
   echo "<h2>Select Lines by Genotype Experiment</h2>";
   echo "<p>After saving, the line selection can be used for analysis or download. Select multiple options by holding down the Ctrl key while selecting.";
   if ($command == "save") {
      if ((($subset == "yes") || ($subset == "comb")) && count($_SESSION['selected_lines'])>0) {
          $lines = $_SESSION['selected_lines'];
          $lines_str = implode(",", $lines);
          $count = count($_SESSION['selected_lines']);
      } elseif (!empty($_GET['lines'])) {
          $lines_str = $_GET['lines'];
          $lines = explode(',', $lines_str);
          $_SESSION['selected_lines'] = $lines;
      } elseif (!empty($_GET['exps'])) {
          $experiments = $_GET['exps'];
          $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($experiments)";
          $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
          if ($row = mysqli_fetch_array($res)) {
              $lines = json_decode($row[0], true);
              //*check for duplicates
              foreach ($lines as $line_record) {
                  if (isset($unique_list[$line_record])) { 
                      $skipped .= "$line_record ";
                  } else {
                      $lines_unique[] = $line_record;
                      $unique_list[$line_record] = 1;
                  }
              }
              $_SESSION['selected_lines'] = $lines_unique;
              //echo "skiped duplicates $skipped\n";
          } else {
              echo "error - no selection found";
          }
      } else {
          echo "error - no selection found";
      }
      if ($subset == "comb") {
          $experiments = $_GET['exps'];
          $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($experiments)";
          $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
          if ($row = mysqli_fetch_array($res)) {
              $lines_fnd = json_decode($row[0], true);
          } else {
              echo "error - no selection found";
          }
          foreach ($lines_fnd as $line_uid) {
            if (!in_array($line_uid,$lines)) {
                array_push($lines,$row['id']);
            }
          }
          $_SESSION['selected_lines'] = $lines;
      } elseif ($subset == "yes") {
          $experiments = $_GET['exps'];
          $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($experiments)";
          $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
          if ($row = mysqli_fetch_array($res)) {
              $tmp = json_decode($row[0], true);
          }
          $lines = array_intersect($lines, $tmp);
          $_SESSION['selected_lines'] = $lines;
      }
      if (!empty($_GET['exps'])) {
          $exps_str = $_GET['exps'];
          $experiments = explode(',', $exps_str);
          $_SESSION['geno_exps'] = $experiments;
          $sql = "select count(marker_uid) from allele_bymarker_exp_101 where experiment_uid in ($exps_str)";
          $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
          if ($row = mysqli_fetch_array($res)) {
              $_SESSION['geno_exps_cnt'] = $row[0];
          }
      }
        $username=$_SESSION['username'];
        if ($username) {
          store_session_variables('selected_lines', $username);
        }
      }
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
     include $config['root_dir'].'theme/normal_header.php';
     $this->type1_checksession();
     include $config['root_dir'].'theme/footer.php';
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
  unset($_SESSION['geno_exps']);
  unset($_SESSION['geno_exps_cnt']);

  ?>
  <p>1.
  <select name="select1" onchange="javascript: update_select1(this.options)">
  <option value="Platform">Platform</option>
  <option value="DataProgram">Data Program</option>
  </select></p>
  <div id="step11" style="float: left; margin-bottom: 1.5em;">
  <?php
  $this->step1_platform();
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
  <p>1.
  <select name="select1" onchange="javascript: update_select1(this.options)">
  <option value="Platform">Platform</option>
  <option value="DataProgram">Data Program</option>
  </select></p>
  <div id="step11" style="float: left; margin-bottom: 1.5em;">
  <script type="text/javascript" src="downloads/select_genotype01.js"></script>
  <?php
  $this->step1_platform(); 
  //$this->type_GenoType_Display();
  ?>
  </div></div>
  <div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
  <div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
  <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
  <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
  </div>
  <?php
}

/**
 * display platform
 */
private function step1_platform()
{
    global $mysqli;
    ?>
    <table><tr><td>
    <select name='platform[]' style="height: 12em;" multiple onchange="javascript: update_platform(this.options)">
    <?php
    $result=mysqli_query($mysqli, "select distinct(platform.platform_uid), platform_name from platform, genotype_experiment_info where platform.platform_uid = genotype_experiment_info.platform_uid") or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_assoc($result)) {
        $uid = $row['platform_uid'];
        $val = $row['platform_name'];
        print "<option value='$uid'>$val</option>\n";
    }
    print "</select></table>";
}
/**
 * display data program
 */
private function step1_breedprog()
{
  global $mysqli;
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
 
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  while ($row = mysqli_fetch_assoc($res))
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
    global $mysqli;
    $CAPdata_programs = $_GET['bp'];
     ?>
    <div id="step21">
    <p>
    <select disabled>
    <option>Year</option>
    </select>
    </p>
    <table id="phenotypeSelTab" class="tableclass1">
    <tr><td>
    <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
    <?php
    $sql = "SELECT e.experiment_year AS year 
    FROM experiments AS e, experiment_types AS et
    WHERE e.experiment_type_uid = et.experiment_type_uid
    AND et.experiment_type_name = 'genotype'
    AND e.CAPdata_programs_uid IN ($CAPdata_programs)
    GROUP BY e.experiment_year DESC";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_assoc($res))
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
    global $mysqli;
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
              $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
              $row = mysqli_fetch_assoc($res)
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
          echo "pedigree/line_properties.php>Select Lines by Properties</a>";
        }
}

/**
 * starting with lines display trials
 */
private function step2_lines()
{
  global $mysqli;
  if (isset($_SESSION['selected_lines'])) {
    $selectedlines= $_SESSION['selected_lines'];
    $count = count($_SESSION['selected_lines']);
    ?>
    <p>2.
    <select disabled>
    <option>Experiments</option>
    </select>
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
    
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_assoc($res))
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
 * display lines for given experiment
 */
private function step3_lines()
{
  global $mysqli;
  $experiments = $_GET['exps'];
  $datasets = $_GET['dp'];
  ?>
  <p>
  <select disabled>
  <option>Lines</option>
  </select>
  <table id="phenotypeSelTab">
  <tr>
  <?php
  $sql_option = "";
  $count1 = 0;
  if (preg_match("/\d/",$experiments)) {
      $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
  }
  $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($experiments)";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_array($res)) {
      $line_index = json_decode($row[0], true);
      $count1 = count($line_index);
  } else {
      $line_index = array();
  }
  
  if (isset($_SESSION['selected_lines'])) {
      $count2 = count($_SESSION['selected_lines']);
      echo "<td>Lines found: $count1<td><td>Current selection: $count2";
  }
  ?>
  <tr><td>
          <select name="lines" multiple="multiple" style="height: 12em;"  disabled>
          <!--select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_lines(this.options)"-->
            <?php
            $count = 0;
            $sql_option = "";
            if (preg_match("/\d/",$experiments)) {
              $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
            }
            if (preg_match("/\d/",$datasets)) {
              $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
            }
            foreach ($line_index as $uid) {
              $sql = "select line_record_name from line_records where line_record_uid = $uid";
              $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
              if ($row = mysqli_fetch_array($res)) {
                $name = $row[0];
              } else {
                $name = "unknown";
              }
              ?>
              <option selected value="<?php echo $uid ?>">
              <?php echo $name ?>
              </option>
              <?php
            }
            ?>
          </select>
  <?php
      if (isset($_SESSION['selected_lines']) AND count($_SESSION['selected_lines']) != 0) {
          ?>
          <td style="width: 130px; padding: 8px">Combine with <font color=blue>currently<br>selected lines</font>:<br>
          <input type="radio" name="subset" value="no" checked onclick="javascript: update_combine(this.value)">Replace<br>
          <input type="radio" name="subset" value="comb" onclick="javascript: update_combine(this.value)">Add (OR)<br>
          <input type="radio" name="subset" value="yes" onclick="javascript: update_combine(this.value)">Intersect (AND)<br>
  <?php
          $count = count($_SESSION['selected_lines']);
          print "<td><select name=\"deselLines[]\" multiple=\"multiple\" style=\"height: 12em;\">";
          foreach ($_SESSION['selected_lines'] as $lineuid) {
            $result=mysqli_query($mysqli, "select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
            while ($row=mysqli_fetch_assoc($result)) {
              $selval=$row['line_record_name'];
              print "<option value=\"$lineuid\" selected>$selval</option>\n";
            }
          }
          print "</select></table>";
       }

}	

/**
 * starting with data program dispaly data program and year
 */
private function type_GenoType_Display()
{
    global $mysqli;
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
						<select name="breeding_programs" style="height: 12em;" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
		<?php 
		// Select data programs for the drop down menu
                $sql = "SELECT DISTINCT dp.CAPdata_programs_uid AS id, dp.data_program_name AS name, dp.data_program_code AS code
                                FROM CAPdata_programs as dp, experiments as e, experiment_types as e_t
                                WHERE dp.CAPdata_programs_uid = e.CAPdata_programs_uid
                                AND e.experiment_type_uid = e_t.experiment_type_uid
                                AND e_t.experiment_type_name = 'genotype'
                                AND program_type='data' ORDER BY name";

		$res = mysqli_query($mysqli, $sql) or die(mysql_error($mysqli));
		while ($row = mysqli_fetch_assoc($res))
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
						<select name="year" style="height: 12em;" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
		<?php

		// set up drop down menu with data showing year
		

		$sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
				WHERE e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
				GROUP BY e.experiment_year ASC";
		$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
		while ($row = mysqli_fetch_assoc($res)) {
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
    <select disabled>
    <option>Experiments</option>
    </select>
<table>
	
	<tr><td>
		<!--select name="experiments" multiple="multiple" size="10" style="height: 12em" onchange="javascript:load_tab_delimiter(this.options)"-->
                <select name="experiments" multiple="multiple" style="height: 12em;" style="height: 12em" onchange="javascript:update_experiments(this.options)">
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
 * display genotype experiments
 */
private function type2_experiments()
{
    global $mysqli;
    $platform = $_GET['platform'];
    ?>
    <p>
    <select disabled>
    <option>Experiments</option>
    </select>
    <table><tr><td><select name='expt[]' style="height: 12em;" multiple onchange="javascript: update_experiments(this.options)">
    <?php
    $prev_name = "";
    $result=mysqli_query($mysqli, "select experiments.experiment_uid, trial_code, data_program_name from experiments, genotype_experiment_info, CAPdata_programs
        where experiments.CAPdata_programs_uid = CAPdata_programs.CAPdata_programs_uid
        and experiments.experiment_uid = genotype_experiment_info.experiment_uid
        and genotype_experiment_info.platform_uid IN ($platform) order by data_program_name") or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_assoc($result)) {
        $uid=$row['experiment_uid'];
        $val=$row['trial_code'];
        $name=$row['data_program_name'];
        if ($prev_name == "") {
          print "<optgroup label=\"$name\">\n";
          $prev_name = $name;
        } elseif ($name != $prev_name) {
          print "</optgroup>\n<optgroup label=\"$name\">\n";
          $prev_name = $name;
        }
        print "<option value=$uid>$val</option>\n";
    }
    ?>
    </optgroup></select></table>
    <?php
}	

/**
 * display results of search
 */
private function type1_markers()
{
  global $mysqli;
  $experiments = $_GET['exps'];
  $datasets = $_GET['dp'];
  $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
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
      $skipped = "";
      $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($experiments)";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      if ($row = mysqli_fetch_array($res)) {
          $lines = json_decode($row[0], true);
          //*check for duplicates
          foreach ($lines as $key=>$line_record) {
              $sql = "select line_record_name from line_records where line_record_uid = $line_record";
              $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
              if ($row = mysqli_fetch_array($res)) {
                  $name = $row[0];
              } else {
                  $name = "unknown";
              }
              if (isset($unique_list[$line_record])) {
                  if ($skipped == "") {
                      $skipped = "$name";
                  } else {
                      $skipped .= ", $name";
                  }
              } else {
                  $lines_unique[] = $line_record;
                  $unique_list[$line_record] =  $name;
              }
          }
      }
    }
    if ($skipped != "") {
        echo "skipped duplicate line names<br>$skipped\n";
    }
    if ($subset == "comb") {
        $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($experiments)";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_array($res)) {
            $lines_fnd = json_decode($row[0], true);
        } else {
            echo "error - no selection found";
        }
        foreach ($lines_fnd as $line_uid) {
          if (!in_array($line_uid, $lines)) {
              array_push($lines, $line_uid);
          }
        }
    } elseif ($subset == "yes") {
        $experiments = $_GET['exps'];
        $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($experiments)";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_array($res)) {
            $tmp = json_decode($row[0], true);
        }
        $lines = array_intersect($lines, $tmp);
        $_SESSION['selected_lines'] = $lines;
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
  if ($count1 > 0) {
  ?>
  <td>
  <input type="hidden" name="subset" id="subset" value="yes" />
  <input type="button" value="Save selection" onclick="javascript: load_title('save');"/>
  </table>
  <?php
  }
}

} /* end of class */
