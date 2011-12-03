<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
?>

<h1>Terms of Use</h1>

All data in T3 may be used without restriction except as follows.


<h3>9K wheat iSelect assay</h3>
<div class="section">
  <p>Due to the importance to the wheat community, the SNP data from the 9K
  wheat iSelect assay has been made public.  The 9K wheat iSelect
  development group reserves the right to publish until June 30, 2012 a
  global analysis of genetic diversity and genetic maps obtained using the
  iSelect assay.  For details please contact Eduard Akhunov
  (<a href="mailto:eakhunov@ksu.edu">eakhunov@ksu.edu</a>)
  <p>
    <!-- <form method = POST action=search.php> -->
    <form method = POST action=http://malt.pw.usda.gov/t3/wheat/search.php>
      <input type=hidden name=keywords value=NSGCwheat9K>
      <input type=submit value=Datasets>
    </form>
</div>


</div>
<?php include($config['root_dir'].'theme/footer.php');?>
