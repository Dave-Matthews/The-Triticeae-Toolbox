<?php

if (isset($_GET['id'])){
    $id = $_GET['id'];
    }
else{
    die();
    }
if (isset($_GET['name'])){
    $name = $_GET['name'];
    }
else{
    die();
    }
$delete = FALSE;
if (isset($_GET['delete'])){
    $delete = TRUE;
    }  
    
include('cookie/cookie.php');
$mycookie = new MyCookie($name);

if ($delete){
    $mycookie->remove_general($id);
}else{
    $mycookie->add_general($id);
}
$mycookie->to_file();

?>
