<?php
/**
 * Display Map information from database
 *
 * PHP version 5.3
 *
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/maps.php
 *
 * 04/04/2013   C.Birkett make column height dynamic so scroll bars are not used
 * 06/22/2012   C.Birkett sort each column so rows are aligned, move style sheet to top
 * 1apr12 dem: Small cleanups.  Needs work.
 * 10/19/2010   J.Lee use dynamic GBrowse tracks generation
 * 09/02/2010   J.Lee modify to add new snippet Gbrowse tracks
*/
namespace T3download;

require_once 'config.php';
include_once $config['root_dir'].'includes/bootstrap.inc';
connect();

$mapsetStr = "";
$sql = "select mapset_name from mapset";
$sql_r = mysql_query($sql) or die(mysql_error());

while ($row = mysql_fetch_assoc($sql_r)) {
    $val = $row["mapset_name"];
    //echo 	$row["mapset_name"];
    $mapsetStr.= $val.",";
}
$mapsetStr = (substr($mapsetStr, 0, (strlen($mapsetStr)-1)));

new Maps($_GET['function']);

/**
 * Using the class's constructor to decide which action to perform
 * @author claybirkett
 *
 */
class Maps
{
  /**
   * delimiter used for output files
   */
    private $delimiter = "\t";
 
    public function __construct($function = null)
    {
        switch ($function) {
            case 'typeMaps':
                $this->type_Maps(); /* Handle Maps */
                break;
            case 'typeMarkers':
                $this->type_Markers();  /* Handle Markers */
                break;
            case 'typeMarkerAnnotation':
                $this->type_Marker_Annotation();  /* Handle Annotations */
                break;
            case 'typeMarkerExcel':
                $this->type_Marker_Excel();  /* Exporting to excel*/
                break;
            case 'typeAnnotationComments':
                $this->type_Annotation_Comments(); /* displaying annotation comments*/
                break;
            default:
                $this->typeMapSet(); /* intial case*/
                break;
        }
    }

    // The wrapper action for the typeMapset . Handles outputting the header
    // and footer and calls the first real action of the typeMapset .
    private function typeMapSet()
    {
        global $config;
        include $config['root_dir'].'theme/normal_header.php';

        echo "<h2>Map Sets</h2>";
        $this->typeMapSetDisplay();
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }
    //
    // The first real action of the typeMapset. Handles outputting the
    // Mapset names selection boxes as well as outputting the
    // javascript code required by itself and the other typeMapset actions.
    private function typeMapSetDisplay()
    {
    ?>
		<!--Style sheet for better user interface-->
		
		<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; }
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<style type="text/css">

                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
		      { background: #5b53a6; color: #fff; border: 1px solid #666 !important; border-color: black; text-align: left;}
                    
                    td.marker
                    { border: 1 !important; }
        </style>
<a href="map_flapjack.php">Download a complete Map Set</a>, all chromosomes.<p>
<a href="/jbrowse/?data=wheat">View in JBrowse.</a><br><br>

<script type="text/javascript">

      var all_mapSets = <?php echo json_encode($mapsetStr); ?>;
      var link_url = "";
      var mapset_str = "";
      var comment_str = "";
      var markers_annotation_str = "";
      var excel_str1 = "";
      var excel_str2 = "";
      var maps_str = "";
      var annotation_str = "";
      var maps_loaded = false;
      var markers_loaded = false;
      
      /* Function for invoking export to excel functionality  */
      function load_excel() {
	  excel_str1 =	markers_annotation_str;
	  excel_str2 =  maps_str;
	  var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeMarkerExcel'+ '&mxls1=' + excel_str1 + '&mxls2=' + excel_str2;
	  // Opens the url in the same window
	  window.open(url, "_self");
      }
      
      /* Function to open annotation link in a new window */
      function link_for_value(link) {
	  myWin = window.open(link, '');
	  // 		link_url = link;
	  // 		window.open(link_url,
	  // 'open_window',
	  // 'menubar, toolbar, location, directories, status, scrollbars, resizable, dependent, width=640, height=480, left=0, top=0');
	  // link_url = link;
	  // Just open a new tab instead.  In Mac Safari, this changes focus to that tab.
	  // In Firefox it does not unless we leave the name (second argument) empty.
	  // myWin = window.open(link, 'open_window');
	  //myWin.focus(); // Does nothing.
      }
	    
      /* Function for displaying extended comments in a pop up window */
      function display_comments(comvalue) {
	  alert(comvalue);
	  // This is ugly:
	  // comment_str = comvalue;
	  // my_window= window.open ("",  "mywindow1","status=1,width=800,height=300");
	  // my_window.document.write(comment_str);
	  // if (window.focus) {my_window.focus()}
      }
      
      /* Function for passing selected mapset name */
      function update_mapset(test) {
	  mapset_str = test;
	  load_maps();
      }
      
		/*
			Function for passing selected map name
		*/
		
		function update_maps(Str)
			{
				
				maps_str = Str;
				
				load_markers();
			}
			
		/*
			Function for passing selected marker name
		*/
			
			function update_markers_annotations(markvalue)
			{
			
			markers_annotation_str = markvalue;
			
			load_marker_annotation();
			}	
			
		/*
			Function for passing selected annotation name
		*/
			
			function annotation_comments(ann_name)
			{
			annotation_str = ann_name;
			load_annotation_comments();
			}
			
				
		/*
			Function for loading maps dropdown
		*/
		
		function load_maps()
			{
				
                $('maps_loader').hide();
                
				new Ajax.Updater(
                    $('maps_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=typeMaps'+ '&mset=' + mapset_str ,
					{ 
                        onComplete: function() {
                        		
                            $('maps_loader').show();
                        }
                    }
				);
				maps_loaded = true;
				
			}
			
			
			/*
			Function for loading Markers dropdown table
		*/			
			
			function load_markers()
			{
			$('markers_loader').hide();
                
				new Ajax.Updater(
                    $('markers_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=typeMarkers' + '&mp=' + maps_str ,
					{ 
                        onComplete: function() {
                            $('markers_loader').show();
			    var mapname = $j("select[name='mapsdetails'] option:selected")
			      .attr('value');
			    var chr = mapname.substr((mapname.indexOf("_",0)) + 1, 2).toUpperCase();
                            var tocollapse = $j("select[name='mapsetnames'] option")
			      .not(':selected')
			      .map(function () { return $j(this).attr('value'); });
			    $j('#map_gbrowse')
			      .bind('ajaxSend',
				    function () {
				      $j(this).html('<p>Loading marker track..</p>');
				      $j(this).addClass('inprogress');
				    })
			      .bind('ajaxComplete',
				    function () {
				      $j(this)
					.removeClass('inprogress');
				    });
                        }
                    }
				);
				markers_loaded = true;
			
			}
			
			/*
				Function for loading marker  annotation dropdown
			*/
			
			function load_marker_annotation()
			{
						
                $('marker_annotation_loader').hide();
                
				new Ajax.Updater(
                    $('marker_annotation_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=typeMarkerAnnotation'+ '&mkan=' + markers_annotation_str ,
					{ 
                        onComplete: function() {
                        		
                            $('marker_annotation_loader').show();
                            
                        }
                    }
				); 
				
				
			}
			
			/*
				Function for loading marker  annotation comments dropdown
			*/
			
			function load_annotation_comments()
			{
						
                $('annotation_comments_loader').hide();
                
				new Ajax.Updater(
                    $('annotation_comments_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=typeAnnotationComments'+ '&anncom=' + annotation_str ,
					{ 
                        onComplete: function() {
                        		
                            $('annotation_comments_loader').show();
                            
                        }
                    }
				); 
				
				
			}
			

      </script>		
	
                <?php
                $sql = "SELECT count(*) from mapset";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_array($res);
                $height = $row[0] + 1 + 0.3*$row[0];
                ?>
	
		<div style=" float: left; margin-bottom: 1.5em;">
		<table>
				<tr>
					<th>MapSet Name</th>
					<th>Map Type</th>
					<th>Map Unit</th>
					<th>Comments</th>
					
					
				</tr>
				<tr>
					<td>
						<select name="mapsetnames" size="10" style="height: <?php echo $height ?>em;" onchange="javascript: update_mapset(this.value)">
				<?php

        // Select Mapset Name for the drop down menu
        $sql = "SELECT mapset_name FROM mapset ORDER BY mapset_name DESC";

        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($res))
        {
            ?>
            <option value="<?php echo $row['mapset_name'] ?>"><?php echo $row['mapset_name'] ?></option>
            <?php
        }
		?>
						</select>
					</td>
		
	
			<td>
						<select disabled name="MapType" size="10" style="height: <?php echo $height ?>em;" >
		<?php

		
		$sql = "SELECT map_type FROM mapset ORDER BY mapset_name DESC";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
				<option value="<?php echo $row['map_type'] ?>"><?php echo $row['map_type'] ?></option>
			<?php
		}
		?>
						</select>
					</td>
					
			<td>
						<select disabled name="MapUnit" size="10" style="height: <?php echo $height ?>em;width: 6em" >
		<?php

		
		
		$sql = "SELECT map_unit FROM mapset ORDER BY mapset_name DESC";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
				<option value="<?php echo $row['map_unit'] ?>"><?php echo $row['map_unit'] ?></option>
			<?php
		}
		?>
						</select>
					</td>
					

						
			<td>
						<select name="comments" size="10" style="height: <?php echo $height ?>em;width: 28em" onchange="javascript: display_comments(this.value)">
		<?php

		
		$sql = "SELECT comments FROM mapset ORDER BY mapset_name DESC";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
				<option value="<?php echo $row['comments'] ?>"><?php echo $row['comments'] ?></option>
			<?php
		}
		?>
						</select>
					</td>
		
			</tr>
			</table>
			</div>
			
			<div id="maps_loader" style="float: left; margin-bottom: 1.5em;"></div>
			<div id="markers_loader" style="float: left; margin-bottom: 1.5em;"></div>
			<div id="marker_annotation_loader" style="float: left; margin-bottom: 1.5em;"></div>
			<div id="annotation_comments_loader" style="float: left; margin-bottom: 1.5em;"></div>
			
	<?php
			}
			
	private function type_Maps()
	{
		$mapset_query = $_GET['mset'];
                $sql = "SELECT count(*) from mapset";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_array($res);
                $height = $row[0] + 1 + 0.3*$row[0];
                if ($height < 14) {
                  $height = 14;
                }
		
?>


<div>

<table>
	
	<tr><th>Maps</th></tr>
	<tr><td>
		<select name="mapsdetails" size="10" style="height: <?php echo $height ?>em" onchange="javascript: update_maps(this.value)">
<?php
	/* Query for fetching Map Names based on user selected mapset name */
		$sql = "SELECT m.map_name FROM map m, mapset ms where mapset_name='".$mapset_query."' and m.mapset_uid = ms.mapset_uid";

		

			$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
			<!-- Display Map names-->		
				<option value="<?php echo $row['map_name'] ?>"><?php echo $row['map_name'] ?></option>
			<?php
		}
		?>
	
		</select>
	</td></tr>
</table>
</div>
<?php
}

private function type_Markers()
	{
		$maps_query = $_GET['mp']; 
		
		/* For debugging
			$firephp = FirePHP::getInstance(true);
			$firephp->log($maps_query,"maps_query");
		*/
?>
<h2>Map</h2>
 <div id="map_gbrowse"></div>
 <table style="table-layout:fixed; width: 510px">
	<tr>
   <th style="width: 25px;"class="marker">&nbsp;&nbsp;Info</th>
	<th style="width: 125px;" class="marker">Marker</th>
	<th style="width: 50px;" class="marker">Chromo- some </th>
	<th style="width: 50px;" class="marker" >Start </th>
	<th style="width: 50px;" class="marker" >End </th>
	<th style="width: 100px; border-right: 0px" class="marker">Bin </th>
	<!-- <th style="width: 130px;" class="marker">Arm </th> -->
	<th style="width: 15px; padding: 0; border: 0px" class="marker"></th>
	</tr>
	</table> 

<div style="padding: 0; height: 300px; width: 507px;  overflow: scroll;border: 1px solid #5b53a6;">
<table style="table-layout:fixed; ">	
	
<?php

	/* Query for fetching marker name, start position, end position, chromosome and arm values based on user selected map name */
		/* $sql = "SELECT mkr.marker_name, mk.start_position, mk.end_position, mk.chromosome, mk.arm  FROM map m, markers_in_maps mk, markers mkr where map_name='".$maps_query."' and m.map_uid = mk.map_uid AND mk.marker_uid = mkr.marker_uid ORDER BY mk.start_position"; */
		$sql = "SELECT mkr.marker_name, mk.start_position, mk.end_position, mk.bin_name, mk.chromosome, mk.arm  FROM map m, markers_in_maps mk, markers mkr where map_name='".$maps_query."' and m.map_uid = mk.map_uid AND mk.marker_uid = mkr.marker_uid ORDER BY mk.start_position";

			$res = mysql_query($sql) or die(mysql_error());
			
			?>
		
	
			<?php
		while ($row = mysql_fetch_assoc($res)) {
			?>
	
		<tr>
		    <td style="width: 25px;" class="marker">
		    <input type="radio" name="btn1" value="<?php echo $row['marker_name'] ?>" onclick="javascript: update_markers_annotations(this.value)" /> 
		    </td>
		    <!-- Display Marker name, start position, chromosome, arm-->		
		    <td style="width: 125px;" class="marker"><?php echo $row['marker_name'] ?> </td>
		    <td style="width: 50px;" class="marker"> <?php echo $row['chromosome'] ?>   </td>
		    <td style="width: 50px;" class="marker"> <?php echo $row['start_position'] ?> </td>
		    <td style="width: 50px;" class="marker"> <?php echo $row['end_position'] ?> </td>
		    <td style="width: 100px;" class="marker"> <?php echo $row['bin_name'] ?> </td>
		    <!-- <td style="width: 160px;" class="marker"> <?php echo $row['arm'] ?>	</td> -->
		    </tr>
		    <?php
		}
		?>
</table>
</div>

<!-- Button for exporting map details to excel sheet-->		
<div align="left">
<input type="button" value="Download Map Data (XLS)" onclick="javascript:load_excel()" />
</div>


<?php
}

private function type_Marker_Annotation()
{
		$mark_ann_query = $_GET['mkan']; 
		
		/* For debugging
			$firephp = FirePHP::getInstance(true);
			$firephp->log($mark_ann_query,"mark_ann_query");
		*/
		
		$sql = "SELECT mat.name_annotation FROM  markers m, marker_annotations ma,  marker_annotation_types mat
 								where m.marker_name = '".$mark_ann_query."' AND
								m.marker_uid = ma.marker_uid AND
								ma.marker_annotation_type_uid = mat.marker_annotation_type_uid";

	
			$res = mysql_query($sql) or die(mysql_error());
			
	?>
	<div>

<table >
<tr><h2>Marker Annotations</h2></tr>
	<tr> <h3> Marker Selected: <?php echo $mark_ann_query; ?> </h3> </tr>
	<?php
			if (mysql_num_rows($res) >= 1)
			{
?>

<tr><th> Dataset</th><th>Entry</th></tr>

	<tr>
        <td>
		  <select name="markerannotation" size="10" style="height: 12em;width: 16em" onchange="javascript: annotation_comments(this.value)"> 
<?php
	/* Query for fetching annotation name based on user selected marker name */
		$sql = "SELECT mat.name_annotation FROM  markers m, marker_annotations ma,  marker_annotation_types mat
 								where m.marker_name = '".$mark_ann_query."' AND
								m.marker_uid = ma.marker_uid AND
								ma.marker_annotation_type_uid = mat.marker_annotation_type_uid AND
                                                                mat.linkout_string_for_annotation IS NOT NULL";
   	        $res = mysql_query($sql) or die(mysql_error());

		/* Display Annotation Names that have linkouts. */
		while ($row = mysql_fetch_assoc($res)) {
		  ?>	
			<option value="<?php echo $row['name_annotation'] ?>"><?php echo $row['name_annotation'] ?></option>
		    <?php 
		    }
		  ?>
		</select> 
	</td>

		    <td>
		    <select name="markerannotationvalue" size="10" style="height: 12em;width: 22em;text-decoration: underline;color:blue" onchange="javascript: link_for_value(this.value)" > 
<?php

	/* Query for fetching annotation value, annotation linkout string based on user selected marker name */
	
		$sql = "SELECT ma.value, mat.linkout_string_for_annotation FROM  markers m, marker_annotations ma,  marker_annotation_types mat
 								where m.marker_name = '".$mark_ann_query."' AND
								m.marker_uid = ma.marker_uid AND
								ma.marker_annotation_type_uid = mat.marker_annotation_type_uid AND
                                                                mat.linkout_string_for_annotation IS NOT NULL";
			$res = mysql_query($sql) or die(mysql_error());
					
			/* Display Annotation Value with active link */
		while ($row = mysql_fetch_assoc($res)) {
			$reg_pattern = "XXXX";
			$replace_string = $row['value'];
			$source_string = $row['linkout_string_for_annotation'];
			$linkString = ereg_replace($reg_pattern,$replace_string,$source_string);
		?>	
			<option value="<?php echo $linkString ?>"><?php echo $row['value'] ?></option>
			<?php
			}
?>
		</select> 
	</td>
	</tr>

	<?php
	}
	else
	{
	?>
	<p style="font-weight: bold;"> No Marker Annotation Data Available for the marker name selected </p>
	<?php
	}
	?>
</table>
 
<!-- Show annotation values that do not have links. -->
<?php
		$sql = "SELECT mat.name_annotation, ma.value FROM  markers m, marker_annotations ma,  marker_annotation_types mat
 								where m.marker_name = '".$mark_ann_query."' AND
								m.marker_uid = ma.marker_uid AND
								ma.marker_annotation_type_uid = mat.marker_annotation_type_uid AND
                                                                mat.linkout_string_for_annotation IS NULL";
			$res = mysql_query($sql) or die(mysql_error());
		  while ($row = mysql_fetch_assoc($res)) {
		    echo $row['name_annotation'].": ".$row['value']."<br>";
		      } 
?>

</div>


<?php

}
private function type_Annotation_Comments()
	{
		$comment_query = $_GET['anncom']; 
		
		/* For debugging
			$firephp = FirePHP::getInstance(true);
			$firephp->log($comment_query,"comment_query");
		*/
?>


<div>

<table>
	<tr> <h2>Annotation Comments </h2>
	<br/><br/><br/>
	<tr><th>Comments</th></tr>
	<tr><td>
		  <select name="markerannotationcomments" size="10" style="height: 12em;width: 26em" onchange="javascript: display_comments(this.value)" > 
<?php

		/* Query for fetching annotation comments based on user selected annotation name */
		
		$sql = "SELECT comments FROM   marker_annotation_types 	where name_annotation = '".$comment_query."' ";

	
			$res = mysql_query($sql) or die(mysql_error());
					
		while ($row = mysql_fetch_assoc($res)) {
		
		?>	
			<!-- Display Annotation Comments-->		
			
			<option value="<?php echo $row['comments'] ?>"><?php echo $row['comments'] ?></option>
			<?php
			
			}
			
			?>
	
		</select> 
	</td></tr>
</table>
</div>


<?php
}
private function type_Marker_Excel()
	{
require_once 'Spreadsheet/Excel/Writer.php';
	
		$excel_markername = $_GET['mxls1'];
		$excel_mapname = $_GET['mxls2'];  
		
/* For debugging
			$firephp = FirePHP::getInstance(true);
			$firephp->log($excel_markername,"excel_markername");
			$firephp->log($excel_mapname,"excel_mapname");
		*/




$workbook = new Spreadsheet_Excel_Writer();
$format_header =& $workbook->addFormat();
$format_header->setBold();
$format_header->setAlign('center');
$format_header->setColor('red');
//$format_header->setBgColor('blue');

//$format_header->setItalic();

$worksheet =& $workbook->addWorksheet();
$worksheet->write(0, 0, "marker_name", $format_header);
$worksheet->write(0, 1, "start_position", $format_header);
$worksheet->write(0, 2, "end_position", $format_header);
$worksheet->write(0, 3, "chromosome", $format_header);
$worksheet->write(0, 4, "arm", $format_header);
$worksheet->write(0, 5, "HARVEST_U32_Link", $format_header);
$worksheet->write(0, 6, "HARVEST_U35_Link", $format_header);
$worksheet->write(0, 7, "U32_PROBE_SET_Link", $format_header);
$worksheet->write(0, 8, "U32_RICE_LOCUS_Link", $format_header);
$worksheet->write(0, 9, "U32_RICE_DESCRIPTION_Link", $format_header);
$worksheet->write(0, 10, "U35_PROBE_SET_Link", $format_header);
$worksheet->write(0, 11, "U35_RICE_LOCUS_Link", $format_header);
$worksheet->write(0, 12, "U35_RICE_DESCRIPTION_Link", $format_header);



# start by opening a query string

$fullquery="SELECT mk.marker_name, mim.start_position, mim.end_position, mim.chromosome, mim.arm 
						FROM map m, markers mk, markers_in_maps mim
						where m.map_name = '".$excel_mapname."' AND
						
						m.map_uid = mim.map_uid AND
						mim.marker_uid = mk.marker_uid
						order by mim.start_position";
						


$result=mysql_query($fullquery);

$i=1;

while($row=mysql_fetch_assoc($result)){


 /* Add formatting */
 
 $format_row =& $workbook->addFormat();
 $format_row->setAlign('center');
 $format_link =& $workbook->addFormat();
 $format_link->setAlign('center');
 $format_link->setColor('blue');

 
/* Start writing into the excel sheet */ 
		
$worksheet->write($i, 0, "$row[marker_name]",$format_row);
$worksheet->write($i, 1, "$row[start_position]",$format_row);
$worksheet->write($i, 2, "$row[end_position]",$format_row);
$worksheet->write($i, 3, "$row[chromosome]",$format_row);
$worksheet->write($i, 4, "$row[arm]",$format_row);


/* Start of Inner Query */

/* For debugging
		$firephp->log($row[marker_name],"Marker Name in Excel");
*/

$innerquery = "SELECT ma.value, mat.name_annotation as Annotation_Name, mat.linkout_string_for_annotation as Annotation_Link, mat.comments
							 from markers mk, marker_annotations ma, marker_annotation_types mat
							 where mk.marker_name = '".$row[marker_name]."' AND
							 mk.marker_uid = ma.marker_uid AND
							 ma.marker_annotation_type_uid = mat.marker_annotation_type_uid ";

$innerresult=mysql_query($innerquery);



/* start of inner while loop */

$j = $i;
$probe32Count = 1;
$probe35Count = 1;

$test1 = $j;
while($row=mysql_fetch_assoc($innerresult)){

/* replacing value in link out string */ 

$reg_pattern = "XXXX";
$replace_string = $row[value];
$source_string = $row[Annotation_Link];
$linkString = ereg_replace($reg_pattern,$replace_string,$source_string);

/* For debugging
		$firephp->log($linkString,"linkString");
*/

if ($row[Annotation_Name] == "HARVEST_U32")
{

/* Check if link exists */

if($row[Annotation_Link] != "")
{
$worksheet->writeUrl($j, 5, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 5, "$row[value]",$format_row);
}
}

if ($row[Annotation_Name] == "HARVEST_U35")
{

/* Check if link exists */

if($row[Annotation_Link] != "")
{
$worksheet->writeUrl($j, 6, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 6, "$row[value]",$format_row);
}

}

if ($row[Annotation_Name] == "U32_PROBE_SET")
{

/* Check if link exists */
if ($probe32Count != 1)
{
$j = $j + 1;
}
if($row[Annotation_Link] != "")
{
$worksheet->writeUrl($j, 7, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 7, "$row[value]",$format_row);
}
$probe32Count = $probe32Count + 1;
$jcount = $j;
}

if ($row[Annotation_Name] == "U32_RICE_LOCUS")
{

$j = $test1;


/* Check if link exists */

if($row[Annotation_Link] != "")
{
$worksheet->writeUrl($j, 8, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 8, "$row[value]",$format_row);
}

}

if ($row[Annotation_Name] == "U32_RICE_DESCRIPTION")
{

$j = $test1;

/* Check if link exists */

if($row[Annotation_Link] != "")
{
$worksheet->writeUrl($j, 9, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 9, "$row[value]",$format_row);
}

}

if ($row[Annotation_Name] == "U35_PROBE_SET")
{
if ($probe35count == 1)
{
$j = $test1;
}
/* Check if link exists */

if ($probe35Count != 1)
{
$j = $j + 1;
}


if($row[Annotation_Link] != "")
{


$worksheet->writeUrl($j, 10, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 10, "$row[value]",$format_row);
}

$probe35Count = $probe35Count + 1;
$jcount1 = $j;


}

if ($row[Annotation_Name] == "U35_RICE_LOCUS")
{
$j = $test1;

/* Check if link exists */

if($row[Annotation_Link] != "")
{
$worksheet->writeUrl($j, 11, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 11, "$row[value]",$format_row);
}

}

if ($row[Annotation_Name] == "U35_RICE_DESCRIPTION")
{

$j = $test1;

/* Check if link exists */

if($row[Annotation_Link] != "")
{
$worksheet->writeUrl($j, 12, "$linkString", "$row[value]",$format_link);
}
else{
$worksheet->write($j, 12, "$row[value]",$format_row);
}

}
 
 if ( ($probe35Count == 1) AND ($probe32Count == 1) )
 {
 
 $i = $j;
 }

/* For debugging
		$firephp->log($jcount,"jcount");
		$firephp->log($jcount1,"jcount1");
*/

 else 
 {
if ($jcount >= $jcount1)
 { 
 $i = $jcount ;
 }
 if ($jcount1 >= $jcount)
 {
 $i = $jcount1 ;
 }
 }

 
 }
 
 /* end of inner while loop */
 
$i++;
/* For debugging
		$firephp->log($i,"i");
*/

}

/* Sending it to the excel sheet*/

$workbook->send('Maps_Details.xls');
$workbook->close();
}
}/* end of class */
