
<head>
<link rel="icon" href="/cgi-bin/hgweb.cgi/static/hgicon.png"
    type="image/png" />
<meta name="robots" content="index, nofollow" />
<link rel="stylesheet"
    href="/cgi-bin/hgweb.cgi/static/style-paper.css"
    type="text/css" />
<title>THT Repository: Move Tag</title>
<script type="text/javascript" src="/jquery.js"></script>
<script type="text/javascript" src="/jqueryui/js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="tagger.js"></script>

</head>
<body>
<pre>
<?php
if (isset($_GET['thtlive'])) {
  passthru("/usr/local/bin/hg tag -l -f thtlive -r " . $_GET['thtlive']);
  passthru("/usr/local/bin/hg up -r thtlive");
 }
?>
</pre>
<iframe id="hgweb" src="/cgi-bin/hgweb.cgi" width="100%" height="100%"
    onload="iframeload();">
    <p>You need to find a browser that supports IFRAME.</p>
</iframe>
</body>
</html>