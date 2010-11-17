<?php
/*
 * Created on Feb 13, 2008
 *
 * @author Gavin Monroe
 */
require_once 'gmapdatapoint.php';

class GMapData
{
	/**
	 * An array of data points
	 * @var array
	 */
	private $dataPoints;
	/**
	 * The number of data points
	 * @var int
	 */
	private $numDataPoints;
	private $min = null;
	private $max = null;

	public function __construct()
	{
		$this->dataPoints = array();
		$this->numDataPoints = 0;
	}

	public function addDataPoint(GMapDataPoint $dp)
	{
		array_push($this->dataPoints, $dp);
		if ($this->numDataPoints == 0)
		{
			$this->min = $dp;
			$this->max = $dp;
		}
		else
		{
			if ($dp->getValue() < $this->min->getValue())
				$this->min = $dp;
			if ($dp->getValue() > $this->max->getValue())
				$this->max = $dp;
		}
		$this->numDataPoints += 1;
	}

	/**
	 * Returns the minimum value
	 * @return double the minimum value
	 */
	public function minVal()
	{
		return $this->min->getValue();
	}

	/**
	 * Returns the maximum value
	 * @return double the maximum value
	 */
	public function maxVal()
	{
		return $this->max->getValue();
	}

	/**
	 * Returns the number of data points
	 * @return int the number of data points
	 */
	public function size()
	{
		return $this->numDataPoints;
	}

	public function getDataPoints()
	{
		return $this->dataPoints;
	}

}
?>
