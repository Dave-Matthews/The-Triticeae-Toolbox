<?php
/*
 * Created on Feb 13, 2008
 *
 * @author Gavin Monroe
 */
require_once 'gmapconfig.php';
require_once 'config/config.php';
require_once 'gmapdata.php';
require_once 'thickline.php';

error_reporting(E_ALL);

$data =& new GMapData();
$link = mysql_connect("lab.bcb.iastate.edu", "yhames04", "gdcb07") or die (mysql_error());
mysql_select_db("sandbox_yhames04");
$sql = "SELECT markers.marker_uid, markers.marker_name, markers_in_maps.start_position " .
		"FROM markers, markers_in_maps " .
		"WHERE map_uid = '1' " .
			"AND markers.marker_uid = markers_in_maps.marker_uid " .
		"ORDER BY markers_in_maps.start_position ASC";
$res = mysql_query($sql, $link) or die(mysql_error());
while ($row = mysql_fetch_assoc($res))
{
	$data->addDataPoint(new GMapDataPoint($row['marker_uid'], $row['start_position'], $row['marker_name']));
}
$map =& new Gmap($cfg, $data);
$map->display();

/*// test data
$data =& new GMapData();
$data->addDataPoint(new GMapDataPoint(1, 1.0, "Data Point 1"));
$data->addDataPoint(new GMapDataPoint(2, 1.25, "Data Point 2"));
$data->addDataPoint(new GMapDataPoint(3, 1.5, "Data Point 3"));
$data->addDataPoint(new GMapDataPoint(4, 1.75, "Data Point 4"));
$data->addDataPoint(new GMapDataPoint(5, 2.0, "Data Point 5"));
$data->addDataPoint(new GMapDataPoint(6, 2.25, "Data Point 6"));
$data->addDataPoint(new GMapDataPoint(7, 2.5, "Data Point 7"));
$data->addDataPoint(new GMapDataPoint(8, 2.75, "Data Point 8"));
$data->addDataPoint(new GMapDataPoint(9, 3.0, "Data Point 9"));
$data->addDataPoint(new GMapDataPoint(10, 3.25, "Data Point 10"));
$map =& new Gmap($cfg, $data);
$map->display();*/


class GMap
{
	/**
	 * A collection of data points
	 * @var GMapData
	 */
	private $data;

	/**
	 * The current configuration
	 * @var GMapConfig
	 */
	private $cfg;

	/**
	 * The height of the image
	 * @var integer
	 */
	private $imageHeight;

	/**
	 * The image
	 * @var resource
	 */
	private $image;

	/**
	 * Constructs a new GMap
	 */
	public function __construct(GMapConfig $cfg, GMapData $data)
	{
		$this->cfg = $cfg;
		$this->data = $data	;
	}

	/**
	 * Calculates the height of the image
	 * @return integer the height of the image
	 */
	private function calcImageHeight()
	{
		$this->imageHeight = 2 * $this->cfg->getCanvasPadding();
		//$this->imageHeight += $this->data->size() * $this->cfg->getLabelHeight();
		$this->imageHeight += ceil($this->data->size() / 2) * $this->cfg->getLabelHeight();
		//$this->imageHeight += ($this->data->size() - 1) * $this->cfg->getLabelPadding();
		$this->imageHeight += ceil(($this->data->size() - 1) / 2) * $this->cfg->getLabelPadding();
		return $this->imageHeight;
	}

	private function drawImage()
	{
		$image = imagecreatetruecolor($this->cfg->getImageWidth(), $this->calcImageHeight());
		imagesavealpha($image, true);
		imageantialias($image, true);

		$transColor = imagecolorallocatealpha($image, 255, 255, 255, 127);
		imagefill($image, 0, 0, $transColor);

		$axisColor = imagecolorallocate($image, $this->cfg->getAxisColor('r'), $this->cfg->getAxisColor('g'), $this->cfg->getAxisColor('b'));

		// draw the axis
		imageline(
		//imageSmoothAlphaLine(
			$image,
			//$this->cfg->getCanvasPadding() + $this->cfg->getAxisPosL(),
			$this->cfg->getImageWidth() / 2,
			$this->cfg->getCanvasPadding(),
			//$this->cfg->getCanvasPadding() + $this->cfg->getAxisPosL(),
			$this->cfg->getImageWidth() / 2,
			$this->calcImageHeight() - $this->cfg->getCanvasPadding(),
			//0,
			//0,
			//0,
			//0
			$axisColor
		);

		// draw the connecting lines
		$count = 0;
		$alternator = TRUE;
		foreach ($this->data->getDataPoints() as $dp)
		{
			$x1 = $this->cfg->getImageWidth() / 2;
			$y1 = $this->cfg->getCanvasPadding() + $this->scaleToAxis($dp->getValue());
			$x2 = null;
			$y2 = $this->cfg->getCanvasPadding() + floor($count/2) * ($this->cfg->getLabelHeight() + $this->cfg->getLabelPadding()) + ($this->cfg->getLabelHeight() / 2);
			if ($alternator)
			{
				$x2 = $this->cfg->getCanvasPadding() + $this->cfg->getLabelWidth();
				$alternator = FALSE;
			}
			else
			{
				$x2 = $this->cfg->getImageWidth() - $this->cfg->getCanvasPadding() - $this->cfg->getLabelWidth();
				$alternator = TRUE;
			}
			//imageSmoothAlphaLine($image, $x1, $y1, $x2, $y2, 0, 0, 0, 0);
			imageline($image, $x1, $y1, $x2, $y2, $axisColor);
			$dp->lx1 = $x1;
			$dp->ly1 = $y1;
			$dp->lx2 = $x2;
			$dp->ly2 = $y2;
			$dp->lrelx1 = $x1 - min($x1, $x2);
			$dp->lrely1 = $y1 - min($y1, $y2);
			$dp->lrelx2 = $x2 - min($x1, $x2);
			$dp->lrely2 = $y2 - min($y1, $y2);
			$dp->lwidth = abs($x1 - $x2);
			$dp->lheight = abs($y1 - $y2);

			$lineImg = imagecreatetruecolor($dp->lwidth, $dp->lheight+3);
			imagesavealpha($lineImg, true);
			imageantialias($lineImg, true);
			$transColor2 = imagecolorallocatealpha($lineImg, 255, 255, 255, 127);
			imagefill($lineImg, 0, 0, $transColor2);
			unset($transColor2);
			$lineColor = imagecolorallocate($lineImg, 0xFF, 0x00, 0x00);
			imageline($lineImg, $dp->lrelx1, $dp->lrely1-1, $dp->lrelx2, $dp->lrely2-1, $lineColor);
			imageline($lineImg, $dp->lrelx1, $dp->lrely1+1, $dp->lrelx2, $dp->lrely2+1, $lineColor);
			imageline($lineImg, $dp->lrelx1, $dp->lrely1, $dp->lrelx2, $dp->lrely2, $lineColor);
			//@imageSmoothAlphaLine($lineImg, $dp->lrelx1, $dp->lrely1-1, $dp->lrelx2, $dp->lrely2-1, 255, 0, 0, 0);
			//@imageSmoothAlphaLine($lineImg, $dp->lrelx1, $dp->lrely1+1, $dp->lrelx2, $dp->lrely2+1, 255, 0, 0, 0);
			//@imageSmoothAlphaLine($lineImg, $dp->lrelx1, $dp->lrely1, $dp->lrelx2, $dp->lrely2, 255, 0, 0, 0);
			unset($lineColor);
			imagepng($lineImg, "gmap_line_".$dp->getId().".png");
			imagedestroy($lineImg);
			unset($lineImg);

			$count++;
		}
		imagepng($image, "gmap.png");
		imagedestroy($image);
		unset($image);
	}

	/**
	 * Maps a value onto the axis
	 * @param float $val the value to map
	 * @return float the mapped value
	 */
	private function scaleToAxis($val)
	{
		if ($val < $this->data->minVal() || $val > $this->data->maxVal())
			trigger_error('Value is out of range', E_USER_ERROR);
		$axisHeight = $this->calcImageHeight() - 2 * $this->cfg->getCanvasPadding();
		$dataRange = $this->data->maxVal() - $this->data->minVal();
		if ($dataRange == 0)
			return 0;
		$ratio = ($val - $this->data->minVal()) / $dataRange;
		return $axisHeight * $ratio;
	}

	public function display()
	{
		$this->drawImage();

		$cssLabelHeight = $this->cfg->getLabelHeight() - 2; // -2 due to 1px border
		$cssLabelWidth = $this->cfg->getLabelWidth() - 2; // -2 due to 1px border
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<style>
*{margin:0;padding:0;}
body{background: #fff url(images/grad.gif) repeat-x 0 -300px;}

div.gmapdatapoint
{
	height: <?php echo $cssLabelHeight ?>px;
	width: <?php echo $cssLabelWidth ?>px;
	border: 1px solid #aaf;
}
div.gmapdatapoint span
{
	display: block;
	height: <?php echo $cssLabelHeight ?>px;
	width: <?php echo $cssLabelWidth ?>px;
	font-size: <?php echo $this->cfg->getLabelTextSize() ?>px;
	font-family: arial, helvetica, sans-serif;
	text-align: center;
}
div.gmapdatapoint a
{
	display: block;
	height: <?php echo $cssLabelHeight ?>px;
	width: <?php echo $cssLabelWidth ?>px;
	text-decoration: none;
	color: #000;
	background: #fff;
}
/*div.gmapdatapointc a{background: #eef;}*/

div.axisLabel{
	font-size: <?php echo $this->cfg->getLabelTextSize() ?>px;
	font-family: arial, helvetica, sans-serif;
	margin-top: -<?php echo $this->cfg->getLabelTextSize() / 2?>px;
	background: #fff;
}


</style>
<script type="text/javascript" src="js/prototype.js"></script>
<script language="JavaScript1.2">
function disabletextselect(i){
return false
}
function renabletextselect(){
return true
}
//if IE4+
document.onselectstart=new Function ("return false")
//if NS6+
if (window.sidebar){
document.onmousedown=disabletextselect
document.onclick=renabletextselect
}
</script>
<script type="text/javascript">
var selectedLabels = '';
function toggle(id){
	if (selectedLabels.include(' dp' + id)){
		selectedLabels = selectedLabels.sub(' dp' + id, '');
		$('dp' + id + '_a').setStyle({backgroundColor: '#fff'});
	}
	else{
		$('dp' + id + '_a').setStyle({backgroundColor: '#ccf'});
		selectedLabels += ' dp' + id;
	}
}
function showArrow(x, y){
	$('arrow').show();
	$('arrow').setStyle({top: y + 'px', left: (x + 1) + 'px'});
}
function hideArrow(){
	$('arrow').hide();
}

var selectBoxX1 = 0;
var selectBoxY1 = 0;
var selectBoxX2 = 0;
var selectBoxY2 = 0;

var shortClick = true;
var selectionMode = false;

function mouseDown(event)
{
	selectBoxX1 = event.pointerX(event);
	selectBoxY1 = event.pointerY(event);
	shortClick = false;
	setTimeout('selectionMode = true', 500);
}
function mouseUp(event){
	shortClick = true;
	if (selectionMode){
		if (($('selectBox').visible())){
			$('selectBox').hide();
			dataPoints.each(function(dataPoint){
				id = dataPoint.identify();
				if (selectBoxX2 < selectBoxX1){
					temp = selectBoxX2;
					selectBoxX2 = selectBoxX1;
					selectBoxX1 = temp;
				}
				if (selectBoxY2 < selectBoxY1){
					temp = selectBoxY2;
					selectBoxY2 = selectBoxY1;
					selectBoxY1 = temp;
				}
				if (selectionHitTest(dataPoint)) {
					if (Event.isLeftClick(event)){
						if (!selectedLabels.include(' ' + id))
							selectedLabels += ' ' + id;
						$(id+'_a').setStyle({backgroundColor: '#ccf'});
					}else{
						//if (!selectedLabels.include(' ' + id))
						selectedLabels = selectedLabels.sub(' ' + id, '');
						$(id+'_a').setStyle({backgroundColor: '#fff'});
					}
				}
			});
		}
		selectionMode = false;
	}
}

function selectionHitTest(obj2)
{
	obj2X1 = obj2.viewportOffset().left + document.viewport.getScrollOffsets().left;
	obj2Y1 = obj2.viewportOffset().top + document.viewport.getScrollOffsets().top;
	obj2X2 = obj2X1 + obj2.getDimensions().width;
	obj2Y2 = obj2Y1 + obj2.getDimensions().height;
	if ((selectBoxX1 <= obj2X1 && obj2X1 <= selectBoxX2) ||
		(selectBoxX1 <= obj2X2 && obj2X2 <= selectBoxX2)){
		if ((selectBoxY1 <= obj2Y1 && obj2Y1 <= selectBoxY2) ||
			(selectBoxY1 <= obj2Y2 && obj2Y2 <= selectBoxY2)){
			return true;
		}
	}
	return false;
}

function mouseMove(event){
	if (selectionMode && !shortClick){

		selectBoxX2 = event.pointerX(event);
		selectBoxY2 = event.pointerY(event);
		cssLeft = 0;
		cssTop = 0;
		cssWidth = 0;
		cssHeight = 0;
		if (selectBoxX1 < selectBoxX2){
			cssLeft = selectBoxX1;
			cssWidth = selectBoxX2 - selectBoxX1;
		}else{
			cssLeft = selectBoxX2;
			cssWidth = selectBoxX1 - selectBoxX2;
		}
		if (selectBoxY1 < selectBoxY2){
			cssTop = selectBoxY1;
			cssHeight = selectBoxY2 - selectBoxY1;
		}else{
			cssTop = selectBoxY2;
			cssHeight = selectBoxY1 - selectBoxY2;
		}
		if (!$('selectBox').visible()){
			$('selectBox').show();
		}
		$('selectBox').setStyle({
			left: cssLeft + 'px',
			top: cssTop + 'px',
			width: cssWidth + 'px',
			height: cssHeight + 'px'
		});
	}
}

var dataPoints;
Event.observe(window, 'load', function(){
	$('selectBox').setOpacity(0.2);
	dataPoints = $$('div.gmapdatapoint');
});
Event.observe(document, 'mousedown', mouseDown);
Event.observe(document, 'mouseup', mouseUp);
Event.observe(document, 'mousemove', mouseMove);

function isDefined( variable)
{
    return (typeof(window[variable]) == "undefined")?  false: true;
}

function highlightLine(id)
{
	$('lineimg'+id).show();
}
function unhighlightLine(id)
{
	$('lineimg'+id).hide();
}

</script>
</head>
<body oncontextmenu="return false;">
<div style="background-image: url('gmap.png'); width: <?php echo $this->cfg->getImageWidth() ?>px; height: <?php echo $this->imageHeight ?>px;"></div>
<?php
		$start = ceil($this->data->minVal() / $this->cfg->getAxisIncrement()) * $this->cfg->getAxisIncrement();
		for ($i = $start; $i < $this->data->maxVal(); $i += $this->cfg->getAxisIncrement()):
?>
<div class="axisLabel" style="position: absolute; left: <?php echo $this->cfg->getImageWidth() / 2 + 3 ?>px; top: <?php echo $this->cfg->getCanvasPadding() + $this->scaleToAxis($i) ?>px;">
	<?php echo $i ?>
</div>
<?php
		endfor;
?>





<?php
		$count = 0;
		$alternator = TRUE;
		foreach ($this->data->getDataPoints() as $dp)
		{
			if ($alternator)
			{
				$positionTop = $this->cfg->getCanvasPadding() + floor($count/2) * ($this->cfg->getLabelHeight() + $this->cfg->getLabelPadding());
				$positionLeft = $this->cfg->getCanvasPadding(); //$this->cfg->getCanvasPadding() + $this->cfg->getAxisPosL() + $this->cfg->getLabelDist();
				$alternator = FALSE;
			}
			else
			{
				$positionTop = $this->cfg->getCanvasPadding() + floor($count/2) * ($this->cfg->getLabelHeight() + $this->cfg->getLabelPadding());
				$positionLeft = $this->cfg->getImageWidth() - $this->cfg->getCanvasPadding() - $this->cfg->getLabelWidth(); //$this->cfg->getCanvasPadding() + $this->cfg->getAxisPosL() + $this->cfg->getLabelDist();
				$alternator = TRUE;
			}



?>
<div id="lineimg<?php echo $dp->getId() ?>" style="display: none; background: url('gmap_line_<?php echo $dp->getId() ?>.png') no-repeat; position: absolute; left: <?php echo min($dp->lx1, $dp->lx2) ?>px; top: <?php echo min($dp->ly1, $dp->ly2) ?>px; width: <?php echo $dp->lwidth ?>px; height: <?php echo $dp->lheight + 3 ?>px;"></div>
<div id="dp<?php echo $dp->getId() ?>" class="gmapdatapoint" style="position: absolute; top: <?php echo $positionTop ?>px; left: <?php echo $positionLeft ?>px;">
	<span>
		<a
			href="javascript: toggle('<?php echo $dp->getId() ?>');"
			id="dp<?php echo $dp->getId() ?>_a"
			onmouseover="javascript: highlightLine('<?php echo $dp->getId() ?>'); showArrow(<?php echo $this->cfg->getImageWidth() / 2 ?>, <?php echo $this->cfg->getCanvasPadding() + $this->scaleToAxis($dp->getValue()) - 5 ?>);"
			onmouseout="javascript: unhighlightLine('<?php echo $dp->getId() ?>'); hideArrow();"
			title="<?php echo $dp->getValue() ?>"
		>
			<?php echo $dp->getLabel() ?>
		</a>
	</span>
</div>
<?php
			$count++;
		}
?>
<div id="selectBox" style="background: #00f; position: absolute; display:none; border: 1px solid black;"></div>
<div id="arrow" style="display:none; position: absolute; background: #fff url('images/arrow.png'); width: 11px; height: 11px;"></div><body>
</html>
<?php
	}//test
}