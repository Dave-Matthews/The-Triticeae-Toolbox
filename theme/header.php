<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>The Hordeum Toolbox</title>
    <base href="http://www.hordeumtoolbox.org" />
    <link rel="stylesheet" type="text/css" href="theme/new.css" />
    <script type="text/javascript" src="includes/core.js" />
    <script type="text/javascript" src="theme/new.js" />
    <script type="text/javascript" src="theme/js/prototype.js" />
    <script type="text/javascript" src="theme/js/scriptaculous.js" />
<?php
$is_admin = authenticate(array(USER_TYPE_ADMINISTRATOR));
$is_curator = authenticate(array(USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR));
$is_participant = authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR));

// TODO: finish this file
