<?php

class history {

	/*
	 * These are all the types of histories that can be stored.
	 */
	private $typelist = array(
			"page",
			"query",
			"post",
			"get",
			"request"
			);

	/*
	 * This constructor creates the history object and automatically attempts to either
	 * restore the current page if it exists in history or store the current page into 
	 * history.
  	 *
	 * @param $skip - If FALSE then will store/load the current page. Otherwise it will simply
	 * 		  construct the variable
	 * @return void - this function returns nothing. It simply constructs the variable.
	 */
	public function __construct($skip=FALSE) {
		
		//Start the session if it isn't started already
		if(session_test())
			session_start();

		//Initialize the session arrays incase they aren't already
		$this->initialize();

		if($skip) {	//skip the storing process, just construct the object
			return;
		}

		//if we're reverting back to a page in history, then load variables associated with that page
		if( ($index = $this->inHistory("page", $_SERVER['PHP_SELF'])) !== FALSE ) {

			//must merge in this order so that the global overwrites the history
			$_POST = array_merge($this->loadHistoryAtPosition("post", $index), $_POST);
			$_GET = array_merge($this->loadHistoryAtPosition("get", $index), $_GET);
			$_REQUEST = array_merge($this->loadHistoryAtPosition("request", $index), $_REQUEST);

			//re-store (overwrite) the page variables incase someone resubmitted a form.
			$this->storeAtPosition("post", $_POST, $index);
			$this->storeAtPosition("get", $_GET, $index);
			$this->storeAtPosition("request", $_REQUEST, $index);
		}
		else {
			//Store all of the variables
			$this->storeLast("page", $_SERVER['PHP_SELF']);
			$this->storeLast("post", $_POST);
			$this->storeLast("get", $_GET);
			$this->storeLast("request", $_REQUEST);
		}

	}

	/*
	 * This method determines whether or not the the history element is stored in history.
	 *
	 * @param $type - the type of history to search through.
	 * @param $needle - the element to look for in the history.
	 * @return the index at which the element is found at, otherwise returns FALSE
	 */ 
	public function inHistory($type, $needle) {
		return array_search($needle, $_SESSION['history'][$type]);
	}

	/* 
	 * This method generates an ordered list of all the elements in history
	 *
	 * @param $type - the type of history to display
	 * @return void - echos its result.
	 */
	public function listHistory($type) {

		if(count($_SESSION['history'][$type]) < 1) {
			echo "<p>No record of $type history</p>";
		}
		else {
			echo "\n<ol>\n";
			foreach($_SESSION['history'][$type] as $record) {
				echo "\t<li><a href=\"$record\">". basename($record) ."</a></li>\n";
			}
			echo "</ol>\n";
		}
	
	}
	
	/*
	 * This method lists the history in a breadcrumb format (inline) with the option of
	 * specifying a delimiter.
	 *
	 * @param $type - the type of history to display
	 * @param $delim - the delimiter to separate the breadcrumbs by. Defaults to "-"
	 * @return void - echos its result.
	 */
	public function listBreadCrumbs($type, $delim="-") {

		if(count($_SESSION['history'][$type]) < 1) {
			echo "<p>No record of $type history</p>";
		}
		else {
			echo "\n<p>";
			foreach($_SESSION['history'][$type] as $record) {
				echo " $delim <a href=\"$record\">". basename($record) ."</a>";
			}
			echo " $delim </p>\n";
		}
		
	}


	/*
	 * For working with elements that currently exist in history, the next 2 methods
	 * are provided. 
	 */


	/*
	 * This method will return a history element at a given position. The position is typically
	 * found by using the method inHistory. 
	 *
	 * @param $type - the type of history to search through
	 * @param $position - the position at which the element is in history.
	 * @return the element in history or if not found, an empty array.
	 * @see inHistory();
	 */
	public function loadHistoryAtPosition($type, $position) {
		if(is_null($_SESSION['history'][$type][$position]))
			return array();
		else
			return $_SESSION['history'][$type][$position];
	}

	/*
	 * This method is used for overwriting a history element at a given position. The position
	 * is typically found by using the method inHistory.
	 *
	 * @param $type - the type of history to search through
	 * @param $position - the position at which to store the element in history.
	 * @return TRUE always
	 * @see inHistory();
	 */
	public function storeAtPosition($type, $store, $position) {
		$_SESSION['history'][$type][$position] = $store;
		return TRUE;
	}


	/*
	 * For working with elements that do not currently exist in history, the next 2 methods
	 * are provided. 
	 */

	/*
	 * This method will store a given history element at the end of the history.
	 *
	 * @param $type - the type of history to store
	 * @param $qstring - the history element to store.
	 * @return TRUE always.
	 */
	public function storeLast($type, $qstring) {
		array_push($_SESSION['history'][$type], $qstring);
		return TRUE;
	}

	/*
	 * This method will return the history element at the end of the history.
	 *
	 * @param $type - the type of history to search through.
	 * @return the last element in history.
	 */
	public function getLast($type) {
		return array_pop($_SESSION['history'][$type]);
	}

	/*
	 * @return an array containing all the types that can be stored in history.
	 */
	public function getTypes() {
		return $this->typelist;
	}


	/**
	 * Private methods
	 */

	/*
 	 * This function verifies a given type as whether or not it exists in the typelist. 
	 *
	 * @return TRUE if the type is valid, FALSE if not.
	 */
	private function verifyType($type) {
		return in_array($type, $this->typelist);
	}

	/*
	 * This method initializes the session variable where the history 
	 * resides. Since most everything is stored in an array that uses PHP 
	 * array functions, we need to ensure that arrays are initialized. 
	 *
	 * @return void - this just initializes a bunch of arrays.
	 */	
	private function initialize() {
		if(!is_array($_SESSION['history'])) {
			$_SESSION['history'] = array();
		}

		for($i=0; $i<count($this->typelist); $i++) {
			if(!is_array($_SESSION['history'][$this->typelist[$i]])) { 
				$_SESSION['history'][$this->typelist[$i]] = array();
			}
		}
	}

}


?>
