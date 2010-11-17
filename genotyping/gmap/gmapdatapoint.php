<?php
/*
 * Created on Feb 13, 2008
 *
 * @author Gavin Monroe
 */
class GMapDataPoint
{
	private $id;
	private $value;
	private $label;
	
	public $lx1;
	public $ly1;
	public $lx2;
	public $ly2;
	public $lrelx1;
	public $lrely1;
	public $lrelx2;
	public $lrely2;
	public $lwidth;
	public $lheight;

	/**
	 * Constructs a new data point
	 *
	 * @param int $id a unique identifier for this data point
	 * @param double $value a value for this data point
	 * @param string $label a label for this data point
	 */
	public function __construct($id, $value, $label)
	{
		$this->id = $id;
		$this->value = $value;
		$this->label = $label;
	}

	/**
	 * Returns the value of this data point
	 * @return double the value of this data point
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns the label of this data point
	 * @return string the label of this data point
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Returns the id of this data point
	 * @return integer the id of this data point
	 */
	public function getId()
	{
		return $this->id;
	}
	
}
?>
