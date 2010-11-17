<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<base href="http://lab.bcb.iastate.edu/sandbox/yhames04/" />
	<link rel="icon" href="favicon.ico" type="image/x-icon">	
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="theme/style.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="theme/chrome/chromestyleTHT.css" />
	<script type="text/javascript" src="includes/core.js"></script>
	<script type="text/javascript" src="theme/chrome/chrome.js">

	/***********************************************
	* Chrome CSS Drop Down Menu- Dynamic Drive DHTML code library (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	***********************************************/

	</script>
	<title>Home: The Hordeum Toolbox</title>
</head>

<body>
<center>
<div id="pagewrap">

<!-- Greeting -->
<h1>Welcome to <em>The Hordeum Toolbox</em> (THT)</h1>
 
<!-- Login Top Box -->
<div class="tabcontainer">

<div id="sc1" class="tabcontent">
About <em>The Hordeum Toolbox</em>.
</div>

<div id="sc2" class="tabcontent">
Search by Pedigree Related Information.
</div>

<div id="sc3" class="tabcontent">
Search by Trait Related Information.
</div>

<div id="sc4" class="tabcontent">
Search by Genotyping Related Information.
</div>

<div id="sc5" class="tabcontent">
Search by Expression Related information.
</div>

<div id="sch" class="tabcontent">
Go to the homepage.
</div>

</DIV>

<div class="chromestyle" id="chromemenu" align='center'>
<ul>
	<li><a href="http://lab.bcb.iastate.edu/sandbox/yhames04/" onMouseOver=document.getElementById("sch").style.display="block" onMouseOut=document.getElementById("sch").style.display="none">Home</a></li>
	<li><a href="#" rel="dropmenu1" >About</a></li>
        <li><a href="#" rel="dropmenu2" >Pedigree</a></li>
	<li><a href="#" rel="dropmenu3" >Traits</a></li>
	<li><a href="#" rel="dropmenu4" >Genotyping</a></li>
	<li><a href="#" rel="dropmenu5" >Expression</a></li>
</ul>
</div>

<!--1st drop down menu -->                                                   
<div id="dropmenu1" class="dropmenudiv">
<a href="">What is THT?</a>
<a href="">Contact Us</a>
</div>

<!--2nd drop down menu -->                                                
<div id="dropmenu2" class="dropmenudiv">
<a href="pedigree/show_pedigree.php">Show Line Records</a>
<a href="pedigree/pedigree_tree.php">Show Pedigree Tree</a>
<a href="pedigree/parse_pedigree.php">Parse External String-based Pedigree</a>
</div>


<!--3rd drop down menu -->                                                
<div id="dropmenu3" class="dropmenudiv">
</div>

<!--4th drop down menu -->                                                   
<div id="dropmenu4" class="dropmenudiv">
<a href="genotyping/map_display.php">Show Markers</a>
</div>

<!-- 5th drop down menu -->
<div id="dropmenu5" class="dropmenudiv">
</div>

<script type="text/javascript">

cssdropdown.startchrome("chromemenu")

</script>
