<?php
// An exporter for genetic marker and related data into gff3 format
//
// 9/27/2010 JLee - Add check to see if we need to re-generate gff3 file,
//           if so, create a semaphore file to indicate we need to we 
//           generate the Gbrowse DB. 
//
//

session_start();
header('content-type: text/plain');
require_once('config.php');
require_once($config['root_dir'] . 'includes/bootstrap.inc');
require_once 'Date.php';
connect();

function uncM($cmvalue) {
  // function used to convert the marker coordinate in centimorgans
  // into integer coordinate in bases
  $cmbases = 1000;
  // the coordinates for gbrowse must be 1-based, or otherwise those
  // features that start at 0 won't show up in the track which is
  // going to break stuff
  return round($cmvalue * $cmbases) + 1;
}

function chr_seqid($chromosome) {
  return 'chr' . $chromosome;
}

function scaffolds_fragment() {
 $sql = "select chromosome, max(end_position) as max_end_position
from markers_in_maps group by chromosome order by chromosome";
  $sql_r = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_assoc($sql_r)) {
    extract($row);
    $id = chr_seqid($chromosome);
    $name = 'Chr' . $chromosome;
    $chr_end = unCM($max_end_position);
    $rv .= "$id\t.\tchromosome\t1\t$chr_end\t.\t.\t.\tID=$id;Name=$name\n";
  }
  return $rv;
}

function scaffolds() {
  $rv = "##gff-version 3\n##Index-subfeatures 1\n\n";
  $rv .= scaffolds_fragment();
  return $rv;
 }

function annotations() {
  $rv = "##gff-version 3\n##Index-subfeatures 1\n";
  $rv .= scaffolds_fragment();
  $sql = "select mapset_uid, mapset_name, species, map_type,
map_unit from mapset order by mapset_uid";
  $mapset_r = mysql_query($sql) or die(mysql_error());
  while ($mapset = mysql_fetch_assoc($mapset_r)) {
    extract($mapset);
    $sql = "select map_uid, map_name, map_start, map_end
from map where mapset_uid=$mapset_uid";

    $map_r = mysql_query($sql) or die(mysql_error());
    while ($map = mysql_fetch_assoc($map_r)) {
      extract($map);
      $map_start = round($map_start * $cmbases);
      $map_end   = round($map_end * $cmbases);
      $sql = "select mm.marker_uid as marker_uid,
mm.map_uid as map_uid, m.marker_name as marker_name,
mm.start_position as marker_start, mm.end_position as marker_end,
mm.chromosome as chromosome, mm.arm as arm, mt.marker_type_name,
concat_ws(';', group_concat(distinct concat(mat.name_annotation, '=',
url_encode(ma.value)) separator ';'),
group_concat(distinct concat(url_encode(mst.name), '=',
url_encode(ms.value)) separator ';')) as marker_annotation,
mat.comments as annotation_comments,
mat.linkout_string_for_annotation as linkout_string_for_annotation,
map.map_name as map_name
from markers_in_maps as mm
inner join markers as m using(marker_uid)
inner join map using(map_uid)
left join marker_annotations as ma using(marker_uid)
left join marker_annotation_types as mat
using(marker_annotation_type_uid)
inner join marker_types as mt using(marker_type_uid)
left join marker_synonyms as ms using(marker_uid)
inner join marker_synonym_types as mst using(marker_synonym_type_uid)
where mm.map_uid=$map_uid
group by marker_uid"; 

      $marker_r = mysql_query($sql) or die(mysql_error());
      while ($marker = mysql_fetch_assoc($marker_r)) {
	extract($marker);
	$seqid = chr_seqid($chromosome);
	$cmstuff = rawurlencode('Start(map)') . '=' .
	  rawurlencode("$marker_start cM") . ';' .
	  rawurlencode('End(map)') . '=' .
	  rawurlencode("$marker_end cM");
	$marker_start = uncM($marker_start);
	$marker_end   = uncM($marker_end);
	$id = rawurlencode($map_name . '_' . $marker_name);
	$name = rawurlencode($marker_name);
	$arm = rawurlencode($arm);
	$map_name = rawurlencode($map_name);
	$marker_type_name = rawurlencode($marker_type_name);
	$lineurl = rawurlencode("http://hordeumtoolbox.org/marker_lines.php?id=$marker_uid");
	$rv .= "$seqid\t$mapset_name\tremark\t$marker_start\t$marker_end\t.\t.\t.\tID=$id;Name=$name;$marker_annotation;$cmstuff;Arm=$arm;MarkerType=$marker_type_name;Map=$map_name;Lines=$lineurl;marker_uid=$marker_uid";
	if ($linkout_string_for_annotation) {
	  $linkout = str_replace('XXXX', $name,
				 $linkout_string_for_annotation);
	  $rv .= (";Linkout=" . rawurlencode($linkout));
	}
	$rv .= "\n";
      }
    }
  }
  return $rv;
}

// Get the time of the most recent change
$sql = "select updated_on from mapset order by updated_on DESC limit 1";
$sql_r = mysql_query($sql) or die(mysql_error());

$db_time = mysql_result($sql_r, 0);
//echo ($db_time . "\n");
$db_date = new Date($db_time);

// Grab today's date
$cur_date = new Date();
//echo $cur_date->getDate(). "\n";

// Calculate the date differences
$span = new Date_Span();
$span->setFromDateDiff($cur_date, $db_date);
//echo $span->toDays();

// If the date difference is under a week, generate a new gff file.
if ($span->toDays() <= 7) {
  echo annotations();
  $ourFileName = "./gbrowse-generated/regenGbrowseDB.txt";
  $ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
  fclose($ourFileHandle);
}
// else do nothing
#else { 
#  echo "No new maps.";
#}

?>
