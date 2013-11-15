<?php

require_once('config.php');
include_once($config['root_dir'].'includes/bootstrap.inc');
connect();
include($config['root_dir'].'theme/normal_header.php');
?>

<h2>Creation of Composite map</h2>

Created using <a href="http://cran.r-project.org/web/packages/LPmerge/">LPmerge version</a> 1.4<br>
Endelman,J.B. and C. Plomion (submitted) LPmerge: an R package for merging genetic maps by linear programming.<br><br>

LPmerge(Maps, max.interval=3)<br><br>
<a href="maps/LPmerge.txt">analysis output</a><br><br>
<a href="maps/composite_map.txt">composite map</a><br><br>

</pre>
</div>

<?php
include($config['root_dir'].'theme/footer.php');
