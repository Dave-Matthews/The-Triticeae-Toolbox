<?php

/**
 * Applying Security Filters
 *
 *      SQL Injection       - using mysql_real_escape_string
 *      XSS                 - completely stripping out all html, css, comments, AND javascript
 *      Cleanliness         - using trim()
 *
 * Whenever bootstrap is called, this segment runs on $_POST, $_GET, and $_REQUEST.
 * It does a nice job of detecting nearly all XSS and Injection attacks.
 *
 * It does NOT run inside of arrays, so avoid doing this "<input type="text" name="ref[]" />"
 *
 * Also do not get rid of the is_array if statement. That will cause very annoying bugs.
 */

set_include_path(get_include_path() . PATH_SEPARATOR . $config['root_dir'] . 'lib/phpids-0.7/lib/');
set_include_path(get_include_path() . PATH_SEPARATOR . $config['root_dir'] . 'includes/');
require_once $config['root_dir'] . 'lib/phpids-0.7/lib/IDS/Init.php';
require_once $config['root_dir'] . 'lib/phpids-0.7/lib/IDS/Log/Composite.php';
require_once $config['root_dir'] . 'lib/phpids-0.7/lib/IDS/Log/File.php';
try {
    //unserialize data so it will not be detected as intrusion
    $getVar = $_GET;
    $postVar = $_POST;
    if (isset($getVar['exps'])) {
        $getVar['exps'] = explode(",", $getVar['exps']);
    }
    if (isset($getVar['pi'])) {
        $getVar['pi'] = explode(",", $getVar['pi']);
    }
    if (isset($postVar['linelist'])) {
        $postVar['linelist'] = explode(",", $postVar['linelist']);
    }
    if (isset($postVar['contents'])) {
        $postVar['contents'] = "";
    }
    $request = array(
    'GET' => $getVar,
    'POST' => $postVar,
    'COOKIE' => $_COOKIE
    );
    $init = IDS_Init::init($config['root_dir'] . 'lib/phpids-0.7/lib/IDS/Config/Config.ini.php');
    $ids = new IDS_Monitor($request, $init);
    $result = $ids->run();
    $impact = $result->getImpact();
    if ($impact > 0) {
            $compositeLog = new IDS_Log_Composite();
            $compositeLog->addLogger(IDS_Log_file::getInstance($init));
            $compositeLog->execute($result);
    }
    if ($impact > 10) {
        $_SESSION['impact'] += $impact;
        if ($_SESSION['impact'] > 50) {
            $impact_sum = $_SESSION['impact'];
            error_log("A phpids repeated event impact = $impact_sum");
            $url = $config['base_url'] . 'feedback2.php';
            echo "Error: Please help us to improve the website by submitting <a href=\"$url\">feedback</a>.";
            die();
        }
    }
    if ($impact > 20) {
        error_log("A phpids event impact = $impact");
        $url = $config['base_url'] . 'feedback2.php';
        echo "Error: Please help us to improve the website by submitting <a href=\"$url\">feedback</a>.";
        die();
    }
} catch (Exception $e) {
    printf('An error occured: %s', $e->getMessage());
}

if ($dontconnect == false || !isset($dontconnect)) {
    $initial_connect = connecti();

    foreach ($_POST as $k => $v) {
        if (!is_array($v)) {
            $v = htmlentities(trim($v));
            $_POST[$k] = mysqli_real_escape_string($initial_connect, $v);
        }
    }
    foreach ($_REQUEST as $k => $v) {
        if (!is_array($v)) {
            $_REQUEST[$k] = mysqli_real_escape_string($initial_connect, trim($v));
        }
    }
    foreach ($_GET as $k => $v) {
        if (!is_array($v)) {
            $_GET[$k] = mysqli_real_escape_string($initial_connect, trim($v));
        }
    }
    mysqli_close($initial_connect);
}
