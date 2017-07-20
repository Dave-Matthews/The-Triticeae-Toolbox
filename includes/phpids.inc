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
