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

<h1><em>The Hordeum Toolbox</em> (THT) Admin Console </h1>
 
<!-- Login Top Box -->
<div class="tabcontainer">

<div id="sc1" class="tabcontent">
Editing Pedigree Related Information.
</div>

<div id="sc2" class="tabcontent">
Editing Trait Related Information
</div>

<div id="sc3" class="tabcontent">
Editing Genotyping Related Information
</div>

<div id="sc4" class="tabcontent">
Editing Expression Related information
</div>

<div id="sc5" class="tabcontent">
Administer the Database
</div>

<div id="sch" class="tabcontent">
Go to the homepage.
</div>

</DIV>

<div class="chromestyle" id="chromemenu" align='center'>
<ul>
	<li><a href="http://lab.bcb.iastate.edu/sandbox/yhames04/login/" onMouseOver=document.getElementById("sch").style.display="block" onMouseOut=document.getElementById("sch").style.display="none">Home</a></li>
        <li><a href="#" rel="dropmenu1" >Pedigree</a></li>
	<li><a href="#" rel="dropmenu2" >Traits</a></li>
	<li><a href="#" rel="dropmenu3" >Genotyping</a></li>
	<li><a href="#" rel="dropmenu4" >Expression</a></li>
	<li><a href="#" rel="dropmenu5" >Database</a></li>
	<li><a href="login.php?logout=true"><i style="font-size:smaller;">Logout<?php echo " ".$row['name']; ?></i></li>
</ul>
</div>

<!--1st drop down menu -->                                                   
<div id="dropmenu1" class="dropmenudiv">
<a href="login/pedigreeAdd.php">Add Pedigree Information</a>
<a href="login/edit_pedigree.php">Edit Pedigree Information</a>
<a href="login/edit_line.php">Edit Line Records</a>
</div>


<!--2nd drop down menu -->                                                
<div id="dropmenu2" class="dropmenudiv">
<a href="login/traitAdd.php">Add Trait Definitions</a>
<a href="login/edit_traits.php">Edit Trait Definitions</a>
<a href="login/traitAdd.php">Input Trait Data</a>
<a href="login/traitAdd.php">Edit Trait data</a>
</div>

<!--3rd drop down menu -->                                                   
<div id="dropmenu3" class="dropmenudiv">
<a href="login/markerAdd.php">Add Marker Definitions</a>
<a href="login/snpAdd.php">Edit Marker Definitions</a>
<a href="login/snpAdd.php">Input Genotyping Data</a>
<a href="login/snpAdd.php">Edit Genotyping Data</a>
</div>

<!-- 4th drop down menu -->
<div id="dropmenu4" class="dropmenudiv">
<a href="login/index.php">Add Expression Data</a>
<a href="login/index.php">Edit Expression Data</a>
</div>

<!-- 5th drop down menu -->
<div id="dropmenu5" class="dropmenudiv">
<a href="dbtest/">Simple Database View</a>
<a href="dbtest/myadmin/">Full Database Administration</a>
<a href="dbtest/backupDB.php">Full Database Backup</a>
</div>

<script type="text/javascript">

cssdropdown.startchrome("chromemenu")

</script>

<br /><br />
