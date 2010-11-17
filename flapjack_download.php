<?php
//<style type="text/css">
//			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
		//  table {background: none; border-collapse: collapse}
		//	td {border: 1px solid #eee !important;}
		//	h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
//</style>


require_once('config.php');

include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
		
		echo "<h2> FLAPJACK DOWNLOAD</h2>";
		echo "<h4><a href='http://bioinf.scri.ac.uk/flapjack/'> Flapjack</a>
		is a standalone Java tool for graphical genotyping developed by the <a href='http://bioinf.scri.ac.uk/public/'>
		Scottish Crop Research Institute (SCRI) Bioinformatics Group.</a> To use <a href='http://bioinf.scri.ac.uk/flapjack/'> Flapjack</a>
		, you will need to download the code from SCRI</h4>";
		echo "<p>Flapjack uses map and genotype data to provide a number of alternative graphical
		genotype views with individual alleles coloured by state, frequency or similarity to a given standard line.</p>";
		echo "Please select Genotype or Map below to generate Flapjack files";
		echo "<br/><br/>";
		echo "<table>";
		echo "<tr>";
		echo "<td>";
		echo "<h4><a href='genotype_flapjack.php'>  Select Genotype DataFiles </a></h4>";
		echo "</td>";
		echo "<td>";
		echo "</td>";
		echo "<td>";
		echo "<h4><a href='map_flapjack.php'>  Select Map File  </a> </h4>";	
		echo "</td>";
		echo "</tr>";
		echo "</table>";


$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
