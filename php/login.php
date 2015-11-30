<?php

include "database.php";

$credentials = file_get_contents('php://input');
$data = json_decode($credentials, false);

$user = $data->username;
$pass = $data->password;

$conn = new mysqli("localhost", $username, $password, "quiz_website");
if($conn->connect_error){
	echo("Connection failed" . $conn->connect_error);
	exit;
}
                       
$sql = "SELECT count(username) AS TOTAL FROM USERS WHERE username = '" . $user . "' AND password = '" . $pass . "'";

$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($query);

if( $row['TOTAL'] > 0){
    session_start();
    $_SESSION['username'] = $user;
    $_SESSION['loggedIn'] = true;

    echo "success";
}
else{
    echo "User record does not exist.";
}
$conn->close();

?>
