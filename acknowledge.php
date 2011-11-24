<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
?>

<h1>Acknowledgments</h1>

<h3>9K wheat iSelect assay</h3>

<div class="section">
  <p>The 9,000 SNP wheat iSelect assay was designed by research groups
    funded by the USDA National Institute of Food and Agriculture (grant
    CRIS0219050; PI: E. Akhunov; co-PIs: S. Chao, G. Brown-Guedira, D. See,
    M. Sorrells) and the Grains Research and Development Corporation (GRDC),
    Australia (PI: Matthew Hayden). The details of assay design can be
    obtained from
    the <a href="http://wheatgenomics.plantpath.ksu.edu/snp/">USDA wheat SNP
      development project</a>
    and <a href="http://wheatgenomics.plantpath.ksu.edu/IWSWG/">International
      Wheat SNP Working Group</a> websites.
  <p>
    <!-- <form method = POST action=search.php> -->
    <form method = POST action=http://malt.pw.usda.gov/t3/wheat/search.php>
      <input type=hidden name=keywords value=NSGCwheat9K>
      <input type=submit value=Datasets>
    </form>
</div>



</div>
<?php include($config['root_dir'].'theme/footer.php');?>
