/**
 *
 */
<?php
require_once('config.php');
require_once($config['root_dir'].'includes/bootstrap.inc');
connect();

new AdvancedLineRecrodsSearch();
class AdvancedLineRecrodsSearch
{
	
	//--
	// Config
	//--
	private $searchChoices = array(
		"row_type" => "Row Type",
		"primary_end_use" => "End Use"
	);
	
	
	private $includeLayout = true;
	private $output = "";
	
	//---
	// Constructor
	//---
	public function __construct()
	{		
		$action = $_GET['action'];
		switch($action)
		{
			case 'search':
				$this->displaySearchForm();
				$this->displayLinesTable();
				break;
			default:
				$this->displaySearchForm();
				break;
		}
		
		if ($this->includeLayout)
		{
			$this->displayHeader();
			$this->displayFooter();	
		}
		$this->display();
	}
	
	private function displayHeader()
	{
		global $config;
		ob_start();
		include($config['root_dir'].'theme/normal_header.php');
		$this->output = ob_get_contents() . "\n<h1>Advanced Line Search</h1>\n" . $this->output;
		ob_end_clean();
	}
	
	private function displayFooter()
	{
		global $config;
		$footer_div = 1;
		ob_start();
		include($config['root_dir'].'theme/footer.php');
		$this->output .= ob_get_contents();
		ob_end_clean();
	}
	
	//---
	// Adds search form to output
	//---
	private function displaySearchForm()
	{
		$action = $_SERVER['PHP_SELF'] . "?action=search";
		$this->output .= "<form action=\"$action\" method=\"post\">\n<fieldset>\n";
		$this->output .= "Property: <select name=\"choice\">\n";
		$this->output .= "<option value=\"\">(Select One)</option>\n";
		foreach($this->searchChoices as $idx => $choice)
		{
			$this->output .= "<option value=\"$idx\">$choice</option>\n";
		}	
		$this->output .= "</select><br />\n";
		$this->br(1);
		$this->output .= "Value: <input type=\"text\" name=\"value\" /><br />\n";
		$this->br(1);
		$this->output .= "<input type=\"submit\" value=\"Submit\" />\n";
		$this->output .= "</fieldset>\n</form>\n";
	}
	
	//---
	// Adds a table of lines to the output
	//---
	private function displayLinesTable()
	{
		global $config;
		
		if (!isset($_REQUEST['choice']) || !isset($_REQUEST['value']))
			header("Location: " . $config['base_url'] . "lines.php");
		$this->validateSearchForm();
		if (isset($_REQUEST['page']))
			$res = $this->getLines($_REQUEST['choice'], $_REQUEST['value'], $_REQUEST['page']);
		else
			$res = $this->getLines($_REQUEST['choice'], $_REQUEST['value']);
		$row = mysql_fetch_assoc($res);
		
		$of = ceil(intval($row['num']) / intval($row['lim']));
		
		$this->output .= "<p>--- Displaying page {$row['page']} of $of --- </p>\n";
		
		for ($i = 1; $i <= $of; $i++)
		{
			$this->output .= "<a href=\"{$config['base_url']}advanced/lines.php?action=search&page=$i&choice={$_REQUEST['choice']}&value={$_REQUEST['value']}\">[$i]</a>";
		}
		
		$this->output .= "<br />\n<br />\n";
		$this->output .= "<table>\n<tr>\n";
		$this->output .= "<th>Line Name</th><th>{$this->searchChoices[$_REQUEST['choice']]}</th>\n";
		$this->output .= "</tr>\n";
		
		$counter = ($row['page']-1) * $row['lim'] + 1;
		while($row = mysql_fetch_assoc($res))
		{
			$this->output .= "<tr>\n";
			$this->output .= "<td style=\"text-align: left\">$counter. <a href=\"{$config['base_url']}view.php?table=line_records&uid={$row['line_record_uid']}\">{$row['line_record_name']}</a></td><td>{$row[$_REQUEST['choice']]}</td>\n";
			$this->output .= "</tr>\n";
			$counter ++;
		}
		
		$this->output .= "</table>";
	}
	
	private function getLines($fieldName, $value, $page = 1, $orderBy = 'line_record_name', $direction = 'ASC', $num = 25)
	{		
		$offset = ($page - 1) * $num;
		$sql = "
			(SELECT NULL as line_record_uid, NULL as line_record_name, NULL as $fieldName, COUNT(*) AS num, '$page' as page, '$num' as lim
			FROM line_records
			WHERE $fieldName = '$value')
			UNION
			(SELECT line_record_uid, line_record_name, $fieldName, NULL, NULL, NULL
			FROM line_records
			WHERE $fieldName = '$value'
			ORDER BY $orderBy $direction
			LIMIT $offset, $num)		
		";
		return mysql_query($sql);// or die(mysql_error() . $this->br(2, true) . $sql);
	}
	
	//---
	// Returns generated output
	//---
	public function display()
	{
		echo $this->output;
	}
	
	//===
	// Utility functions
	//===
	
	//---
	// Add line breaks to output
	//---
	private function br($num, $return = false)
	{
		$output = "";
		for ($i = 0; $i < $num; $i++)
		{
			$output .= "<br />\n";
		}
		if ($return)
		{
			return $output;
		}
			$this->output .= $output;
	}
	
	private function validateSearchForm()
	{
		if (empty($_REQUEST['choice']) || empty($_REQUEST['value']))
			header("Location: " . $config['base_url'] . "lines.php");
		
	}
	
}


?>