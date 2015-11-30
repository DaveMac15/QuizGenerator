
<?php

include "database.php";

$quizIndex = file_get_contents('php://input');

$conn = new mysqli("localhost", $username, $password, "quiz_website");
if($conn->connect_error){
	echo("Connection failed" . $conn->connect_error);
	exit;
}

$sql = "DELETE FROM quizzes WHERE quiz_id = " . $quizIndex;
$sql2 = "DELETE FROM questions WHERE quiz_id = " . $quizIndex;

if (($conn->query($sql) === TRUE) and ($conn->query($sql2) === TRUE)) {
	echo "Success";
} else {
	echo "Error deleting quiz: " . $conn->error;
}

$conn->close();

?>