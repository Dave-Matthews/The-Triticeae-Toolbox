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

Input maps
<table>
<tr><td>map name<td>markers
<tr><td>Aegilops tauschii 2009<td>877
<tr><td>KleinProteo x KleinChaja, 2012<td>797
<tr><td>SynOP GBS Antmap 2012<td>1485
<tr><td>wsnp 2013 Consensus<td>3503
<tr><td>SynOp GBS BinMap, 2012<td>19720
<tr><td>Composite<td>24,535
</table><br>

Number of markers shared on each map
<table>
<tr><td><td>SynOP_GBS_antmap<td>SynOP_GBS_binmap<td>wsnp2013<td>KPxKC_2012<td>Aegilops2009
<tr><td>SynOP_GBS_antmap
<tr><td>SynOP_GBS_binmap<td>1429<td>
<tr><td>wsnp2013<td>0<td>0
<tr><td>KPxKC_2012<td>0<td>0<td>296
<tr><td>Aegilops2009<td>0<td>0<td>5<td>1
<tr><td>
</table><br>

Summary of LPmerge output for generating composite map
<table>
<tr><td>Chr<td>Input map length<td>Composite map length<td>mean of RMSE
<tr><td>1A<td>130,170,267,281<td>271<td>34
<tr><td>1B<td>140,148,295,220<td>295<td>44
<tr><td>1D<td>180,103,155,239,111<td>180<td>16
<tr><td>2A<td>110,122,242,375<td>368<td>55
<tr><td>2B<td>135,156,258,396<td>395<td>54
<tr><td>2D<td>187,83,104,193,151<td>186<td>25
<tr><td>3A<td>170,207,330,282<td>281<td>40
<tr><td>3B<td>116,162,291,328<td>328<td>47
<tr><td>3D<td>197,253,160,286<td>286<td>21
<tr><td>4A<td>68,141,252,222<td>264<td>74
<tr><td>4B<td>131,200,148<td>148<td>17
<tr><td>4D<td>237,67,129,51<td>127<td>17
<tr><td>5A<td>128,236,245<td>245<td>27
<tr><td>5B<td>172,317,313<td>317<td>30
<tr><td>5D<td>169,227,324<td>324<td>22
<tr><td>6A<td>136,236,243<td>256<td>25
<tr><td>6B<td>139,236,243<td>243<td>20
<tr><td>6D<td>149,161,278<td>161<td>25
<tr><td>7A<td>185,324,296<td>324<td>31
<tr><td>7B<td>180,270,275<td>275<td>21
<tr><td>7D<td>155,194,339<td>221<td>32

</table><br>
<a href="maps/LPmerge.txt">analysis output</a><br><br>
<a href="maps/composite_map.txt">composite map</a><br><br>

</pre>
</div>

<?php
include($config['root_dir'].'theme/footer.php');
