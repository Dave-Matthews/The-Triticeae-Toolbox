<?php
/**
 * page footer
 *
 * PHP version 5.3
 *
 * @category PHP
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/theme/footer.php
 *
 */

if (isset($footer_div)) {
    ?></div>
    <script src="theme/usage_popup.js"></script>
    </div>
    <?php
}
if (!isset($_COOKIE["T3terms"])) {
    ?><link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <div id="dialog-confirm" title="Please Note: Website Data Usage Policy">
    <p><span style="float:left;"></span>
    In 2009 the Toronto International Data Release Workshop agreed on a policy statement about prepublication data sharing. 
    Accordingly, the data producers are making many of the datasets in T3 available prior to publication of a global analysis. 
    Guidelines for appropriate sharing of these data are given in the excerpt from the <a href="/toronto.php" target="_new">Toronto Statement</a>.<br><br>
    I agree to the Data Usage Policy as specified in Toronto Statement.</p>
    </div>
    <?php
}
?>
<div itemscope itemtype="http://schema.org/Organization" id="footer" style="padding: 15px 0 0 0; height: 160px; background-color: #f9cb73; top: 0;">
<table cellpadding="0" cellspacing="0" border="0" width="700" style="width: 700px; background: transparent; border: 0 !important; margin: 0 auto;">
<tr>
<td align="left" style="border: 0 !important">
<table cellpadding="0" cellspacing="0" border="0" style="background: transparent; border: 0 !important; text-align: left;">
<tr>
<td style="border: 0 !important">
<a style="border: 0" href="http://www.csrees.usda.gov/" title="USDA National Institute of Food and Agriculture"><img style="border: 0 !important;" src="images/sitelogo.gif" alt="NIFA Logo"></a>
</td>
<td style="border: 0 !important; text-align: left;">
<p>
<p style="margin: 0; font-size: 8pt;">To send questions or suggestions to the T3 curators, please <a href="./feedback.php"><b>click here</b></a>.<p> 
<span style="font-style: italic; font-size: 8pt">
<a href="http://triticeaetoolbox.org/" title="The Triticeae Toolbox">The Triticeae Toolbox</a> is part of the
<a itemprop="name" href="http://triticeaecap.org/" title="Triticeae CAP">Triticeae CAP</a>
project, supported by Agriculture and Food Research Initiative Competitive Grant no. 2011-68002-30029 from the
<a itemprop="name" href="http://www.csrees.usda.gov/" title="USDA National Institute of Food and Agriculture">USDA National Institute of Food and Agriculture</a>.
</span>
</p>
<p style="margin: 0; font-size: 8pt;"> 
Copyright &copy; 2006 - 2010 <a href="http://www.iastate.edu/" title="Iowa State University">Iowa State University</a>
</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</div>
</body>
</html>
