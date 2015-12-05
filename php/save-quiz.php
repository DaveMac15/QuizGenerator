
<?php

include "database.php";

$jsonQuiz = file_get_contents('php://input');
$data = json_decode($jsonQuiz, false);

$name = $data->name;
$id = $data->id;
$questions = $data->questions;

$conn = new mysqli("localhost", $username, $password, "quiz_website");
if($conn->connect_error){
	echo("Connection failed" . $conn->connect_error);
	exit;
}


// if quiz already exists, delete old questions so that they can be replaced
if($id >= 0){
  $sql = "DELETE FROM questions WHERE quiz_id = " . $id;
  
  if($conn->query($sql) === TRUE){
  } else {
	      echo "Error: " . $sql . "<br> " . $conn->error;
    }
}


// if the quiz is newly created, insert new quiz into quizzes table
if($id < 0){
  $sql = "INSERT INTO quizzes (quiz_id, quiz_name) VALUES (NULL, '" . $name . "')";
  $last_id = '0';
  if($conn->query($sql) === TRUE){
	  $last_id = mysqli_insert_id($conn);
	  $id = $last_id;
	  // send the id of the quiz back to the client
	  
  } else {
	  echo "Error: " . $sql . "<br> " . $conn->error;
  }
  
} else { // otherwise, quiz already exists, so just update quiz name
    $sql = "UPDATE quizzes SET quiz_name='" . $name . "' WHERE quiz_id=" . $id;
    if($conn->query($sql) === TRUE){
    } else {
	      echo "Error: " . $sql . "<br> " . $conn->error;
    }
}



// insert questions into questions table
$length = count($questions);
for ($x = 0; $x < $length; $x++){
	$sql = "INSERT INTO questions (question_id, question_text, question_options, question_correctOption, quiz_id) VALUES (NULL,'";
	$text = $questions[$x]->text;
	$sql = $sql . $text . "', '[{\"text\":\"";

	$options = $questions[$x]->options;

	$numOptions = count($options);
	for($y = 0; $y < $numOptions - 1; $y++){
		$option = $options[$y]->text;
		$sql = $sql . $option . "\"}, {\"text\":\"";
	}
	$sql = $sql . $options[$numOptions - 1]->text . "\"}]', '";

	$correctIndex = $questions[$x]->correctIndex;
	
	$sql = $sql . $correctIndex . "', '" . $id . "')";
  
	
  
	if($conn->query($sql) === TRUE){

	} else {
		  echo "Error: " . $sql . "<br> " . $conn->error;
	}
  
}


echo $id;

$conn->close();




?>
