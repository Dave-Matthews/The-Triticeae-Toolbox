<?php

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
connect();

global $config;
include($config['root_dir'].'theme/normal_header.php');
?>

<h2>Genomic Analysis Tools</h2>
These analysis tools use the R package rrBLUP version 4.1<br>
<a href="http://cran.r-project.org/web/packages/rrBLUP">rrBLUP</a> is available at The Comprehensive R Archive Network<br><br>

<table>
<tr><td>Model<td>Arguments
<tr><td>y = X &beta; + &Zeta; g + &epsilon;
<td>
y = observations<br>
Z = Design matrix for the random effects<br>
X = Design matrix for fixed effects
</table>
The trial is always included as a fixed effect.<br> Principal components can also be added as a fixed effect.<br><br>

<b>Genome Wide Association (GWAS)</b><br>
1. For analysis of single trial P3D=FALSE (variance components are estimated by REML for each marker separately).<br>
2. For more than one trial P3D=TRUE (variance components are estimated by REML only once, without any markers in the model).<br>
3. The dashed line in the Manhattan plot indicates a False Discovery Rate (FDR) of 0.05. When there are no significant loci the dashed line is omitted.<br>
4. Markers that are not mapped are assigned to chromosome 0.<br><br>

<b>Genomic Prediction</b><br>
1. An additive relationship matrix is used for K to creates the model (G = K Vg).<br>
2. In the prediction set the trait values, if any, are set to missing.<br>
</html>
