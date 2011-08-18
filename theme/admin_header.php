<?php
/*
*
*
*  6/20/11 J.Lee  Suppress the full DB admin option
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
  <meta name="copyright" content="Copyright (C) 2008 Iowa State University. All rights reserved." >
  <meta name="expires" content="<?php echo date("D, d M Y H:i:s", time()+6*60*60); ?> GMT">
  <meta name="keywords" content="hordeum,toolbox,barley,tht,database" >
  <meta name="revisit-After" content="1 days" >
  <title>The Triticeae Toolbox</title>

  <base href="<?php echo $config['base_url']; ?>" >
  <link rel="stylesheet" type="text/css" href="<?php echo $config['base_url']; ?>theme/new.css">
  <script type="text/javascript" src="<?php echo $config['base_url']; ?>includes/core.js"></script>
  <script type="text/javascript" src="<?php echo $config['base_url']; ?>theme/new.js"></script>
  <script type="text/javascript" src="<?php echo $config['base_url']; ?>theme/js/prototype.js"></script>
  <script type="text/javascript" src="<?php echo $config['base_url']; ?>theme/js/scriptaculous.js"></script>
  <?php
  global $usegbrowse;
if (isset($usegbrowse) && $usegbrowse)
  require_once $config['root_dir'] . 'includes/gbrowse-deps.inc';
?>
</head>
<body onload="javascript:setup();<?php
if (isset($usegbrowse) && $usegbrowse)
  echo " Overview.prototype.initialize(); Details.prototype.initialize()"; ?>">
  <?php 
  if (isset($usegbrowse) && $usegbrowse)
    echo <<<EOD
      <script>
      var balloon500 = new Balloon;
BalloonConfig(balloon500,'GBubble');
balloon500.images              = './gbrowse/images/balloons';
balloon500.balloonImage        = 'balloon.png';
balloon500.ieImage             = 'balloon_ie.png';
balloon500.upLeftStem          = 'up_left.png';
balloon500.downLeftStem        = 'down_left.png';
balloon500.upRightStem         = 'up_right.png';
balloon500.downRightStem       = 'down_right.png';
balloon500.closeButton         = 'close.png';
balloon500.maxWidth = 500;
balloon500.delayTime = 50;
var balloon = new Balloon;
BalloonConfig(balloon,'GBubble');
balloon.images              = './gbrowse/images/balloons';
balloon.balloonImage        = 'balloon.png';
balloon.ieImage             = 'balloon_ie.png';
balloon.upLeftStem          = 'up_left.png';
balloon.downLeftStem        = 'down_left.png';
balloon.upRightStem         = 'up_right.png';
balloon.downRightStem       = 'down_right.png';
balloon.closeButton         = 'close.png';
balloon.delayTime = 50;
</script>
EOD;
?>
<div id="container">
  <div id="barleyimg">
  </div>
  <h1 id="logo">
  The Triticeae Toolbox
  </h1>
  <div id="util">
  <div id="utilright">
  </div>
  <a href="./feedback.php">Feedback</a> | <a href="about.php">About T3</a>
  </div>

<?php
  //The navigation tab menus 
  //Tooltips:
  $lang = array(
		"desc_sc1" => "Search by germplasm and phenotype information",
		"desc_sc2" => "Credits",
		"desc_sc3" => "Search by genotyping information",
		"desc_sc4" => "Search by Expression Related information.",
		"desc_sc5" => "Database administration",
		"desc_sc6" => "Visualization tools",
		);
?>
 <div id="nav">
  <ul>
  <li>
  <a href="">
  Home
  </a>
  <li>
  <a title="<?php echo $lang["desc_sc1"]; ?>">Lines</a>
  <ul>
  <li>
  <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php" title="Select Lines">
  Select Lines by Properties</a>
  <li>
  <a href="<?php echo $config['base_url']; ?>phenotype/compare.php" title="Select Lines by Phenotype">
  Select Lines by Phenotype</a>
  <li>
  <a href="<?php echo $config['base_url']; ?>pedigree/pedigree_tree.php" title="Show Pedigree Tree">
  Pedigree Trees</a>
  </ul>
  <li>
  <a title="<?php echo $lang["desc_sc3"]; ?>">Markers</a>
  <ul>
  <li>
  <a href="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" title="Select Markers">
  Select Markers</a>
  <li>
  <a href="<?php echo $config['base_url']; ?>maps.php" title="Genetic Maps">Genetic Maps</a>
  <li><a href="<?php echo $config['base_url']; ?>snps.php" title="SNP alleles and sequences">SNP alleles and sequences</a>
  </ul>
  <li><a title="<?php echo $lang["desc_sc6"]; ?>">Analysis</a>
  <ul>
  <li><a href="<?php echo $config['base_url']; ?>cluster_lines.php" title="Find lines like my lines of interest">Cluster Lines by Genotype</a>
  <li><a href="<?php echo $config['base_url']; ?>advanced_search.php" title="Alleles for selected markers">View Haplotypes</a>
  <li><a href="<?php echo $config['base_url']; ?>pedigree/parse_pedigree.php" title="Parse a pedigree string in Purdy notation">Parse Purdy Pedigrees</a>
<!--  <li><a href="<?php echo $config['base_url']; ?>not_yet.php" title="Markers polymorphic for a pair of lines">Marker Polymorphisms</a> -->
  </ul>
  <li>
  <a title="<?php echo $lang["desc_sc2"]; ?>">Data Sources</a>
  <ul>
  <li>
  <a href="<?php echo $config['base_url']; ?>all_breed_css.php" title="Show CAP Data Programs">
  CAP Data Programs</a>
  </ul>

  <?php 
  //  if( authenticate( array( USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): 
  if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): 
  ?> 
   <li> <a title="Curate the Database">Curation</a>
      <ul>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/input_trait_router.php" title="Must precede loading data about the traits">
      Add/Edit Trait Definitions</a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/input_line_names.php" title="Must precede loading data about the lines">
      Add/Edit Lines</a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/input_pedigree_router.php" title="Pedigree information about the lines, optional">
      Add/Edit Pedigree</a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/genotype_annotations_upload.php" title="Add Genotype Annotations Data">
      Add Genotype Annotations</a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/genotype_data_upload.php" title="Add Genotyping Result Data">
      Add Genotype Results </a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_router.php" title="Descriptions of phenotype experiments, must precede loading results">
      Add Phenotype Experiment Annotations</a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/input_experiments_upload_router.php" title="Phenotype data">
      Add Phenotype Experiment Results</a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/input_map_upload.php" title="Genetic maps of the markers">
      Add Maps</a></li>
      <li><a href="<?php echo $config['base_url']; ?>curator_data/markers_upload.php" title="Must precede loading data about the markers">
      Add/Edit Markers</a></li>
      </ul>
      <?php endif ?>

  <?php  			
//   if( authenticate( array( USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): 
  if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): 
    ?>
  <li> <a title="Manage access to my data">Share data</a>
  <ul>
  <li><a href="<?php echo $config['base_url']; ?>sharegroup.php">Manage access to my data</a>
  </ul>
  <?php endif ?>

      <?php if( authenticate( array( USER_TYPE_ADMINISTRATOR ) ) ): ?>
      <li>
	 <a title="<?php echo $lang["desc_sc5"]; ?>">
	 Database
	 </a>
	 <ul>
	 <?php if( authenticate( array( USER_TYPE_ADMINISTRATOR ) ) ): ?>
	 <li>
	    <a href="<?php echo $config['base_url']; ?>dbtest/" title="Table Status">
	    Table Status
	    </a>
<!--	  	    <li>
	    <a href="<?php echo $config['base_url']; ?>dbtest/myadmin/" title="Full Database Administration">
	    Full Database Administration
	    </a>
-->
	  	    <?php endif; ?>
<?php if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
    <li>
       <a href="<?php echo $config['base_url']; ?>dbtest/backupDB.php" title="Full Database Backup">
       Full Database Backup
       </a>
            <li>
       <a href="<?php echo $config['base_url']; ?>login/input_gateway.php" title="Data Input Gateway">Data Input Gateway</a></li>
       <?php endif; ?>
<?php if( authenticate( array( USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
    <li>
       <a href="<?php echo $config['base_url']; ?>login/export_gateway.php" title="Data export Gateway">
       Data export Gateway
       </a>
            <?php endif; ?>
<?php if( authenticate( array(USER_TYPE_ADMINISTRATOR ) ) ): ?>
    <li>
       <a href="<?php echo $config['base_url']; ?>login/cleanup_temporary_dir.php" title="Clean up temporary files">
       Clean up temporary files
       </a>
            <?php endif; ?>
</ul>
</li>
<?php endif; ?>
			
			
</ul>
</div>
<div id="quicklinks" style="top:141px">
  <h2>
  Quick Links
  </h2>
		<ul>
  <!--  <li>
  <a href="">Home</a>
  -->
  <?php if ( isset( $_SESSION['username'] ) && !isset( $_REQUEST['logout'] ) ):  ?>
    <li>
       <a title="Logout" href="<?php echo $config['base_url']; ?>logout.php">Logout <span style="font-size: 10px">(<?php echo $_SESSION['username'] ?>)</span></a>
            <?php else: ?>
       <li>
	  <a title="Login" href="<?php echo $config['base_url']; ?>login.php"><strong>Login/Register</strong></a>
		  <?php endif; ?>
			
<li>
<a href="<?php echo $config['base_url']; ?>downloads/downloads.php">Download Genotype/Phenotype Data (Tassel format)</a>
			
  <li>
  <a href="<?php echo $config['base_url']; ?>/flapjack_download.php">Download Genotype Data (Flapjack format)</a>
			
			
  </ul>
  <div id="searchbox">
  <form style="margin-bottom:3px" action="search.php" method="post">
  <div style="margin: 0; padding: 0;">
  <input type="hidden" value="Search" >
  <input style="width:170px" type="text" name="keywords" value="Quick search..." onfocus="javascript:this.value=''" onblur="javascript:if(this.value==''){this.value='Quick search...';}" >
  </div>
  </form>
  <br>
<!--  <a href="<?php echo $config['base_url']; ?>advanced_search.php">Advanced Search</a> -->
  </div>
  </div>
  <div id="main">
