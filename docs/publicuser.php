#!/usr/bin/php
# Allow anonymous read-only users (publicuser@localhost) to access
# all tables _except_ table 'users'.

<?php

/* MySQL root password here: */
$password = 'xxxx';

$mysqli = mysqli_connect('localhost', 'root', $password);
if (!$mysqli) 
  die("Connection failed: " . mysqli_connect_error() . "\n");
echo "Connected successfully.\n";

/* // Get all the databases for which "tht" is a user. */
/* $sql = "select distinct db from mysql.db where mysql.db.user = 'tht'"; */
/* $res = mysqli_query($mysqli, $sql) or die(mysqli_error() . "\n"); */
/* while ($row = mysqli_fetch_row($res))  */
/*   $databases[] = $row[0]; */

// For just one database:
$databases = array('T3wheat');

foreach ($databases as $db) {
  // Use only those databases that contain a table "users".
  $sql = "select table_schema from information_schema.tables 
          where table_schema = '$db' 
          and table_name = 'users'";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error() . "\n");
  if (mysqli_num_rows($res) > 0) {
    // Revoke SELECT on all tables.  MySQL won't let us revoke on just one.
    $sqlrevoke = "revoke SELECT on $db.* from publicuser@localhost";
    $resrevoke = mysqli_query($mysqli, $sqlrevoke) or die(mysqli_error() . "\n");
    // Grant SELECT on all tables except 'users'.
    $sql2 = "select table_name from information_schema.tables
             where table_schema = '$db' 
             and table_name <> 'users'";
    $res2 = mysqli_query($mysqli, $sql2) or die(mysqli_error() . "\n");
    while ($row2 = mysqli_fetch_row($res2)) {
      $tbl = $row2[0];
      $sqlgrant = "grant SELECT on $db.$tbl to publicuser@localhost";
      $resgrant = mysqli_query($mysqli, $sqlgrant) or die(mysqli_error() . "\n");
    }
    echo "$db\n";
  }
}

mysqli_close($mysqli);
?>