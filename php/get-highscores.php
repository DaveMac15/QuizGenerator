<?php

include "database.php";

$params = file_get_contents('php://input');
$data = json_decode($params, false);

$quiz_id = $data->quiz_id;
$user = $data->username;
$score = $data->score;

$conn = new mysqli("localhost", $username, $password, "quiz_website");
if($conn->connect_error){
	echo("Connection failed" . $conn->connect_error);
	exit;
}
if($user != ""){                       
  $sql = "INSERT INTO highscores (quiz_id, username, score, date) VALUES ('" . $quiz_id . "', '" . $user . "', '" . $score . "', CURRENT_TIMESTAMP)";
  if($conn->query($sql) === TRUE){
  } else {
	    echo "Error: " . $sql . "<br> " . $conn->error;
  }
}
$result = $conn->query("SELECT username, score FROM highscores WHERE quiz_id = " . $quiz_id . " ORDER BY score desc LIMIT 10");
  
$outp = "";
while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
    if ($outp != "") {$outp .= ",";}

    $outp .= '{"name":"'  . $rs["username"] . '",';
    $outp .= '"score":'   . $rs["score"]        . '}';
    
}
$outp ='['.$outp.']';
$conn->close();

echo($outp);

?>
