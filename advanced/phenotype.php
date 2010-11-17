<?php
require 'config.php';

/**
 * Advanced Phenotype Search
 *
 * @author Gavin Monroe
 *
 * @todo finalize data output formats
 */

/*
 * Includes
 */
//ini_set( "include_path", "..\classes;..\includes;..\pear;" . ini_get("include_path") );

include($config['root_dir'].'includes/bootstrap.inc');
connect();
// filter class
require($config['root_dir'].'classes/filter.php');
// autoload
new AS_Phenotype($_GET['function']);

/**
 * Represents the Advanced Phenotype Search
 *
 * @author Gavin Monroe
 */
class AS_Phenotype
{
    /**
     * A List of active filters
     *
     * @var array
     */
	var $filters;

	var $arg0; // phenotype.php?arg0=
    var $arg1; // phenotype.php?arg1=
	var $arg2; // phenotype.php?arg2=
	var $arg3; // phenotype.php?arg3=

	//var $includedir='D:/inetpub/sandbox/yhames04/advanced/';
	//var $includedirrel='/sandbox/yhames04/advanced/';

    /**
     * Redirects the user to the login page
     */
    function forceLogin()
	{
		global $config;

		// tells the login script where we want the user to end up after login
		$_SESSION['login_referer_override'] = $config['base_url']/*BASEURL*/.'advanced/phenotype.php';
    	header('Location: '.$config['base_url'].'login.php');
    }

    /**
     * Constructs a new instance of the Advanced Phenotype Search
     *
     * @param string $function the action to perform
     * @return AS_Phenotype
     */
    function AS_Phenotype($function)
	{
		$filters = array();
		$arg3 = $arg2 = $arg1 = $arg0 = null;

		/*
		 * Populate the arguments from the URL
		 */
		if (isset($_GET['arg0']) && !empty($_GET['arg0']))
            $this->arg0 = $_GET['arg0'];
        if (isset($_GET['arg1']) && !empty($_GET['arg1']))
            $this->arg1 = $_GET['arg1'];
		if (isset($_GET['arg2']) && !empty($_GET['arg2']))
            $this->arg2 = $_GET['arg2'];
		if (isset($_GET['arg3']) && !empty($_GET['arg3']))
            $this->arg3 = $_GET['arg3'];

        // force the user to login
        if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR))){
        	$this->forceLogin();
        	die();
		}

		// decide which function to perform
        switch($function)
        {
            case 'main': $this->main(); break;
            case 'sl': $this->selectLine(); break;
            case 'dl': $this->deselectLine(); break;
            case 'sal': $this->selectLinesAll(); break;
            case 'snl': $this->selectLinesNone(); break;
            case 'add_filter': $this->add_filter(); break;
            case 'html_filters': $this->do_html_filters(); break;
            case 'count_results': $this->count_results(true); break;
            case 'delete_filter': $this->delete_filter(); break;
            case 'get_results': $this->get_results(); break;
            case 'new_filter_set': $this->new_filter_set(); break;
            case 'delete_filter_set': $this->delete_filter_set(); break;
            case 'load_filter_set': $this->load_filter_set(); break;

			// data export
			case 'get_csv': $this->getCsv(); break;
			case 'get_qtl': $this->getQtl(); break;

            default: $this->main(); break;
		}
	}





	/**
	 * The default action. Displays the main A.P.S interface. Makes calls to
	 * other actions.
	 *
	 */
	function main()
	{
		global $config;

		include($config['root_dir'].'theme/normal_header.php');
		?>
		<h1>Advanced Phenotype Search</h1>
		<h3>Active Filters</h3>
		<div id="active_filters">
		<div class="section">
			<br />
			<p><?php $this->do_html_filters(); ?></p>
		</div>
		</div>

		<a name="fav"></a>
		<h3>Saved Filter Sets</h3>
		<div id="favorite_filters">
		<div class="section">
			<p><?php $this->get_filter_sets(); ?></p>
		</div>
		</div>

		<a name="create"></a>
		<h3>Create a Filter (<a title="help" onclick="window.open('help/aps_create.html#create', 'help', 'menubar=no,width=350,height=500,resizable=no,scrollbars=yes,toolbar=no');">?</a>)</h3>
		<div class="section">
			<br />
			<br />
			<p><?php $this->do_hmtl_search_form(); ?></p>
		</div>
		<?php
		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}





  	/**
  	 * Adds a temporary filter
  	 *
  	 */
	function add_filter()
	{
		// Determine the type of filter
		$type = $_POST['type'];
		if ($_POST['categories'] == 'misc') {
			switch(strtolower($_POST['category_misc'])){
				case 'year':
					$type = 'experiments';
					break;
				case 'row_type':
					$type = 'line_records';
					break;
				case 'end_use':
					$type = 'line_records';
					break;
			}
		}

		// Phenotype filter
		if ($type == 'phenotype')
		{
			$category_id = $_POST['categories'];
			$phenotype_id = $_POST['category_' . $category_id];
			$value_is = $_POST['value_is'];

			// Equal to
			if ($value_is == '0')
			{
				$equalto = $_POST['equalto'];
				$filter = new PhenotypeFilter(
					null,	// identifier
					'phenotype_data',
					array('phenotype_uid', 'value'),
					array('= \''. $phenotype_id .'\'', '= \''. $equalto .'\'')
				);
			}
			// Between
			else if ($value_is == '1')
			{
				$lower = $_POST['lower'];
				$upper = $_POST['upper'];
				$filter = new PhenotypeFilter(
					null,	// identifier
					'phenotype_data',
					array('phenotype_uid', 'value+0'),
					array('= \''. $phenotype_id .'\'', 'BETWEEN '. $lower .' AND '. $upper)
				);
			}
		}

        // Experiment filter
		else if ($type == 'experiments')
		{
			$value_is = $_POST['value_is'];

			// Equal to
			if ($value_is == '0') {
				$equalto = $_POST['equalto'];
				if (strtolower($_POST['category_misc']) == 'year') {
					$filter = new ExperimentFilter(
						null,
						'experiments',
						array('experiment_year'),
						array("= '$equalto'")
					);
				}
			}

			// Between
			else if ($value_is == '1') {
				$lower = $_POST['lower'];
				$upper = $_POST['upper'];
				if (strtolower($_POST['category_misc']) == 'year') {
					$filter = new ExperimentFilter(
						null,
						'experiments',
						array('experiment_year'),
						array("BETWEEN $lower AND $upper")
					);
				}
			}
		}
		
		// Line Records filter
		else if ($type == 'line_records')
		{
			$value_is = $_POST['value_is'];
			//Equal to
			if($value_is == '0')
			{
				$equalto = $_POST['equalto'];
				switch(strtolower($_POST['category_misc'])) {
					case 'row_type':
						$filter = new LineRecordsFilter(null, 'line_records', array('row_type'), array("= '$equalto'"));
						break;
					case 'end_use':
						$filter = new LineRecordsFilter(null, 'line_records', array('primary_end_use'), array("= '$equalto'"));
						break;
						
				}
			}
			// Between
			else if ($value_is == '1')
			{
				$lower = $_POST['lower'];
				$upper = $_POST['upper'];
				switch(strtolower($_POST['category_misc'])) {
					case 'row_type':
						$filter = new LineRecordsFilter(null, 'line_records', array('row_type'), array("BETWEEN $lower AND $upper"));
						break;
					case 'end_use':
						$filter = new LineRecordsFilter(null, 'line_records', array('primary_end_use'), array("BETWEEN $lower AND $upper"));
						break;
				}
			}	
		}


        // Add the filter to the database
        $data = serialize($filter);
        $users_uid = $this->get_user_id($_SESSION['username']);
        $sql = "INSERT INTO temp_filters (users_uid, data) VALUES ('$users_uid', '" . base64_encode($data) . "')";
        mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n" . $sql . "</pre>");

		// Redirect (invisible)
        header("Location: {$_SERVER['PHP_SELF']}");
    }





    /**
     * Deletes a temporary filter from the database
     *
     */
    function delete_filter() {
   		if ($id = $this->forceArgument(0)) { // require ?arg0=
   			$sql = "delete from temp_filters where temp_filters_uid = $id";
   			mysql_query($sql) or die(mysql_error());
		}
		header('Location: ' . $_SERVER['PHP_SELF']);
	}

	/**
	 * Outputs the "Create a Filter" form
	 *
	 */
    function do_hmtl_search_form()
    {
    	global $config;

		ob_start();
		$sql = <<< SQL
			select
				phenotype_category.phenotype_category_uid,
				phenotype_category.phenotype_category_name,
				phenotypes.phenotype_uid,
				phenotypes.phenotypes_name,
				phenotypes.description,
				MIN(phenotype_data.value+0) AS min,
				MAX(phenotype_data.value+0) AS max,
				units.unit_name
			from
				phenotype_category
			join
				(phenotypes, phenotype_data, units)
			on
				(phenotype_category.phenotype_category_uid = phenotypes.phenotype_category_uid
				AND phenotypes.phenotype_uid = phenotype_data.phenotype_uid
				AND phenotypes.unit_uid = units.unit_uid)
			where
				phenotype_data.value != 'N/A'
			group by
				phenotype_data.phenotype_uid
			order by
				phenotype_category.phenotype_category_name
SQL;
		$query = mysql_query($sql) or die(mysql_error());

		$left_html = '<select style="width: 100%" size="10" name="categories" onchange="getPhenotypeSelect(this.value)"><option selected="selected" value="-1">(Select a category)</option>';
		$middle_html = "";
		$hiddens = "";
		$done_first = FALSE;	// whether/not we've processed at least 1 category
		$last_category_name = "";	// the last category we processed

		while($row = mysql_fetch_assoc($query))
		{
			$category_id =		$row['phenotype_category_uid'];
			$category_name =	$row['phenotype_category_name'];
			$phenotype_id =		$row['phenotype_uid'];
			$phenotype_name =	$row['phenotypes_name'];
			$description =		$row['description'];
			$unit_name =		$row['unit_name'];
			$min =				$row['min'];
			$max =				$row['max'];

			if ($last_category_name != $category_name)
			{
				$left_html .= "<option value=\"$category_id\">$category_name</option>";
				if ($done_first == TRUE)
				{
					$middle_html .= "
						</select>
					</div>
					";
				}
				else
				{
					$done_first = TRUE;
				}
				$middle_html .= "
					<div style=\"display: none\" id=\"category_$category_id\">
						<select style=\"width: 100%\" size=\"10\" name=\"category_$category_id\" onchange=\"getValueIs(this.value)\">
							<option value=\"-1\">(Select a phenotype)</option>
					";
				$last_category_name = $category_name;
			}
			$middle_html .= "<option value=\"$phenotype_id\">$phenotype_name ($unit_name)</option>";
			$hiddens .= "<input type=\"hidden\" id=\"min_$phenotype_id\" name=\"min_$phenotype_id\" value=\"$min\" />";
			$hiddens .= "<input type=\"hidden\" id=\"max_$phenotype_id\" name=\"max_$phenotype_id\" value=\"$max\" />";
			$hiddens .= "<input type=\"hidden\" id=\"desc_$phenotype_id\" name=\"desc_$phenotype_id\" value=\"$description\" />";
		}
		$middle_html .= "
			</select>
		</div>
		";

		// do the miscellaneous category manually
		$sql = "
			SELECT
				MIN(experiment_year+0) AS experiment_year_min,
				MAX(experiment_year+0) AS experiment_year_max,
				MIN(row_type+0) AS row_type_min,
				MAX(row_type+0) AS row_type_max,
				MIN(primary_end_use) AS end_use_min,
				MAX(primary_end_use) AS end_use_max
			FROM experiments, line_records
			WHERE
				experiment_year IS NOT NULL
				AND row_type IS NOT NULL
				AND primary_end_use IS NOT NULL
		";
		$query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n" . $sql . "</pre>");
		$result = mysql_fetch_assoc($query);
		$year_min = $result['experiment_year_min'];
		$year_max = $result['experiment_year_max'];
		$row_type_min = $result['row_type_min'];
		$row_type_max = $result['row_type_max'];
		$end_use_min = $result['end_use_min'];
		$end_use_max = $result['end_use_max'];

		$left_html .= "<option value=\"misc\">Miscellaneous</option></select>";
		$middle_html .= "
			<div style=\"display: none\" id=\"category_misc\">
				<select style=\"width: 100%\" size=\"10\" name=\"category_misc\" onchange=\"getValueIs(this.value)\">
					<option value=\"-1\">(Select a phenotype)</option>
					<option value=\"year\">Year</option>
					<option value=\"row_type\">Row Type</option>
					<option value=\"end_use\">End Use</option>
				</select>
			</div>
			";
		$hiddens .= "<input type=\"hidden\" id=\"min_year\" name=\"min_year\" value=\"$year_min\" />";
		$hiddens .= "<input type=\"hidden\" id=\"max_year\" name=\"max_year\" value=\"$year_max\" />";
		$hiddens .= "<input type=\"hidden\" id=\"desc_year\" name=\"desc_year\" value=\"The experiment year\" />";
		$hiddens .= "<input type=\"hidden\" id=\"min_row_type\" name=\"min_row_type\" value=\"$row_type_min\" />";
		$hiddens .= "<input type=\"hidden\" id=\"max_row_type\" name=\"max_row_type\" value=\"$row_type_max\" />";
		$hiddens .= "<input type=\"hidden\" id=\"desc_row_type\" name=\"desc_row_type\" value=\"The row type\" />";
		$hiddens .= "<input type=\"hidden\" id=\"min_end_use\" name=\"min_end_use\" value=\"$row_end_use\" />";
		$hiddens .= "<input type=\"hidden\" id=\"max_end_use\" name=\"max_end_use\" value=\"$row_end_use\" />";
		$hiddens .= "<input type=\"hidden\" id=\"desc_end_use\" name=\"desc_end_use\" value=\"The end_use\" />";
		// output html
?>


<script type="text/javascript" src="<?php echo $config['base_url']; ?>advanced/phenotype.js.php"></script>
<div id="search_form">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?function=add_filter" method="POST">
        <input type="hidden" name="type" value="phenotype">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
				<th width="33%">Category</th>
				<th width="34%">Phenotype</th>
				<th width="33%">Value</th>
			</tr>
            <tr>
                <td>
                    <?php echo $left_html; ?>
                </td>
                <td>
                    <?php echo $middle_html; ?>
                    <?php echo $hiddens; ?>
                </td>
                <td>
                    <div id="value_is" style="display:none;width:100%">&nbsp;</div>
                </td>
            </tr>
        </table>
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td style="text-align: left">
                    <input type="submit" value="Create!" />
                </td>
                <td width="100%">
                    <div style="display: inline; font-size: 8pt" id="descript"></span>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php
        $contents = ob_get_contents();
        ob_end_clean();
        echo $contents;
    }





    /**
     * Ouputs the active filters
     *
     */
    function do_html_filters()
    {

		$numPhenotypeFilters = 0;

    	$sub_sql = $this->get_user_id_sql($_SESSION['username']);
        $sql = "
        	SELECT
        		*
        	FROM
        		temp_filters
        	WHERE
        		users_uid = ($sub_sql)
        	";
        $query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n" . $sql . "<pre>");

        if (mysql_num_rows($query) <= 0)
        {
            echo "You do not have any active filters. Use the form below to <a href=\"{$_SERVER['PHP_SELF']}#create\">Create a Filter</a>.";
        }
        else
        {
            $html = "";
            $last_filter = null; //new PhenotypeFilter(null, 'phenotype_data');
            while ($row = mysql_fetch_assoc($query))
            {
                $data = base64_decode($row['data']);
                $current_filter = unserialize($data);

                // if false, $last_filter will remain $last_filter
                // if true, $current_filter will become $last_filter
                $do_swap = false;

                if (!is_null($last_filter))
                {
                	// if we are allowed to make $last_filter a sub filter of $current_filter
                    if ($current_filter->sub_filter($last_filter))
                    {
                        $do_swap = true;
                    }
                    else if($last_filter->sub_filter($current_filter))
                    {
                        // do nothing
                    }
                    // if we can't make either filter a sub filter of the other
                    else
                    {
                            die('Invalid filter set');
                    }
                }else{
                    $do_swap = true;
                }

                /* Handle A Phenotype Filter */
                if ($current_filter->table_name() == 'phenotype_data')
                {
                    $pheno_id = array();
                    $bounds = $current_filter->bounds();
                    $bound = stripslashes($bounds[0]);
                    preg_match('/^= \'(.+)\'$/', $bound, $pheno_id);
                    $info = $this->get_phenotype_info($pheno_id[1]);
                    $bound = $this->translate_sql_value($bounds[1]);
                    $info['phenotypes_name'] = ucwords($info['phenotypes_name']);

                    $html .= <<< HTML
<tr id="temp_filter_{$row['temp_filters_uid']}">
	<td>
		<input type="button" value="Delete" style="display:inline" id="delete_btn_{$row['temp_filters_uid']}" onclick="
			ans = confirm('Are you sure you want to delte this filter?');
			if (ans) {
			deleteFilter('{$row['temp_filters_uid']}')
			}"
		/>
	</td>
	<td>
		{$info['phenotypes_name']}
	</td>
	<td>
		{$info['phenotype_category_name']}
	</td>
	<td>
		$bound
	</td>
</tr>
HTML;

                	$numPhenotypeFilters += 1;

                }

                /* Handle An Experiment, Line Records Filter */
                else if ($current_filter->table_name() == 'experiments' ||
					$current_filter->table_name() == 'line_records')
                {
                    $field_names = $current_filter->field_names();
                    $field = $field_names[0];
                    $bounds = $current_filter->bounds();
                    $bound = $this->translate_sql_value($bounds[0]);
					
					$convert = array(
						"experiment_year" => "Year",
						"row_type" => "Row Type",
						"primary_end_use" => "End Use"
					);
					$comp = strtolower($field);
                    if ($comp == 'experiment_year' ||
						$comp == 'row_type' || $comp == 'primary_end_use'){
							

                    	$html .= <<< HTML
<tr id="temp_filter_{$row['temp_filters_uid']}">
	<td>
		<input type="button" value="Delete" style="display:inline" id="delete_btn_{$row['temp_filters_uid']}" onclick="
			ans = confirm('Are you sure you want to delete this filter?');
			if (ans) {
			deleteFilter('{$row['temp_filters_uid']}')
			}"
		/>
	</td>
	<td>
		{$convert[$comp]}
	</td>
	<td>
		Miscellaneous
	</td>
	<td>
		$bound
	</td>
</tr>
HTML;
                    }
                }
				
				
                if ($do_swap)
                {
                	$last_filter = $current_filter;
                }
            }
            $sql = stripslashes($last_filter->get_sql()); //die();
            $query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n" . $sql);
            $num_results = mysql_num_rows($query);
            $button_html = "";
            if ($num_results <= 0 || $numPhenotypeFilters <= 0)
            {
                    $button_html = "<input disabled=\"disabled\" type=\"button\" value=\"View 0 Result(s)\" />\n";
            }
            else
            {
                    $button_html = <<< HTML
<input id="view_results_btn" type="button" value="View $num_results Result(s)" onclick="
	$('view_results_btn').disable();
	new Ajax.Updater(
		'results',
		'{$_SERVER['PHP_SELF']}?function=get_results',
		{
			onComplete: function () { new Effect.Appear('results'); $('hide_results_btn').enable(); }
		}
	);
" />
HTML;
            }

?>
<!--<div id="active_filters">-->
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<th>&nbsp;</th>
			<th>Phenotype</th>
			<th>Category</th>
			<th>Limit</th>
		</tr>
		<?php echo $html; ?>
	</table>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td style="text-align: left">
				<?php echo $button_html; ?>
				<input id="hide_results_btn" disabled="disabled" type="button" value="Hide Result(s)" onclick="
					new Effect.Fade('results');
					this.disable();
					$('view_results_btn').enable();
				" />

                <input <?php if ($numPhenotypeFilters <= 0) echo 'disabled="disabled"'; ?> id="new_filter_set_btn" type="button" value="Save As..." onclick="new Effect.Appear('new_filter_set');" />
                <span id="new_filter_set" style="display: none">
                    <script type="text/javascript" src="js/base64.js"></script>
                    <form
						style="display: inline;"
						action="<?php echo $_SERVER['PHP_SELF']; ?>?function=new_filter_set&arg0="
						method="post"
						onsubmit="document.location='<?php echo $_SERVER['PHP_SELF']; ?>?function=new_filter_set&arg0='+Base64.encode($F('new_filter_set_name')); return false;"
					>
                        <input type="text" id="new_filter_set_name" name="new_filter_set_name" value="Name..."  onfocus="if (typeof(changed) == 'undefined'){ this.value = ''; changed  = true; }"/>
						<input type="submit" value="Save" />
					</form>
				</span>
			</td>
		</tr>
	</table>
	<div id="results" style="display: none">
	</div>
<!--</div>-->
<?php if ($numPhenotypeFilters <= 0): ?>
<!--<script type="text/javascript">
    var inputs = document.getElementsByTagName("input");
    //loop through all the inputs on the page
    for (var i=0; i<inputs.length; i++)
    //if there are inputs with id starting with 'delete_btn_' then hide them
    if ((inputs[i].id.indexOf("delete_btn_") == 0)) inputs[i].disabled = "true";//.style.display = "none";

</script>-->
<div id="error" style="color:red">
	<p>Error! You must create at least one filter not of the "Miscellanous" category.</p>
</div>
<?php endif;?>

<?php
        }
    }





	/**
	 *  Get a user id from a user name
	 *
	 */
	function get_user_id($username)
	{
		$query = mysql_query($this->get_user_id_sql($username)) or die("<pre>" . mysql_error() . "</pre>");
		if (mysql_num_rows($query) <= 0)
			return FALSE;
		$row = mysql_fetch_row($query);
		return $row[0];
	}





	/**
	 *  Get the sql statement used to get a user id from a user name
	 *
	 */
	function get_user_id_sql($username) {
		return "select users_uid from users where users_name = '$username' limit 1";
	}





	function get_phenotype_info($id)
	{
		$sql = "
			SELECT
				phenotypes_name,
				phenotype_category_name
			FROM
				phenotypes
			JOIN
				phenotype_category
			ON
				phenotypes.phenotype_category_uid = phenotype_category.phenotype_category_uid
			WHERE
				phenotypes.phenotype_uid = '$id'
			";
		$query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n" . $sql . "</pre>");
		if (mysql_num_rows($query) <= 0)
		{
			return FALSE;
		}
		return mysql_fetch_assoc($query);
	}

	/**
	 * Returns the results of a search in CSV form
	 */
	function getCsv()
	{
		$results = $this->generateResults();
		// column headings
		$output = 'Line,Experiment,Year';
		$identifiers = array();
		foreach ($results['filters'] as $filter) {
			$identifier = $filter->identifier();
			array_push($identifiers, $identifier);
			$output .= ',' . $results['lines'][0]['name_' . $identifier] . '(' . $results['lines'][0]['unit_name_' . $identifier] . ')';
		}
		$output .= "\n";
		// data
		foreach ($results['lines'] as $line) {
			$output .= $line['line_record_name'] . ',' . $line['experiment_name'] . ',' . $line['experiment_year'];
			foreach ($identifiers as $identifier) {
				$output .= ',' . $line['value_' . $identifier];
			}
			$output .= "\n";
		}
		$time = date('m-d-y_H-i-s'); // time stamp
		header('Content-type: application/octet-stream');	// forces browser to download the file
		header('Content-disposition: attachment; filename="THT-pdata-'.$time.'.csv"'); // changes the filename on the fly
		echo $output;
	}

	function getQtl()
	{
		$results = $this->generateResults();
		// column headings
		$output = 'Experiment Linename';
		$identifiers = array();
		foreach ($results['filters'] as $filter) {
			$identifier = $filter->identifier();
			array_push($identifiers, $identifier);
			$output .= ' '.$results['lines'][0]['name_'.$identifier].' N';
		}
		$output .= "\r\n";
		// data
		foreach ($results['lines'] as $line) {

			$line['experiment_name'] = str_replace(' ', '_', $line['experiment_name']);
			$line['line_record_name'] = str_replace(' ', '_', $line['line_record_name']);

			$output .= $line['experiment_name'].' '.$line['line_record_name'];
			foreach ($identifiers as $identifier) {
				$replications = (intval($line['number_replications']) > 0) ? $line['number_replications'] : '1';
				$output .= ' '.$line['value_'.$identifier].' '.$replications;
			}
			$output .= "\r\n";
		}
		$time = date('m-d-y_H-i-s'); // time stamp
		header('Content-type: application/octet-stream');	// forces browser to download the file
		header('Content-disposition: attachment; filename="qtlminer_pheno_'.$time.'.txt"'); // changes the filename on the fly
		echo $output;
	}

	/**
	 * Returns the results of a search.
	 *
	 * @param int the starting row
	 * @param int the number of rows to include
	 * @param string the field on which ordering is done
	 * @param string the direction in which ordering is done
	 */
	function generateResults($offset = 0, $limit = NULL, $orderby = 'line_record_name', $asc = TRUE)
	{
		$result = array('filters' => array(), 'lines' => array());
		$subSql = $this->get_user_id_sql($_SESSION['username']);
		$sql = 'select * from temp_filters where users_uid = (' . $subSql. ')';
		$res = mysql_query($sql) or die(mysql_error());
		if(mysql_num_rows($res) <= 0) {
			return NULL;
		}
		$result['filters'] = array();
		$lastFilter = NULL;
		while ($filter = mysql_fetch_assoc($res)) {
			$curFilter = unserialize(base64_decode($filter['data']));

			$doSwap = FALSE;
			if (is_null($lastFilter)) {
				$doSwap = TRUE;
			} else {
				if ($curFilter->sub_filter($lastFilter)) {
					$doSwap = TRUE;
				} else if (!$lastFilter->sub_filter($curFilter)){
					trigger_error('Invalid Filter Set', E_USER_ERROR);
				}
			}
			if ($doSwap === TRUE) $lastFilter = $curFilter;
			array_push($result['filters'], $curFilter);
		}
		$sql = stripslashes($lastFilter->get_sql()); // recursive-ish call
		$res = mysql_query($sql) or die(mysql_error());

		$result['num_rows'] = mysql_num_rows($res);
		$result['num_pages'] = (is_null($limit)) ? 1 : ceil($result['num_rows']/$limit);
		$result['current_page'] = (is_null($limit)) ? 1 : ceil($offset/$limit)+1;

		if (is_null($limit)) $sql .= " limit $offset, 18446744073709551615";
		else $sql .= " limit $offset, $limit";
		if ($asc) $sql = "select * from ($sql) as t ORDER BY $orderby ASC";
		else $sql = "select * from ($sql) as t ORDER BY $orderby DESC";
		$res = mysql_query($sql) or die(mysql_error());

		$result['lines'] = array();
		while ($line = mysql_fetch_assoc($res)) {
			array_push($result['lines'], $line);
		}
		return $result;
	}


	/**
	 * Outputs the results
	 *
	 */
    function get_results()
    {
        $presentLines = array();

    	$offset = $this->arg0;
        $limit = $this->arg1;
		$order_by_column = (NULL == $this->arg2 || empty($this->arg2)) ? 'line_record_name' : $this->arg2;
		$order_by_direction = (NULL == $this->arg3 || empty($this->arg3)) ? 'ASC' : $this->arg3;

        if ($offset == null)
            $offset = 0;
        if ($limit == null)
            $limit = 10;

        $next_offset = $offset + $limit;
        $previous_offset = $offset - $limit;
        $next_disabled = "";
        $prev_disabled = "";

        $sub_sql = $this->get_user_id_sql($_SESSION['username']);
        $sql = "SELECT * FROM temp_filters WHERE users_uid = ($sub_sql)";
        $query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n" . $sql);


        if (mysql_num_rows($query) <= 0)
        {
            echo "No results.";
            return;
        }
        else
        {
            $filters = array();
            $last_filter = null;

            while($row = mysql_fetch_assoc($query)){
                $data = base64_decode($row['data']);
                $current_filter = unserialize($data);

                $do_swap = false;
                if (!is_null($last_filter)){
                    if ($current_filter->sub_filter($last_filter)){
                        $do_swap = true;
                    }else if($last_filter->sub_filter($current_filter)){
                        // do nothing;
                    }else{
                            die('Invalid filter set');
                    }
                }else{
                    $do_swap = true;
                }
                if ($do_swap) $last_filter = $current_filter;
                array_push($filters, $current_filter);
            }

            $sql = stripslashes($last_filter->get_sql());
            $query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n" . $sql);
            $num_rows = mysql_num_rows($query);
        	$num_pages = ceil($num_rows / $limit);
        	$cur_page = ceil($offset / $limit) + 1;

            if ($next_offset >= $num_rows)
                $next_disabled = " disabled=\"disabled\" ";
            if ($previous_offset < 0)
                $prev_disabled = " disabled=\"disabled\" ";
            $sql .= " LIMIT $offset, $limit";

			$sql = "SELECT * FROM ($sql) as oderable ORDER BY $order_by_column $order_by_direction";



            $query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n" . $sql);

            $other_headings = "";
            $table_body = "";

            $lines = $this->getSelectedLines();

            $first_row = mysql_fetch_assoc($query);
            $onchange = "sl(this, {$first_row['line_record_uid']});";

            array_push($presentLines, $first_row['line_record_uid']);

            if ($lines && in_array($first_row['line_record_uid'], $lines)){
            	$checked = "checked=\"checked\"";
            }else{
            	$checked = "";
            }

			$experimentNameEncoded = base64_encode($first_row['experiment_name']);
            $table_body .= "
            	<tr><td><input id=\"cbx_{$first_row['line_record_uid']}\" type=\"checkbox\" $checked onchange=\"$onchange\" /></td><td><a href=\"pedigree/show_pedigree.php?line={$first_row['line_record_uid']}\">" . $first_row["line_record_name"] . "</a></td><td><a href=\"view.php?table=experiments&name=$experimentNameEncoded\" target=\"_blank\">" . $first_row["experiment_name"] . "</a></td><td>" . $first_row["experiment_year"] . "</td>
            ";
            foreach($filters as $filter)
            {
                if ($filter->table_name() == 'phenotype_data'){
                    $other_headings .= "<th>" . $first_row["name_" . $filter->identifier()] . "&nbsp;&nbsp;<a href=\"javascript:;\" onclick=\"javascript:reorder_results('$offset', '$limit', 'value_".$filter->identifier()."', 'DESC');\">&uarr;</a>&nbsp;<a href=\"javascript:;\" onclick=\"javascript:reorder_results('$offset', '$limit', 'value_".$filter->identifier()."', 'ASC');\">&darr;</a><br />(" . $first_row["unit_name_" . $filter->identifier()] . ")</th>";
                    $table_body .= "<td>" . $first_row["value_" . $filter->identifier()] . "</td>";
                }

            }
            $table_body .= "</tr>";
            while($row = mysql_fetch_assoc($query))
            {
                $onchange = "sl(this, {$row['line_record_uid']});";

                array_push($presentLines, $row['line_record_uid']);

                if ($lines && in_array($row['line_record_uid'], $lines)){
	            	$checked = "checked=\"checked\"";
	            }else{
	            	$checked = "";
	            }


				$experimentNameEncoded = base64_encode($row['experiment_name']);
            	$table_body .= "
            		<tr><td><input id=\"cbx_{$row['line_record_uid']}\" type=\"checkbox\" $checked onchange=\"$onchange\" /></td><td><a href=\"pedigree/show_pedigree.php?line={$row['line_record_uid']}\">" . $row["line_record_name"] . "</a></td><td><a href=\"view.php?table=experiments&name=$experimentNameEncoded\" target=\"_blank\">" . $row["experiment_name"] . "</a></td><td>" . $row["experiment_year"] . "</td>
            	";
                foreach($filters as $filter)
                {
                    if ($filter->table_name() == 'phenotype_data')
                        $table_body .= "<td>" . $row["value_" . $filter->identifier()] . "</td>";
                }
                $table_body .= "</tr>";
            }

            $presentLinesString = implode('-', $presentLines);

$self = $_SERVER['PHP_SELF'];
?>
<h3>Results</h3>
Export All As: <a href="<?php echo $self; ?>?function=get_csv">CSV</a>, <a href="<?php echo $self; ?>?function=get_qtl">QTL</a>
<br /><br />
<?php ob_start() ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<input type="hidden" value="<?php echo $presentLinesString; ?>" id="present_lines" />
    <tr>
        <td style="text-align: left">
        	<input <?php echo $prev_disabled; ?> type="button" value="Previous" onclick="
        		new Ajax.Updater( 'results',
        			'<?php echo $self; ?>?function=get_results&arg0=<?php echo $previous_offset; ?>&arg1=<?php echo $limit; ?>',
        			{onComplete: function (){ new Effect.Pulsate('results', {duration:0.4, pulses:1});}}
        		);
        	" />
        	<input type="button" value="Select all" onclick="
        		new Ajax.Request( '<?php echo $self; ?>?function=sal&arg0='+$F('present_lines'),
        			{onComplete: function (){
        					inputs = document.getElementsByTagName('input');
        					for (i = 0; i < inputs.length; i++){
        						if ((inputs[i].id.indexOf('cbx_') == 0)){
        							inputs[i].checked = true;
								}
							}
        					new Effect.Pulsate('results', {duration:0.4, pulses:1});
        				}
        			}
        		);
        	" />
        	<input type="button" value="Select None" onclick="
        		new Ajax.Request( '<?php echo $self; ?>?function=snl&arg0='+$F('present_lines'),
        			{ onComplete: function (){
        					inputs = document.getElementsByTagName('input');
        					for (i = 0; i < inputs.length; i++){
        						if ((inputs[i].id.indexOf('cbx_') == 0)){
        							inputs[i].checked = false;
								}
							}
        					new Effect.Pulsate('results', {duration:0.4, pulses:1});
        				}
        			}
        		);
        	" />
        </td>
        <td>
        	<span style="font-size: 10px">
        	Viewing page <?php echo $cur_page; ?> of <?php echo $num_pages; ?> - Jump to page <input type="text" size="3" onkeyup="
        		var keynum;
				if (window.event) keynum = event.keyCode;
				else if (event.which) keynum = event.which;
				if (keynum != 13) return;
				/* [Enter] pressed */
        		if (this.value >= 1 && this.value <= <?php echo $num_pages; ?>){
        			offset = (this.value - 1) * <?php echo $limit; ?>;
	    			new Ajax.Updater( 'results', '<?php echo $self; ?>?function=get_results&arg0='+offset,
	    				{onComplete: function (){new Effect.Pulsate('results', {duration:0.4, pulses:1});}}
	    			);
	    		}else{
					alert('Sorry, the value you typed is invalid. Please try again.\n(Possible values include the numbers from 1 to <?php echo $num_pages; ?>)');
				}
        	" />
        	-
        	Display <input type="text" size="3" value="<?php echo $limit; ?>" onkeyup="
				var keynum;
				if (window.event) keynum = event.keyCode;
				else if (event.which) keynum = event.which;
				if (keynum != 13) return;
				/* [Enter] pressed */
				if (this.value >= 1 && this.value <= <?php echo $num_rows; ?>){
					offset = <?php echo $offset; ?>;
					if (offset % this.value != 0) offset = Math.floor(offset / this.value);
					new Ajax.Updater( 'results', '<?php echo $self; ?>?function=get_results&arg0'+offset+'&arg1='+this.value,
						{onComplete:function(){new Effect.Pulsate('results', {duration:0.4, pulses:1});}}
					);
				}else{
					alert('Sorry, the value you typed is invalid. Please try again.\n(Possible values include the numbers from 1 to <?php echo $num_rows; ?>)');
				}
        	" /> lines at a time
        	</span>
        </td>
        <td style="text-align: right">
            <input <?php echo $next_disabled; ?> type="button" value="Next" onclick="
        		new Ajax.Updater( 'results', '<?php echo $self; ?>?function=get_results&arg0=<?php echo $next_offset; ?>&arg1=<?php echo $limit?>',
        			{onComplete:function (){new Effect.Pulsate('results', {duration:0.4, pulses:1});}}
        		);
        	" />
        </td>
    </tr>
</table>
<?php
$results_header = ob_get_contents();
ob_end_clean();
?>

<?php echo $results_header ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<th>&nbsp;</th>
<th>Line&nbsp;&nbsp;<a href="javascript:;" onclick="javascript:reorder_results('<?php echo $offset; ?>', '<?php echo $limit; ?>', 'line_record_name', 'DESC');">&uarr;</a>&nbsp;<a href="javascript:;" onclick="javascript:reorder_results('<?php echo $offset; ?>', '<?php echo $limit; ?>', 'line_record_name', 'ASC');">&darr;</a></th>
<th>Experiment&nbsp;&nbsp;<a href="javascript:;" onclick="javascript:reorder_results('<?php echo $offset; ?>', '<?php echo $limit; ?>', 'experiment_name', 'DESC');">&uarr;</a>&nbsp;<a href="javascript:;" onclick="javascript:reorder_results('<?php echo $offset; ?>', '<?php echo $limit; ?>', 'experiment_name', 'ASC');">&darr;</a></th>
<th>Year&nbsp;&nbsp;<a href="javascript:;" onclick="javascript:reorder_results('<?php echo $offset; ?>', '<?php echo $limit; ?>', 'experiment_year', 'DESC');">&uarr;</a>&nbsp;<a href="javascript:;" onclick="javascript:reorder_results('<?php echo $offset; ?>', '<?php echo $limit; ?>', 'experiment_year', 'ASC');">&darr;</a></th>
<?php echo $other_headings; ?>
</tr>
    <?php echo $table_body; ?>
</table>

<?php echo $results_header ?>

<?php
        }
    }

    /* Filter Set Functions
     ***********************/

    /**
     * Adds a new filter set from the current active filters
     * arg0 - the name of the new filter set
     */
    function new_filter_set()
    {
        if (is_null($this->arg0)) die('No name specified');
        $userid = $this->get_user_id_sql($_SESSION['username']);
        $this->arg0 = base64_decode($this->arg0);
        $sql = "select * from filter_sets where users_uid = ($userid) and name = '{$this->arg0}' limit 1";
        $res = mysql_query($sql) or die(mysql_error()."<br>$sql");
        if(mysql_num_rows($res) <= 0){
        	$sql = "insert into filter_sets (users_uid, name) values (($userid), '{$this->arg0}')";
        	mysql_query($sql) or die(mysql_error()."<br>$sql");
        	$filter_set_uid = mysql_insert_id();
        	$sql = "insert into filters (users_uid, filter_set_uid, data) select users_uid, '$filter_set_uid', data from temp_filters where users_uid = ($userid)";
        	mysql_query($sql) or die(mysql_error()."<br>$sql");
        }else{
        	$row = mysql_fetch_assoc($res);
        	$sql = "delete from filters where users_uid = '{$row['users_uid']}' and filter_set_uid = '{$row['filter_set_uid']}'";
        	mysql_query($sql) or die(mysql_error()."<br>$sql");
        	$sql = "insert into filters (users_uid, filter_set_uid, data) select users_uid, '{$row['filter_set_uid']}', data from temp_filters where users_uid = '{$row['users_uid']}'";
        	mysql_query($sql) or die(mysql_error()."<br>$sql");
        }
        header("Location: {$_SERVER['PHP_SELF']}");
    }

    /**
     * Deletes the specified filter set
     * arg0 - the id of the filter set to delete
     */
    function delete_filter_set()
    {
        if (is_null($this->arg0)) die('No filter set specified');
        $userid = $this->get_user_id_sql($_SESSION['username']);
        // delete the filter set
        $sql = "delete from filter_sets where filter_set_uid = '{$this->arg0}' AND users_uid = ($userid)";
        mysql_query($sql);
        // delete the filters that belonged to that filter set
        $sql = "delete from filters where filter_set_uid = '{$this->arg0}'";
        mysql_query($sql);
        // redirect
        header("Location: {$_SERVER['PHP_SELF']}");
    }

    /**
     * Loads the specified filter set into the temp filters table
     * arg0 - the id of the filter set to load
     */
    function load_filter_set()
    {
        if (is_null($this->arg0)) die('No filter set specified');
        $userid = $this->get_user_id_sql($_SESSION['username']);
        // delete the active filters
        $sql = "delete from temp_filters where users_uid = ($userid)";
        mysql_query($sql);
        // copy the saved filters to the active filters
        $sql = "insert into temp_filters (users_uid, data) select users_uid, data from filters where filter_set_uid = '{$this->arg0}'";
        mysql_query($sql);
        // redirect
        header("Location: {$_SERVER['PHP_SELF']}");
    }





	function get_filter_sets()
	{
		$userid = $this->get_user_id_sql($_SESSION['username']);
		$sql = "select filter_set_uid as id, name from filter_sets where users_uid = ($userid)";
		$query = mysql_query($sql);
		if (mysql_num_rows($query) <= 0)
		{
			echo '<p>You do not have any saved filter sets. After creating a filter/, click the "Save As..." button to create a filter set.</p>';
		}
		else
		{
			$output = '
				<table border="0" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th width="100%">Name</th>
					</tr>';
			while ($row = mysql_fetch_assoc($query))
			{
				$output .= "
					<tr>
						<td><input type=\"button\" value=\"Load\" onclick=\"document.location='{$_SERVER['PHP_SELF']}?function=load_filter_set&arg0={$row['id']}';\" /></td>
						<td><input type=\"button\" value=\"Delete\" onclick=\"ans = confirm('Are you sure you want to delte this filter set?'); if(ans){document.location='{$_SERVER['PHP_SELF']}?function=delete_filter_set&arg0={$row['id']}';}\" /></td>
						<td style=\"text-align: left\">{$row['name']}</td>
					</tr>";
			}
			$output .= '
				</table>
				';
			echo $output;
		}
	}





	/* Utility Funtions
	*******************/
	function translate_sql_value($str)
	{
		$matches = array();
		if (preg_match("/^BETWEEN (.+) AND (.+)$/i", $str, $matches))
		{
			return $matches[1] . ' - ' . $matches[2];
		}
		if (preg_match("/^= '(.+)'$/i", $str, $matches))
		{
			return $matches[1];
		}
	}

    function has_phenotype_filters()
    {
        $sub_sql = $this->get_user_id_sql($_SESSION['username']);
        $sql = "
        	SELECT
        		*
        	FROM
        		temp_filters
        	WHERE users_uid = ($sub_sql)
        	";
        $query = mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n" . $sql . "</pre>");
        if (mysql_num_rows($query) <= 0)
            return FALSE;
        return TRUE;
    }

    /* Process Line Selections */

    function selectLine()
    {
    	if ($lineId = $this->forceArgument(0)){
    		if ($lines = $this->getSelectedLines()){
	    		if (!in_array($lineId, $lines)){
	    			array_push($lines, $lineId);
	    		}
	    	}else{
	    		$lines = array($lineId);
	    	}
	    	$this->setSelectedLines($lines);
    	}
    }

    function deselectLine()
    {
    	if ($lineId = $this->forceArgument(0)){
	    	if($lines = $this->getSelectedLines()){
	    		$deleteKey = array_search($lineId, $lines);
	    		unset($lines[$deleteKey]);
	    		$this->setSelectedLines($lines);
	    	}
    	}
    }

    // physically select all lines from arg0
    function selectLinesAll()
    {
    	if ($linesString = $this->forceArgument(0)){
    		$lines = explode('-', $linesString);
	    	if($selectedLines = $this->getSelectedLines()){
	    		foreach ($lines as $line){
	    			array_push($selectedLines, $line);
	    		}
	    	}else{
	    		$selectedLines = $lines;
	    	}
	    	$this->setSelectedLines($selectedLines);
    	}
    }

    // physically deselect all lines from arg0
    function selectLinesNone()
    {
    	if ($linesString = $this->forceArgument(0))
    	{
    		$lines = explode('-', $linesString);
    		$selectedLines = array();
    		if ($selectedLines = $this->getSelectedLines())
    		{
    			foreach ($selectedLines as $k => $line)
    			{
    				if (in_array($line, $lines))
    				{
    					unset($selectedLines[$k]);
    				}
    			}
    		}
    		$this->setSelectedLines($selectedLines);
    	}
    }

	/**
	 * Retrieves hte user's currently selected lines
	 * @return array an array of ids of the selected lines, FALSE on failure
	 */
	private function getSelectedLines()
	{
		$lines = array();
		if (isset($_SESSION['lines_string']))
		{
			$linesString = $_SESSION['lines_string'];
			$lines = explode(' ', $linesString);
			return $lines;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Sets the specified lines as the user's currently selected lines
	 * @param array $lines an array of ids of the lines to be selected
	 */
	private function setSelectedLines(array $lines)
	{
		$linesString = implode(' ', $lines);
		$_SESSION['lines_string'] = $linesString;
	}

	/**
	 * Clears the user's currently selected lines
	 */
	private function clearSelectedLines()
	{
		$_SESSION['lines_string'] = "";
	}

	/**
	 * Causes the script to exit if the specified argument is not present.
	 *
	 * @param int $offset the offset of the argument, e.g., offset 0 is arg0
	 * @return mixed the specified argument if present
	 */
	public function forceArgument($offset)
	{
		if ($offset < 0) {
			return false;
		}
		if (isset($_GET['arg'.$offset]) && ! empty($_GET['arg'.$offset])) {
			return $_GET['arg'.$offset];
		} else {
			die('Invalid Argument' . $offset);
		}
	}
}
?>
