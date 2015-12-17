<?php

include "database.php";

$credentials = file_get_contents('php://input');
$data = json_decode($credentials, false);

$user = $data->username;
$pass = $data->password;
$dbname = "quiz_website";
try {
    $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	// prepare sql and bind parameters
	$stmt = $conn->prepare("SELECT count(*) AS TOTAL FROM users WHERE username = :user AND password = :pw");
	

	$stmt->bindParam(':user', $user);
	$stmt->bindParam(':pw', $pass);
	$stmt->execute();    
    $numRows = $stmt->fetchColumn();

	if( $numRows > 0){
		session_start();
		$_SESSION['username'] = $user;
		$_SESSION['loggedIn'] = true;
		$_SESSION['LAST_ACTIVITY'] = time();
		
		echo "1";
	}
	else{
		echo "User record does not exist.";
	}
	
} 
catch(PDOException $e)
{
    echo "Error: " . $e->getMessage();
}
$conn = null;

?>
