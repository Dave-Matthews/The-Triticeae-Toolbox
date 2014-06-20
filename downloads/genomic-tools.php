<?php

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
connect();

global $config;
include($config['root_dir'].'theme/normal_header.php');
?>

<h2>Genomic Analysis Tools</h2>
These analysis tools use the R package rrBLUP version 4.2<br>
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

<b>Genome Wide Association (GWAS)</b>
<ul>
<li>R function GWAS(pheno, geno, fixed="trial", K=mrkRelMat, n.PC=model_opt1, P3D=model_opt2)
<li>method (EMMAX Kang et al. 2010) - variance components are estimated by REML only once, without any markers in the model. This method is faster but can underestimate significance.
<li>method (EMMA with REML Kang et al. 2008) - variance components are estimated by REML for each marker separately.
<li>The dashed line in the Manhattan plot indicates a False Discovery Rate (FDR) of 0.05. When there are no significant loci the dashed line is omitted.</li>
<li>Markers that are not mapped are assigned to chromosome 0.
</ul>

<b>Genomic Prediction</b>
<ul>
<li>R function kin.blup(data, "geno", "pheno", K=mrkRelMat, fixed="trial")
<li>An additive relationship matrix is used for K to creates the model (G = K Vg).
<li>In the prediction set the trait values, if any, are set to missing.
</ul>
</html>
