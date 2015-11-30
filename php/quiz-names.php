
<?php

include 'database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", $username, $password, "quiz_website");
if($conn->connect_error){
	echo("Connection failed" . $conn->connect_error);
	exit;
}

$result = $conn->query("SELECT quiz_name, quiz_id FROM quizzes ORDER BY quiz_id");

$outp = "";
while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
    if ($outp != "") {$outp .= ",";}

    $outp .= '{"name":"'  . $rs["quiz_name"] . '","id":"' . $rs["quiz_id"] . '"}';
    
}
$outp ='['.$outp.']';
$conn->close();

echo($outp);
?>