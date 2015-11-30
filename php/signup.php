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
                       
$sql = "SELECT count(username) AS TOTAL FROM USERS WHERE username = '" . $user . "'";

$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($query);

if( $row['TOTAL'] > 0){
    echo "0";
} else {
    $sql = "INSERT INTO users (username, password, id) VALUES ('" . $user . "', '" . $pass . "', NULL)";    
    if($conn->query($sql) === TRUE){
        echo "1";
    } else {
	      echo "Error: " . $sql . "<br> " . $conn->error;
    }
}

$conn->close();


?>

