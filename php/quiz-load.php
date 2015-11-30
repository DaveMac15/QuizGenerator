
<?php

include "database.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$quizIndex = $_GET['id'];

$conn = new mysqli("localhost", $username, $password, "quiz_website");
if($conn->connect_error){
	echo("Connection failed" . $conn->connect_error);
	exit;
}

$result = $conn->query("SELECT * FROM questions WHERE quiz_id = " . $quizIndex . " ORDER BY question_id");


$outp = "";
while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
    if ($outp != "") {$outp .= ",";}

    $outp .= '{"text":"'  . $rs["question_text"] . '",';
    $outp .= '"options":'   . $rs["question_options"]        . ',';
    $outp .= '"correctIndex":"'. $rs["question_correctOption"]     . '"}'; 
}
$outp ='['.$outp.']';
$conn->close();

echo($outp);
?>