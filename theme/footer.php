<?php
/**
 * Page footer
 *
 * PHP version 5.3
 *
 * @category PHP
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/theme/footer.php
 */

if (isset($footer_div)) {
    ?></div>
    <?php
}
if (!isset($_COOKIE["T3terms"])) {
    ?>
    <script src="theme/usage_popup.js" async></script>
    <div id="dialog-confirm" title="Please Note: Website Data Usage Policy">
    <p><span style="float:left;"></span>
    In 2009 the Toronto International Data Release Workshop agreed on a policy statement about prepublication data sharing. 
    Accordingly, the data producers are making many of the datasets in T3 available prior to publication of a global analysis. 
    Guidelines for appropriate sharing of these data are given in the excerpt from the <a href="../toronto.php" target="_blank">Toronto Statement</a>.<br><br>
    I agree to the Data Usage Policy as specified in Toronto Statement.</p>
    </div>
    <?php
}
?>
<footer itemscope itemtype="http://schema.org/Organization" style="padding: 15px 0 0 0; height: 160px; top: 0;">
<table cellpadding="0" cellspacing="0" border="0" style="background: transparent; border: 0 !important; margin: 0 auto;">
<tr>
<td align="left" style="border: 0 !important">
<table cellpadding="0" cellspacing="0" border="0" style="background: transparent; border: 0 !important; text-align: left;">
<tr>
<td style="border: 0 !important">
<a style="border: 0" href="https://nifa.usda.gov/international-wheat-yield-partnership-iwyp-program" title="USDA National Institute of Food and Agriculture"><img style="border: 0 !important;" src="images/sitelogo.gif" alt="NIFA Logo"></a>
</td><td style="border: 0 !important; text-align: left;">
<a style="border: 0" href="https://www.ars.usda.gov/research/project/?accnNo=430844" title="USDA Agricultural Research Service"><img style="border: 0 !important;" src="images/ars-logo.png" width="300" alt="ARS Logo"></a>
</td><td style="border: 0 !important">
<a style="border: 0" href="http://scabusa.org/" title="US Wheat and Barley Scab Initiative"><img style="border: 0 !important;" src="images/scablogo_notext_small_transparent.gif" alt="Scab Logo"></a>
</td>
<td style="border: 0 !important; text-align: left;">
<p style="margin: 0; font-size: 8pt;">To send questions or suggestions to the T3 curators, please <a href="./feedback.php"><b>click here</b></a>.<p> 
<span style="font-style: italic; font-size: 8pt">
<a href="http://triticeaetoolbox.org/" title="The Triticeae Toolbox">The Triticeae Toolbox</a> is part of the
<a itemprop="name" href="http://triticeaecap.org/" title="Triticeae CAP">Triticeae CAP</a>
project, supported by Agriculture and Food Research Initiative Competitive Grant no. 2011-68002-30029 from the
<a itemprop="name" href="https://nifa.usda.gov/international-wheat-yield-partnership-iwyp-program" title="USDA National Institute of Food and Agriculture">USDA National Institute of Food and Agriculture</a>.
</span>
</p>
<p style="margin: 0; font-size: 8pt;"> 
Copyright &copy; 2006 - 2010 <a href="http://www.iastate.edu/" title="Iowa State University">Iowa State University</a>
</p>
</tr>
</table>
</td>
</tr>
</table>
</footer>
</div>
</body>
</html>
