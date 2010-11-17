<?php
$lang = array();
if ( authenticate( array( USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) )
{
	$lang = array(
		"desc_sc1" => "Editing Pedigree Related Information.",
		"desc_sc2" => "Editing Trait Related Information",
		"desc_sc3" => "Editing Genotyping Related Information",
		"desc_sc4" => "Editing Expression Related information",
		"desc_sc5" => "Administer the Database",
		"desc_sch" => "Go to the homepage."
	);
}
else
{
	$lang = array(
		//"desc_about" => "About <em>The Hordeum Toolbox</em>.",
		"desc_sc1" => "Search by Pedigree Related Information.",
		"desc_sc2" => "Search by Trait Related Information.",
		"desc_sc3" => "Search by Genotyping Related Information.",
		"desc_sc4" => "Search by Expression Related information.",
		"desc_sch" => "Go to the homepage.",
		"desc_sc5" => "",
	);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>The Hordeum Toolbox</title>

	<base href="<?php echo $config['base_url']; ?>" />

	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="copyright" content="Copyright (C) 2007 Iowa State University. All rights reserved." />
	<meta name="expires" content="<?php echo date("D, d M Y H:i:s", time()+6*60*60); ?> GMT">
	<meta name="keywords" content="hordeum,toolbox,barley,tht,database" />
	<meta name="revisit-After" content="1 days" />

	<link rel="stylesheet" type="text/css" href="<?php echo $config['base_url']; ?>theme/new.css"></link>

	<script type="text/javascript" src="<?php echo $config['base_url']; ?>includes/core.js"></script>
	<script type="text/javascript" src="<?php echo $config['base_url']; ?>theme/new.js"></script>
	<script type="text/javascript" src="<?php echo $config['base_url']; ?>theme/js/prototype.js"></script>
	<script type="text/javascript" src="<?php echo $config['base_url']; ?>theme/js/scriptaculous.js"></script>
</head>
<body onload="javascript:setup()">
<div id="container">
	<div id="barleyimg">
	</div>
	<h1 id="logo">
		The Hordeum Toolbox
	</h1>
	<div id="util">
		<div id="utilright">
		</div>
		<a href="about.php">
			about</a> | <a href="contact.php">contact
		</a>
	</div>
	<div id="nav">
		<ul>
			<li id="active">
				<a href="">
				Home
				</a>
			</li>
			<li>
				<a title="<?php echo $lang["desc_sc1"]; ?>">
					Pedigree
				</a>
				<ul>
					<?php if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/pedigreeAdd.php" title="Add Pedigree Information">
							Add Pedigree Information
						</a>
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/edit_pedigree.php" title="Edit Pedigree Information">
							Edit Pedigree Information
						</a>
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/edit_line.php" title="Edit Line Records">
							Edit Line Records
						</a>
					<?php endif; ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>all_breed_css.php" title="Show Breeding Programs">
							Show Breeding Programs
						</a>
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>pedigree/pedigree_tree.php" title="Show Pedigree Tree">
							Show Pedigree Tree
						</a>
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>pedigree/parse_pedigree.php" title="Parse External String-based Pedigree">
							Parse External String-based Pedigree
						</a>
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php" title="Select Lines">
							Select Lines
						</a>
					</li>

				</ul>

			</li>
			<li>
				<a title="<?php echo $lang["desc_sc2"]; ?>">
					Traits
				</a>
				<ul>
					<?php if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/input_gateway.php" title="Add Trait Definitions">
							Add Trait Definitions
						</a>
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/edit_traits.php" title="Edit Trait Definitions">
							Edit Trait Definitions
						</a>
					</li>
					<?php endif; ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>phenotype/compare.php" title="Select Lines by Phenotype">
							Select Lines by Phenotype
						</a>
					</li>
				</ul>
			</li>
			<li>
				<a title="<?php echo $lang["desc_sc3"]; ?>">
					Genotyping
				</a>
				<ul>
					<?php if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/input_gateway.php" title="Add Marker Definitions">
							Add Marker Definitions
						</a>
					</li>
					<?php endif; ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" title="Select Markers">
							Select Markers
						</a>
					</li>
				</ul>
			</li>
			<li>
				<a title="<?php echo $lang["desc_sc4"]; ?>">
					Expression
				</a>
				<ul>
					<?php if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/input_gateway.php" title="Add Expression Data">
							Add Expression Data
						</a>
					</li>
					<?php endif; ?>
					<li>
						<a href="http://plexdb.org" title="PLEXdb">
							PLEXdb
						</a>
					</li>
				</ul>
			</li>
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
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>dbtest/myadmin/" title="Full Database Administration">
							Full Database Administration
						</a>
					</li>
					<?php endif; ?>
					<?php if( authenticate( array( USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>dbtest/backupDB.php" title="Full Database Backup">
							Full Database Backup
						</a>
					</li>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/input_gateway.php" title="Data Input Gateway">Data Input Gateway</a></li>
					<?php endif; ?>
					<?php if( authenticate( array( USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) ): ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/export_gateway.php" title="Data export Gateway">
							Data export Gateway
						</a>
					</li>
					<?php endif; ?>
					<?php if( authenticate( array(USER_TYPE_ADMINISTRATOR ) ) ): ?>
					<li>
						<a href="<?php echo $config['base_url']; ?>login/cleanup_temporary_dir.php" title="Clean up temporary files">
							Clean up temporary files
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</li>
		</ul>
	</div>
	<div id="quicklinks" style="top:141px">
		<h2>
			Quick Links
		</h2>
		<ul>
			<li>
				<a href="">Home</a>
			</li>
			<?php if ( isset( $_SESSION['username'] ) && !isset( $_REQUEST['logout'] ) ):  ?>
			<li>
				<a title="Logout" href="<?php echo $config['base_url']; ?>logout.php">Logout <span style="font-size: 10px">(<?php echo $_SESSION['username'] ?>)</span></a>
			</li>
			<?php else: ?>
			<li>
				<a title="Login" href="<?php echo $config['base_url']; ?>login.php"><strong>Login/Register</strong></a>
			</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo $config['base_url']; ?>genotyping/marker_selection.php">Select Markers</a>
			</li>
			<li>
				<a href="<?php echo $config['base_url']; ?>advanced/phenotype.php">Advanced Phenotype Search</a>
			</li>
		</ul>
		<div id="searchbox">
			<form style="margin-bottom:3px" action="search.php" method="post">
				<div style="margin: 0; padding: 0;">
					<input type="hidden" value="Search" />
					<input style="width:138px" type="text" name="keywords" value="Search..." onfocus="javascript:this.value=''" onblur="javascript:if(this.value==''){this.value='Search...';}" />
					<input style="border: none; padding: 0; width: 25px; height: 21px" type="image" src="theme/images/searchbtn.png" align="middle" onclick="javascript:void(0);" />
				</div>
			</form>
			<a href="<?php echo $config['base_url']; ?>advanced_search.php">Advanced Search</a>
		</div>
	</div>
	<div id="main">