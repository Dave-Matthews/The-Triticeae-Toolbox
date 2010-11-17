<?php
/*
 * Created on Feb 13, 2008
 *
 * @author Gavin Monroe
 */
/**
 *
 */


define('DEFAULT_AXIS_COLOR_R', 0x00);
define('DEFAULT_AXIS_COLOR_G', 0x00);
define('DEFAULT_AXIS_COLOR_B', 0x00);

/**
 * The default distance between the axis and the left side of the image in pixels
 */
define('DEFAULT_AXIS_POS_L', 30);

/**
 * The default margin around the graph in pixels
 */
define('DEFAULT_CANVAS_PADDING', 10);
/**
 * The default width of the graph in pixels
 */
define('DEFAULT_IMAGE_WIDTH', 500);
/**
 * The default height of a data point label in pixels
 */
define('DEFAULT_LABEL_HEIGHT', 15);
/**
 * The default width of a data point in pixels
 */
define('DEFAULT_LABEL_WIDTH', 80);
/**
 * The default space between two adjacent labels in pixels
 */
define('DEFAULT_LABEL_PADDING', 5);
/**
 * The default space between the axis and labels in pixels
 */
define('DEFAULT_LABEL_DIST', 400);

/**
 * The default label text size in pixels
 */
define('DEFAULT_LABEL_TEXT_SIZE', 10);

/**
 * The default increment for labeling the axis in units
 */
define('DEFAULT_AXIS_INCREMENT', 10);



class GMapConfig
{
	/**
	 * @var integer
	 */
	private $axisColorR;
	/**
	 * @var intger
	 */
	private $axisColorG;
	/**
	 * @var intger
	 */
	private $axisColorB;
	/**
	 * @var integer
	 */
	private $axisPosL;
	/**
	 * @var intger
	 */
	private $canvasPadding;
	/**
	 * @var intger
	 */
	private $imageWidth;
	/**
	 * @var intger
	 */
	private $labelHeight;
	/**
	 * @var integer
	 */
	private $labelWidth;
	/**
	 * @var integer
	 */
	private $labelPadding;
	/**
	 * @var integer
	 */
	private $labelDist;
	/**
	 * @var integer
	 */
	private $labelTextSize;
	/**
	 * @var integer
	 */
	private $axisIncrement;


	public function __construct()
	{
		$this->setDefaults();
	}

	public function setDefaults()
	{
		$this->axisColorR =		DEFAULT_AXIS_COLOR_R;
		$this->axisColorG =		DEFAULT_AXIS_COLOR_G;
		$this->axisColorB =		DEFAULT_AXIS_COLOR_B;
		$this->axisPosL =		DEFAULT_AXIS_POS_L;
		$this->canvasPadding =	DEFAULT_CANVAS_PADDING;
		$this->imageWidth =		DEFAULT_IMAGE_WIDTH;
		$this->labelHeight =	DEFAULT_LABEL_HEIGHT;
		$this->labelWidth =		DEFAULT_LABEL_WIDTH;
		$this->labelPadding =	DEFAULT_LABEL_PADDING;
		$this->labelDist =		DEFAULT_LABEL_DIST;
		$this->labelTextSize = 	DEFAULT_LABEL_TEXT_SIZE;
		$this->axisIncrement =	DEFAULT_AXIS_INCREMENT;
	}
//
	public function getLabelHeight()
	{
		return $this->labelHeight;
	}
	public function setLabelHeight($h)
	{
		// The label height cannot be even
		if ($h % 2 == 0)
			trigger_error('The label height must be odd', E_USER_ERROR);
		$this->labelHeight = $h;
	}
//
	public function getLabelWidth()
	{
		return $this->labelWidth;
	}
	public function setLabelWidth($w)
	{
		if (is_int($w))
			$this->labelWidth = $w;
		else
			$this->labelWidth = NULL;
	}
//
	public function getCanvasPadding()
	{
		return $this->canvasPadding;
	}
	public function setCanvasPadding($p)
	{
		$this->canvasPadding = $p;
	}
//
	public function getLabelPadding()
	{
		return $this->labelPadding;
	}
	public function setLabelPadding($p)
	{
		$this->labelPadding = $p;
	}
//
	public function getImageWidth()
	{
		return $this->imageWidth;
	}
	public function setImageWidth($w)
	{
		if ($w < 2 * $this->canvasPadding)
			trigger_error('Image width is too small', E_USER_ERROR);
		return $this->imageWidth = $w;
	}
//
	public function getAxisColor($rgb)
	{
		if (strtolower($rgb) == 'r')
			return $this->axisColorR;
		if (strtolower($rgb) == 'g')
			return $this->axisColorG;
		if (strtolower($rgb) == 'b')
			return $this->axisColorB;
		else
			return NULL; // possibly trigger an error?
	}
	public function setAxisColor($r, $g, $b)
	{
		if ($r < 0 || $r > 255 || $g < 0 || $g > 255 || $b < 0 || $b >255)
			trigger_error('Invalid color', E_USER_ERROR);
		$this->axisColorR = $r;
		$this->axisColorG = $g;
		$this->axisColorB = $b;
	}
//
	public function getAxisPosL()
	{
		return $this->axisPosL;
	}
//
	public function getLabelDist()
	{
		return $this->labelDist;
	}
//
	public function getLabelTextSize()
	{
		return $this->labelTextSize;
	}
	public function setLabelTextSize($s)
	{
		if ($s < 8)
			trigger_error('Label text size is too small', E_USER_ERROR);
		return $this->labelTextSize = $s;
	}
//
	public function getAxisIncrement()
	{
		return $this->axisIncrement;
	}
	public function setAxisIncrement($i)
	{
		if ($i <= 0)
			trigger_error('Axis increment is invalid', E_USER_ERROR);
		return $this->axisIncrement = $i;
	}

}
?>
