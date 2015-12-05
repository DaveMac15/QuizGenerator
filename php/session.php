<?php

session_start();

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    // session will time out after 15 minutes of inactivity
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    $_SESSION['username'] = ''; 
    $_SESSION['loggedIn'] = false;
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

$sessions = array();

$sessions['username'] = $_SESSION['username'];
$sessions['loggedIn'] = $_SESSION['loggedIn'];
header('Content-Type: application/json');
echo json_encode($sessions);

?>