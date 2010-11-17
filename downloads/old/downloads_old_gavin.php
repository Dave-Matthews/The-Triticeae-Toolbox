<?php
// +----------------------------------------------------------------------+
// | PHP version 5.0                                                      |
// | Prototype version 1.5.0                                              |
// +----------------------------------------------------------------------+
// | "Download Gateway"                                                   |
// |                                                                      |
// | The purpose of this script is to provide the user with an interface  |
// | for downloading certain kinds of files from THT.                     |
// +----------------------------------------------------------------------+
// | Authors:  Gavin Monroe <gemonroe@iastate.edu>                        |
// +----------------------------------------------------------------------+
set_time_limit(0);
// this is the relative path to this file
$cfg_file_rel_path = 'downloads/downloads.php';
require_once('config.php');

// Database connection override. Connects to a different database the the
// default one for this site.
$initial_connect = mysql_connect("lab.bcb.iastate.edu", "yhames04", "gdcb07");
mysql_select_db("sandbox_yhames04_dev", $initial_connect);
//mysql_select_db("sandbox_yhames04", $initial_connect);

$dontconnect = true; // Tells the bootstrapper to not automatically establish a
				     // database connection
include($config['root_dir'].'includes/bootstrap.inc');

new Downloads($_GET['function']);
//
// Using a PHP class to implement the "Download Gateway" feature
class Downloads
{
    
    private $delimiter = "\t";
    
	//
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			case 'type1':
				$this->type1();
				break;
			case 'type1experiments':
				$this->type1_experiments();
				break;
			case 'type1traits':
				$this->type1_traits();
				break;
			case 'type1markers':
				$this->type1_markers();
				break;
			case 'type1build':
				$this->type1_build_download();
				break;
			case 'type1_markers_csv':
				echo $this->type1_markers_csv_raw();
				break;
			default:
				$this->type1();
				break;
				/*type1_markers_csv*/
		}	
	}
	
	//
	// The wrapper action for the type1 download. Handles outputting the header
	// and footer and calls the first real action of the type1 download.
	private function type1()
	{
		global $config;
		include($config['root_dir'].'theme/normal_header.php');

		echo "<h2>Download Gateway</h2>";
		echo "<p><em>Currently, only data for the <b>University of Minnesota</b> is available</em></p>";
	
		$this->type1_breeding_programs_year();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	//
	// The first real action of the type1 download. Handles outputting the
	// Breeding Program and Year selection boxes as well as outputting the
	// javascript code required by itself and the other type1 actions.
	private function type1_breeding_programs_year()
	{
		?>
		<script type="text/javascript">
			
			var breeding_programs_str = "";
			var years_str = "";
			var experiments_str = "";
			var experiments_loaded = false;
			var traits_loaded = false;
			var markers_loaded = false;
            
            var markerids = null;
            var selmarkerids = new Array();
            
            var markers_loading = false;
            var traits_loading = false;
            
            var title = document.title;
			
			function update_breeding_programs(options) {
				breeding_programs_str = "";
				experiments_str = "";
				$A(options).each(function(breeding_program) {
					if (breeding_program.selected) {
						breeding_programs_str += (breeding_programs_str == "" ? "" : ",") + breeding_program.value;
					}
				});
				if (breeding_programs_str != "" && years_str != "")
					load_experiments();
			}
			
			function update_years(options) {
				years_str = "";
				experiments_str = "";
				$A(options).each(function(year) {
					if (year.selected) {
						years_str += (years_str == "" ? "" : ",") + year.value;
					}
				});
				if (breeding_programs_str != "" && years_str != "")
					load_experiments();
			}
			
			function update_experiments(options) {
				$A(options).each(function(experiment) {
					if (experiment.selected) {
						experiments_str += (experiments_str == "" ? "" : ",") + experiment.value;
					}
				});
				load_traits();
				load_markers('', '', 100, 0);
			}

			function load_experiments()
			{
                $('experiments_loader').hide();
                document.title='Loading...';
				new Ajax.Updater(
                    $('experiments_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1experiments&bp=' + breeding_programs_str + '&yrs=' + years_str,
					{ 
                        onComplete: function() {
                            $('experiments_loader').show();
                            document.title=title;
                        }
                    }
				);
				experiments_loaded = true;
				if (traits_loaded == true)
					load_traits();
				if (markers_loaded == true)
					load_markers('', '', 100, 0);
			}
			
			function load_traits()
			{		
                traits_loading = true;		
                $('traits_loader').hide();
                document.title='Loading...';
				new Ajax.Updater($('traits_loader'), '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1traits&exps='+experiments_str,
					{onComplete: function() {
                        $('traits_loader').show();  
                        if (markers_loading == false) {
                            document.title = title;
                        }
                        traits_loading = false;
                        traits_loaded = true;
                    }}
				);
			}
            
            function lm(o,d){
                var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                load_markers(o,d,mm,mmaf);
            } 
			function load_markers(o, d, mm, mmaf) {
                markers_loading = true;
				$('markers_loader').hide();
                document.title='Loading...';
                new Ajax.Updater($('markers_loader'), '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1markers&exps='+experiments_str+'&o='+o+'&d='+d+'&m='+selmarkerids.join(',')+'&mm='+mm+'&mmaf='+mmaf,
					{onComplete: function() {
                        markerids = $('muids').getValue().split(",");
                        $('markers_loader').show();
                        if (traits_loading == false) {
                            document.title = title;
                        }
                        markers_loading = false;
                        markers_loaded = true;
                    }}
				);
			}
			
			function selectedTraits() {
				var ret = '';
				$A($('traitsbx').options).each(function(trait){
				 	if (trait.selected)
					{
						ret += (ret == '' ? '' : ',') + trait.value;
					}			 
				});
				return ret;
			}
			

			function getdownload(type)
			{
				if (selectedTraits() == '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
				if (type == 'qtlminer')
					document.location = '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1build&m='+selmarkerids.join(',')+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
				else
					document.location = '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1build&m='+selmarkerids.join(',')+'&t='+selectedTraits()+'&e='+experiments_str+'&mono=true&mm='+mm+'&mmaf='+mmaf;
			}

            function mrefresh() {
                var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                var o = $('co').getValue();
                var d = $('cd').getValue();
                load_markers(o, d, mm, mmaf);
            }
            
            // select all
            function exclude_all() {
                for (var i=0; i<markerids.length; ++i) {
                    $('exbx_'+markerids[i]).checked = true;
                }
                selmarkerids = markerids; // all
            }
            // select none
            function exclude_none() {
                for (var i=0; i<markerids.length; ++i) {
                    $('exbx_'+markerids[i]).checked = false;
                }
                selmarkerids = new Array(); // empty
            }
            // select/deselect
            function sm(exbx, id) {
                if (exbx.checked == true)
                    selmarkerids.push(id);
                else
                    selmarkerids = selmarkerids.without(id);
            }
		</script>
		<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<div style="float: left; margin-bottom: 1.5em;">
		<h3>1. Breeding Program & Year</h3>
			<table>
				<tr>
					<th>Breeding Program</th>
					<th>Year</th>
				</tr>
				<tr>
					<td>
						<select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
		<?php
		$sql = "SELECT breeding_programs_uid AS id, breeding_programs_name AS name FROM breeding_programs";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
			?>
							<option value="<?php echo $row['id'] ?>"><?php echo $this->friendly_breeding_program_name($row['name']) ?></option>
			<?php
		}
		?>
						</select>
					</td>
					<td>
						<select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
		<?php
		$sql = "SELECT experiment_year AS year FROM experiments GROUP BY experiment_year ASC";
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
		<div id="experiments_loader" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="traits_loader" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="markers_loader" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
<?php
	}
	
	private function type1_experiments()
	{
		$breeding_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
		$years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>
<h3>2. Experiments</h3>
<div>

<table>
	<tr><th>Experiment</th></tr>
	<tr><td>
		<select name="experiments" multiple="multiple"
		  style="height: 12em" onchange="javascript: update_experiments(this.options)">
<?php
		$sql = "SELECT e.experiment_uid AS id, e.experiment_name AS name, e.experiment_year AS year 
				FROM experiments AS e, datasets AS d WHERE e.datasets_uid = d.datasets_uid
				AND e.experiment_year IN ($years)
				AND d.breeding_programs_uid IN ($breeding_programs)
				ORDER BY e.experiment_year DESC";
		$res = mysql_query($sql) or die(mysql_error());
		$last_year = NULL;
		while ($row = mysql_fetch_assoc($res)) {			
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
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
<?php
		}
?>
			</optgroup>
		</select>
	</td></tr>
</table>
</div>
<?php
	}
	
	private function type1_traits()
	{
		$experiments = $_GET['exps'];
		if (empty($experiments))
		{
			echo "
				<h3>3. Traits</h3>
				<div>
					<p><em>No Experiments Selected</em></p>
				</div>";
		}
		else
		{
?>
<h3>3. Traits</h3>
<div>
<?php
			$sql = "SELECT p.phenotype_uid AS id, p.phenotypes_name AS name
					FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
					WHERE pd.tht_base_uid = t.tht_base_uid
					AND p.phenotype_uid = pd.phenotype_uid
					AND t.experiment_uid IN ($experiments)
					GROUP BY p.phenotype_uid";
			$res = mysql_query($sql) or die(mysql_error());
			if (mysql_num_rows($res) >= 1)
			{
?>
<table>
	<tr><th>Trait</th></tr>
	<tr><td>
		<select id="traitsbx" name="traits" multiple="multiple" style="height: 12em">
<?php
				while ($row = mysql_fetch_assoc($res))
				{
?>
			<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
<?php
				}
?>
		</select>
	</td></tr>
</table>
<?php
			}
			else
			{
?>
		<p style="font-weight: bold;">No Data</p>
<?php
			}
?>
</div>
<?php
		}
	}
	
	/**
	 * Displays the marker table
	 */
	private function type1_markers()
	{
		global $cfg_file_rel_path;
		
        // parse url
        $experiments = $_GET['exps'];
		$selected_markers = array();
		if (isset($_GET['m']) && !empty($_GET['m'])) $selected_markers = explode(',', $_GET['m']);
        $max_missing = 1;
        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'] * 0.01;
        $min_maf = 0;
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'] * 0.01;
		$orderbys = array(
			'name' => 'name',
			'pos' => 'position',
			'miss' => 'a.missing',
			'aa' => 'a.aa_freq',
			'ab' => 'a.ab_freq',
			'bb' => 'a.bb_freq',
			'tot' => 'a.total',
            'maf' => 'a.maf'
		);
		$directions = array('a' => 'asc', 'd' => 'desc');
        $o = 'name';
        $d = 'a';
        $orderby = 'name';
        $direction = 'asc';
		if (isset($_GET['o']) && in_array($_GET['o'], array_keys($orderbys))){
			$orderby = $orderbys[$_GET['o']];
            $o = $_GET['o'];
        }
		if (isset($_GET['d']) && in_array($_GET['d'], array_keys($directions))) {
			$direction = $directions[$_GET['d']];
            $d = $_GET['d'];     
        }
        ?>
        <h3>4. Markers</h3>
		<div>
		<?php
		if (empty($experiments)) {
		    ?><p><em>No Experiments Selected</em></p><?php
		} else {
            ?><div class="notice"><p style="font-style: italic">Monomorphic markers are shaded gray and will <u>NOT</u> be included in the QTLMiner download.</p></div><?php
			
			if (!isset($orderby)) $orderby = "name";
			if (!isset($direction)) $direction = "asc";
		    
            $sql = <<< SQL
                select
                    m.marker_uid,
                    ms.value as name,
                    CONCAT('Chr ', map.map_name, ' ', mim.start_position, ' cM') as position,
                    a.missing,
                    a.aa_freq,
                    a.ab_freq,
                    a.bb_freq,
                    a.total,
                    a.monomorphic,
                    ROUND(a.maf, 2) as maf
                from
                    markers m inner join (allele_frequencies a, marker_synonyms ms, markers_in_maps mim, map, datasets d, experiments e)
                        on m.marker_uid = a.marker_uid
                        and m.marker_uid = ms.marker_uid
                        and m.marker_uid = mim.marker_uid
                        and mim.map_uid = map.map_uid
                        and a.datasets_uid = d.datasets_uid
                        and d.datasets_uid = e.datasets_uid
                where
                    e.experiment_uid in ($experiments)
                    and a.missing / a.total <= $max_missing
                    and a.maf >= $min_maf
                    and ms.marker_synonym_type_uid = 1
                group by name
                order by $orderby $direction
            
SQL;
			$res = mysql_query($sql) or die(mysql_error());
			
            $markerids = array();
			if (mysql_num_rows($res) >= 1) {
                ?>
                <style type="text/css">
                    #markers_loader tr, #markers_loader tr td
                    { background-color: #fff; }
                    #markers_loader tr.mono, #markers_loader tr.mono td
                    { background-color: #eee; }
                    a.arr
                    { font-size: large; font-weight: normal; text-decoration: none; border: 0; }
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    th.marker a
                    { color: #fff; }  
                    th.marker a:hover
                    { color: #fff; }
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
                <table cellspacing="0" cellpadding="0" border="0" style="width: 725px; border: 0">
                    <tr>
                        <th style="width: 75px;" class="marker">Exclude?</th>
                        <th style="width: 100px;" class="marker">Marker<br />
                        <a class="arr" href="#" onclick="javascript:lm('name','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('name','d');return false;">(&darr;)</a></th>
                        <th style="width: 100px;" class="marker">Position<br />
                        <a class="arr" href="#" onclick="javascript:lm('pos','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('pos','d');return false;">(&darr;)</a></th>
                        <th style="width: 75px;" class="marker">Missing<br />
                        <a class="arr" href="#" onclick="javascript:lm('miss','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('miss','d');return false;">(&darr;)</a></th>
                        <th style="width: 75px;" class="marker">AA Freq<br />
                        <a class="arr" href="#" onclick="javascript:lm('aa','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('aa','d');return false;">(&darr;)</a></th>
                        <th style="width: 75px;" class="marker">BB Freq<br />
                        <a class="arr" href="#" onclick="javascript:lm('bb','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('bb','d');return false;">(&darr;)</a></th>
                        <th style="width: 75px;" class="marker">AB Freq<br />
                        <a class="arr" href="#" onclick="javascript:lm('ab','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('ab','d');return false;">(&darr;)</a></th>
                        <th style="width: 75px;" class="marker">Freq Total<br />
                        <a class="arr" href="#" onclick="javascript:lm('tot','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('tot','d');return false;">(&darr;)</a></th>
                        <th style="width: 75px;" class="marker">MAF<br />
                        <a class="arr" href="#" onclick="javascript:lm('maf','a');return false;">(&uarr;)</a><a class="arr" href="#" onclick="javascript:lm('maf','d');return false;">(&darr;)</a></th>
                    </tr>
                </table>
                <div style="padding: 0; width: 725px; height: 300px; overflow: scroll; border: 1px solid #5b53a6; clear: both">
                <table cellspacing="0" cellpadding="0" border="0">
                <?php
                $marker = $aa_freq = $bb_freq = $ab_freq = $missing = $tot_freq = $maf = $position = $checked = null;
				while ($row = mysql_fetch_assoc($res)){
				    $markerids[] = intval($row['marker_uid']);
					$marker = $row['name'];
					$aa_freq = $row['aa_freq'];
					$bb_freq = $row['bb_freq'];
					$ab_freq = $row['ab_freq'];
					$missing = $row['missing'];
					$tot_freq = $row['total'];
                    $maf = $row['maf'];
                    $position = $row['position'];
					$checked = '';
					if (in_array($row['marker_uid'], $selected_markers)) $checked = ' checked="checked"';
                    ?>
                    <tr <?php if ($row['monomorphic'] == 'Y') { echo 'class="mono"'; } ?>>
                        <td style="width: 75px; background: #eee" class="marker"><input onchange="sm(this, <?php echo $row['marker_uid'] ?>);" id="exbx_<?php echo $row['marker_uid'] ?>" class="exbx" type="checkbox" value="<?php echo $row['marker_uid'] ?>"<?php echo $checked ?>></td>
                        <td style="width: 100px; text-align: left;" class="marker"><?php echo $row['name'] ?></td>
                        <td style="width: 100px; text-align: left;" class="marker"><?php echo $row['position'] ?></td>
                        <td style="width: 75px;" class="marker"><?php echo $row['missing'] ?></td>
                        <td style="width: 75px;" class="marker"><?php echo $row['aa_freq'] ?></td>
                        <td style="width: 75px;" class="marker"><?php echo $row['bb_freq'] ?></td>
                        <td style="width: 75px;" class="marker"><?php echo $row['ab_freq'] ?></td>
                        <td style="width: 75px;" class="marker"><?php echo $row['total'] ?></td>
                        <td style="width: 75px;" class="marker"><?php echo $row['maf'] ?></td>
                    </tr>

                    <?php
				}
                ?>
                </table>
                </div>
                <div style="padding-left: 20px">
                    Check <a href="#" onclick="javascript:exclude_all();return false;">All</a>&nbsp;/&nbsp;
                    <a href="#" onclick="javascript:exclude_none();return false;">None</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    Maximum Missing Data (%): <input type="text" name="mm" id="mm" size="3" value="<?php echo ($max_missing*100) ?>" />&nbsp;&nbsp;|&nbsp;&nbsp;
                    Minimum MAF (%): <input type="text" name="mmaf" id="mmaf" size="3" value="<?php echo ($min_maf*100) ?>" />&nbsp;&nbsp;|&nbsp;&nbsp;
                    <input type="button" value="Refresh" onclick="javascript:mrefresh();return false;" />
                    <input type="hidden" id="co" name="co" value="<?php echo $o ?>" />
                    <input type="hidden" id="cd" name="cd" value="<?php echo $d ?>" />
                    <input type="hidden" id="muids" name="muids" value="<?php echo implode(',', $markerids) ?>" /><br />
                    <br />
                    <input type="button" value="Download" onclick="javascript:getdownload('default');return false;" />
                    <input type="button" value="Download for QTLMiner" onclick="javascript:getdownload('qtlminer');return false;" />
                </div>
				<?php
			} else {
				?><p style="font-weight: bold">No Data</p><?php
			}
		}
		?></div><?php
	}
	
	function type1_build_download()
	{
		$experiments = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
		$markers = (isset($_GET['m']) && !empty($_GET['m'])) ? $_GET['m'] : null;
		$traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
		$incmono = (isset($_GET['mono']) && $_GET['mono'] == 'true') ? true : false;
		
		set_include_path(get_include_path . PATH_SEPARATOR . '../pear/');
		require_once('../pear/File/Archive.php');
		
		$dir = 'temp/';
		$filename = 'download'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
		
        // File_Archive doesn't do a good job of creating files, so we'll create it first
		if(!file_exists($dir.$filename)){
			$h = fopen($dir.$filename, "w+");
			fclose($h);
		}
		
        // Now let File_Archive do its thing
		$zip = File_Archive::toArchive($dir . $filename, File_Archive::toFiles());
		$zip->newFile("traits.txt");
		$zip->writeData($this->type1_biuld_traits_download($experiments, $traits));
		$zip->newFile("markers.txt");
		$zip->writeData($this->type1_build_markers_download($experiments, $markers, $incmono));
		$zip->newFile("pedigree.txt");
		$zip->writeData($this->type1_build_pedigree_download($experiments));
		$zip->close();
		
		header("Location: ".$dir.$filename);
	}
	
	private function type1_biuld_traits_download($experiments, $traits)
	{
		$output = 'Experiment' . $this->delimiter . 'Inbred';
		$traits = explode(',', $traits);
		$select = "SELECT experiments.experiment_name, experiments.number_replications, line_records.line_record_name";
		$from = " FROM 
					tht_base
					JOIN experiments ON experiments.experiment_uid = tht_base.experiment_uid
					JOIN line_records ON line_records.line_record_uid = tht_base.line_record_uid ";
		foreach ($traits as $trait) {
			$from .= " JOIN (
						SELECT phenotypes.phenotypes_name, phenotype_data.value, phenotype_data.tht_base_uid
						FROM phenotype_data JOIN phenotypes ON phenotypes.phenotype_uid = phenotype_data.phenotype_uid
						WHERE phenotypes.phenotype_uid = $trait) as t$trait ON t$trait.tht_base_uid = tht_base.tht_base_uid ";
			$select .= ", t$trait.phenotypes_name as name$trait, t$trait.value as value$trait";
		}
		$where = " WHERE tht_base.experiment_uid IN ($experiments)";
		$res = mysql_query($select.$from.$where) or die(mysql_error());
		
		$namevaluekeys = null;
		$valuekeys = array();
		while($row = mysql_fetch_assoc($res)) {
			if ($namevaluekeys == null)
			{
				$namevaluekeys = array_keys($row);
				unset($namevaluekeys[array_search('experiment_name', $namevaluekeys)]);
				unset($namevaluekeys[array_search('number_replications', $namevaluekeys)]);
				unset($namevaluekeys[array_search('line_record_name', $namevaluekeys)]);
				
				foreach($namevaluekeys as $namevaluekey) {
					if (stripos($namevaluekey, 'name') !== FALSE) {
						$output .= $this->delimiter . "{$row[$namevaluekey]}" . $this->delimiter . "N";
					} else {
						array_push($valuekeys, $namevaluekey);
					}
				}
				$output .= "\n";
			}
			$output .= "{$row['experiment_name']}" . $this->delimiter . "{$row['line_record_name']}";
			foreach($valuekeys as $valuekey) {
				if (is_null($row[$valuekey]))
					$row[$valuekey] = 'N/A';
				$output .= $this->delimiter . "{$row[$valuekey]}" . $this->delimiter . "{$row['number_replications']}";
			}
			$output .= "\n";
		}
		return $output;
	}
	
	private function type1_build_markers_download($experiments, $markers, $incmono = false)
	{
		$outputheader = '';
		$output = '';
		$doneheader = false;
        
        $max_missing = 1;
        $min_maf = 0;
        if (isset($_GET['mm'])) $max_missing = $_GET['mm'] * 0.01;
        if (isset($_GET['mmaf'])) $min_maf = $_GET['mmaf'] * 0.01;
        
		
		$lookup = array(
			'AA' => '1',
			'BB' => '-1',
			'--' => 'NA',
			'AB' => '0'
		);
		
        $sql = "SELECT
                        line_records.line_record_name,
						marker_synonyms.value AS name,
                        alleles.value
					FROM
                        markers,
						allele_frequencies,
						experiments,
                        marker_synonyms,
                        line_records,
                        alleles,
                        tht_base,
                        genotyping_data
					WHERE
						markers.marker_uid = allele_frequencies.marker_uid
						AND allele_frequencies.datasets_uid = experiments.datasets_uid
						AND experiments.experiment_uid = tht_base.experiment_uid
                        AND tht_base.experiment_uid IN ($experiments)
                        AND markers.marker_uid = marker_synonyms.marker_uid
                        AND marker_synonyms.marker_synonym_type_uid = 1
                        AND allele_frequencies.missing / allele_frequencies.total <= $max_missing
                        AND allele_frequencies.maf >= $min_maf
                        AND markers.marker_uid = genotyping_data.marker_uid
                        AND genotyping_data.genotyping_data_uid = alleles.genotyping_data_uid
                        AND tht_base.line_record_uid = line_records.line_record_uid
						";
		if (!$incmono) {
		    $sql .= " AND allele_frequencies.monomorphic = 'N' ";
			/*$sql = "
			SELECT line_records.line_record_name, t1.marker_name, marker_synonyms.value as name, t1.value
			FROM experiments, allele_frequencies, marker_synonyms, tht_base 
				JOIN (
					SELECT markers.marker_uid, markers.marker_name, alleles.value, genotyping_data.tht_base_uid
					FROM genotyping_data
						JOIN markers ON markers.marker_uid = genotyping_data.marker_uid
						LEFT OUTER JOIN alleles ON alleles.genotyping_data_uid = genotyping_data.genotyping_data_uid
				) AS t1 ON t1.tht_base_uid = tht_base.tht_base_uid
				JOIN line_records ON line_records.line_record_uid = tht_base.line_record_uid
			WHERE tht_base.experiment_uid IN ($experiments)
			AND experiments.experiment_uid = tht_base.experiment_uid
			AND allele_frequencies.datasets_uid = experiments.datasets_uid
			AND allele_frequencies.marker_uid = t1.marker_uid
            AND t1.marker_uid = marker_synonyms.marker_uid
            AND marker_synonyms.marker_synonym_type_uid = 1
            AND allele_frequencies.missing / allele_frequencies.total <= $max_missing
            AND allele_frequencies.maf >= $min_maf
			AND allele_frequencies.monomorphic = 'N' ";*/
		} else {
		    
		    /*$sql = "
			SELECT
                line_records.line_record_name,
                t1.marker_name,
                marker_synonyms.value as name,
                t1.value
			FROM experiments, allele_frequencies, marker_synonyms, tht_base 
				JOIN (
					SELECT markers.marker_uid, markers.marker_name, alleles.value, genotyping_data.tht_base_uid
					FROM genotyping_data
						JOIN markers ON markers.marker_uid = genotyping_data.marker_uid
						LEFT OUTER JOIN alleles ON alleles.genotyping_data_uid = genotyping_data.genotyping_data_uid
				) AS t1 ON t1.tht_base_uid = tht_base.tht_base_uid
				JOIN line_records ON line_records.line_record_uid = tht_base.line_record_uid
			WHERE tht_base.experiment_uid IN ($experiments)
			AND experiments.experiment_uid = tht_base.experiment_uid
			AND allele_frequencies.datasets_uid = experiments.datasets_uid
			AND allele_frequencies.marker_uid = t1.marker_uid
            AND t1.marker_uid = marker_synonyms.marker_uid
            AND marker_synonyms.marker_synonym_type_uid = 1
            AND allele_frequencies.missing / allele_frequencies.total <= $max_missing
            AND allele_frequencies.maf >= $min_maf ";
			/*$sql = "
			SELECT line_records.line_record_name, t1.marker_name, t1.value
			FROM marker_synonyms, tht_base
				JOIN (
					SELECT markers.marker_uid, markers.marker_name, alleles.value, genotyping_data.tht_base_uid
					FROM genotyping_data
						JOIN markers ON markers.marker_uid = genotyping_data.marker_uid
						LEFT OUTER JOIN alleles ON alleles.genotyping_data_uid = genotyping_data.genotyping_data_uid
				) AS t1 ON t1.tht_base_uid = tht_base.tht_base_uid
				JOIN line_records ON line_records.line_record_uid = tht_base.line_record_uid
			WHERE tht_base.experiment_uid IN ($experiments)
            AND marker_synonyms.marker_synonym_type_uid = 1";*/
		}
		
		if (!empty($markers))
			$sql .= " AND markers.marker_uid NOT IN ($markers) ";
		$sql .= "GROUP BY line_records.line_record_name, marker_synonyms.value";

		$res = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_assoc($res);
		
		while($row !== FALSE)
		{
			$last_line = $row['line_record_name'];
			$output .= "$last_line";
			while($last_line == $row['line_record_name'])
			{
				if (!$doneheader)
					$outputheader .= $this->delimiter . "{$row['name']}";
					
				$output .= $this->delimiter . "{$lookup[$row['value']]}";	
				$row = mysql_fetch_assoc($res);
			}
			$doneheader = true;
			$output .= "\n"; 
		}
		//echo $outputheader."\n".$output;
		return $outputheader."\n".$output;
	}
	
	
	private function type1_markers_csv_raw()
	{
		header("Content-type: text/plain");
		return $this->type1_build_markers_download($_GET['experiments'], $_GET['markers'], $_GET['incmono']);
	}
	
	private function type1_build_pedigree_download($experiments)
	{
		$output = "Inbred" . $this->delimiter . "Parent1" . $this->delimiter . "Parent2" . $this->delimiter . "Contrib1" . $this->delimiter . "Contrib2\n";
		$sql = "SELECT line_records.line_record_name
			FROM line_records LEFT OUTER JOIN (
				SELECT line_record_uid, experiments.experiment_uid FROM tht_base JOIN
				experiments ON experiments.experiment_uid = tht_base.experiment_uid) AS t1
				ON t1.line_record_uid = line_records.line_record_uid
			WHERE t1.experiment_uid IN ($experiments)";
		$res = mysql_query($sql) or die(mysql_error());
		$count = 1;
		while($row=mysql_fetch_assoc($res))
		{
			$output .= "{$row['line_record_name']}" . $this->delimiter . "Parent1_$count" . $this->delimiter . "Parent2_$count" . $this->delimiter . ".5" . $this->delimiter . ".5\n";
			$count++;
		}
		return $output;
	}
	
	
	/**
	 * Converts a specified breeding program code into a breeding program name
	 * @param string $code the specified breeding program code
	 * @return string the corresponding breeding program name
	 */
	private function friendly_breeding_program_name($code)
	{
		$mappings = array(
			"OSU" => "Oregon State University",
			"WSU" => "Washington State University",
			"NDSU-2R" => "North Dakota State University (2 row)",
			"NDSU-6R" => "North Dakota State University (6 row)",
			"VT" => "Virginia Tech",
			"UMN" => "University of Minnesota",
			"USU" => "Utah State University",
			"UI" => "University of Idaho",
			"BARI" => "Busch Agricultural Resources Inc.",
			"MSU" => "Montana State University"
		);
		return isset($mappings[$code]) ? $mappings[$code] : $code;
	}
}// end class
