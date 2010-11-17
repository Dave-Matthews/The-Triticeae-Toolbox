<?php
  // generate a gbrowse database config for tht data
require_once('config.php');
require_once($config['root_dir'] . 'includes/bootstrap.inc');
connect();

$dbpath = $argv[1];
echo <<<EOD
[GENERAL]
description   = THT
db_adaptor    = Bio::DB::SeqFeature::Store
#db_args       = -adaptor memory
#                -dir    '{$dbpath}'
db_args       = -adaptor DBI::mysql
                -dsn     gtht
				-user 	tht
				-password	wheat_2008 

#units         = cM
#unit_divider  = 1000

plugins = 

# Web site configuration info
gbrowse root = gbrowse
stylesheet   = gbrowse.css
buttons      = images/buttons
js           = js
tmpimages    = tmp

initial landmark = chr1H

# advanced features
balloon tips    = 1
drag and drop = 1

# one hour
cache time    = 1

# where to link to when user clicks in detailed view
#link          = AUTO

# what image widths to offer
image widths  = 450 640 800 1024

# color of the selection rectangle
hilite fill    = beige
hilite outline = red

# default width of detailed view (pixels)
default width = 800
default features = remark

# max and default segment sizes for detailed view
max segment     = 500000
default segment = 50000

# zoom levels
zoom levels    = 50 100 200 1000 2000 5000 10000 20000 40000 100000 200000 500000 1000000

# whether to show the sources popup menu (0=false, 1=true; defaults to true)
show sources   = 1

# colors of the overview, detailed map and key
overview bgcolor = lightgrey
detailed bgcolor = lightgoldenrodyellow
key bgcolor      = beige

# examples to show in the introduction
examples = chr1H
       chr2H

# "automatic" classes to try when an unqualified identifier is given
automatic classes = Symbol Gene Clone

### HTML TO INSERT AT VARIOUS STRATEGIC LOCATIONS ###
# inside the <head></head> section
head = 

# at the top...
header =

# a footer
footer = <hr>
	<table width="100%">
	<TR>
	<TD align="LEFT" class="databody">
	For the source code for this browser, see the <a href="http://www.gmod.org">
	Generic Model Organism Database Project.</a>  For other questions, send
	mail to <a href="mailto:lstein@cshl.org">lstein@cshl.org</a>.
	</TD>
	</TR>
	</table>
	<hr>

# Various places where you can insert your own HTML -- see configuration docs
html1 = <iframe name='invisibleiframe' style='display:none'></iframe>
html2 = 
html3 = 
html4 = 
html5 = 
html6 = 

# Advanced feature: custom balloons
custom balloons = [balloon]
                  delayTime = 500

                  [balloon500]
	          maxWidth  = 500
                  delayTime = 50


# Default glyph settings
[TRACK DEFAULTS]
glyph       = generic
height      = 8
bgcolor     = cyan
fgcolor     = cyan
label density = 25
bump density  = 100

### TRACK CONFIGURATION ####
# the remainder of the sections configure individual tracks
[Lines:DETAILS]
URL = \$value

[Linkout:DETAILS]
URL = \$value

EOD;

$sql = "select mapset_name from mapset order by mapset_name";
$sqlr = mysql_query($sql) or die(mysql_error());

while ($row = mysql_fetch_assoc($sqlr)) {
  extract($row);
 echo <<<EOD1

[Marker $mapset_name:overview]
feature       = remark:$mapset_name
fgcolor       = sub { my \$feat = shift; my \$mt = join('', \$feat->each_tag_value('MarkerType')); return 'gray' if \$mt eq 'Historical'; return 'green' if \$mt eq 'OPA SNP Name'; return 'red' if \$mt eq 'DArT Marker'; return 'blue' if \$mt eq 'QTL'; return 'black'; }
glyph         = generic
key           = $mapset_name marker

[Marker $mapset_name]
feature       = remark:$mapset_name
fgcolor       = gray
bgcolor       = sub { my \$feat = shift; my \$mt = join('', \$feat->each_tag_value('MarkerType')); return 'gray' if \$mt eq 'Historical'; return 'green' if \$mt eq 'OPA SNP Name'; return 'red' if \$mt eq 'DArT Marker'; return 'blue' if \$mt eq 'QTL'; return 'black'; }
glyph         = dot
description   = 1
key           = Marker in $mapset_name
# default pop-up balloon
balloon hover = sub {
    my \$feat = shift;
    my \$mt = join('', \$feat->each_tag_value('MarkerType'));
    my \$linkout = join('', \$feat->each_tag_value('Linkout'));
    my \$rv = "<b>".(\$feat->name)."</b> is a marker spanning "
    .(\$feat->ref)." from ".(\$feat->start)." to ".(\$feat->end)
    ." of type <b>\$mt</b>. Click for more details.";
    return \$rv;
  }
balloon click = sub {
    my \$feat = shift;
    my \$marker_uid = join('', \$feat->each_tag_value('marker_uid'));
    my \$map = join('', \$feat->each_tag_value('Map'));
    my \$rv = "Marker ".(\$feat->name)."<br /><form method='POST' "
    ."action='/genotyping/marker_selection.php'>"
    ."<input type='hidden' name='mapname' value='".(\$map)."'></input>"
    ."<input type='hidden' name='selbyname' value='".(\$feat->name)."'></input>"
    ."<input type='submit' value='Select in THT'></form>"
    ."<br /><a href='/cgi-bin/gbrowse_details/tht?name=".(\$feat->name).";class=".(\$feat->class)
    .";ref=".(\$feat->ref).";start=".(\$feat->start).";end=".(\$feat->end).">More..</a>";
    return \$rv;
  }
EOD1;
 }
?>
