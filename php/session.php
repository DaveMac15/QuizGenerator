<?php

session_start();

if(!isset($_SESSION['username'])){
    $_SESSION['username'] = '';
}
if(!isset($_SESSION['loggedIn'])){
    $_SESSION['loggedIn'] = false;
}

$sessions = array();

$sessions['username'] = $_SESSION['username'];
$sessions['loggedIn'] = $_SESSION['loggedIn'];
header('Content-Type: application/json');
echo json_encode($sessions);

?>