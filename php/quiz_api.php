<?php
/*
 * @Author: David MacCormick
 * @Date: December 2015
 *
 * This is a RESTful API for the Quiz Generator application.
 * It performs the following operations on Quiz objects:
 *
 * OPERATION:		 TYPE OF REQUEST:		DESCRIPTION:
 * /names		     GET    				returns array of quiz names and ids
 * /quiz/id          GET    				returns quiz with id of: id
 * /quiz/            GET    				creates new quiz and returns id added
 * /quiz/id          POST     				updates quiz with id of: id
 * /quiz/id	         DELETE  				deletes quiz with id of: id
 * /highscores/id    GET     				returns the highscores quiz with id of: id
 * /highscores/id    POST     				puts a new record into the highscores for quiz with id of: id
 *
 */

include 'database.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);
$dbname = "quiz_website";

try {
	$options = array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
	$conn = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8", $username, $password, $options);
	
	switch($method) {
		case 'GET':
			if($request[0] == 'quiz'){
				if(count($request) > 1){
					getQuiz($request[1]);
				} else {
					createQuiz();
				}
			} elseif($request[0] == 'names'){
				getNames();
			} elseif($request[0] == 'highscores') {
				getHighscores($request[1]);
			} else {}
			break;
		case 'POST':
			if($request[0] == 'highscores'){
				addHighscore($request[1]);
			} elseif($request[0] == 'quiz'){
				updateQuiz($request[1]);
			} 
			break;
		case 'DELETE':
			deleteQuiz($request[1]);
			break;
	}
}
catch(PDOException $e)
{
    echo "Error: " . $e->getMessage();
	$conn = null;
	exit;
}




function getQuiz($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = :id ORDER BY question_id");
	$stmt->bindParam(':id', $id);
	$stmt->execute();
	
	$outp = "";
	while($rs = $stmt->fetch(PDO::FETCH_ASSOC)) {
		if ($outp != "") {$outp .= ",";}

		$outp .= '{"text":"'  . $rs["question_text"] . '",';
		$outp .= '"options":'   . $rs["question_options"]        . ',';
		$outp .= '"correctIndex":"'. $rs["question_correctOption"]     . '"}'; 
	}
	$outp ='['.$outp.']';
	
	$conn = null;
	die($outp);	
}

function getNames(){
	global $conn;
	
	$result = $conn->query("SELECT quiz_name, quiz_id FROM quizzes ORDER BY quiz_id");
	$outp = "";
	while($rs = $result->fetch(PDO::FETCH_ASSOC)) {
		if ($outp != "") {$outp .= ",";}
		$outp .= '{"name":"'  . $rs["quiz_name"] . '","id":"' . $rs["quiz_id"] . '"}';
	}
	$outp ='['.$outp.']';
	$conn = null;
	die($outp);	
}

function getHighscores($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$stmt = $conn->prepare("SELECT username, score FROM highscores WHERE quiz_id = :id ORDER BY score desc LIMIT 10");
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	$outp = "";
	while($rs = $stmt->fetch(MYSQLI_ASSOC)) {
		if ($outp != "") {$outp .= ",";}
		$outp .= '{"name":"'  . $rs["username"] . '",';
		$outp .= '"score":'   . $rs["score"]        . '}';		
	}
	$outp ='['.$outp.']';
	
	$conn = null;	
	die($outp);
}
function addHighscore($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$params = file_get_contents('php://input');
	$data = json_decode($params, false);

	$user = $data->username;
	$score = $data->score;
	$outp = "";
	
	if($user != ""){    
		$stmt = $conn->prepare("INSERT INTO highscores (quiz_id, username, score, date) VALUES (:id, :user, :score,  CURRENT_TIMESTAMP)");
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':user', $user);
		$stmt->bindParam(':score', $score);

	    if(!$stmt->execute()){
			$outp .= "Error.";
		} else {
			$outp .= '200';
		}
	}
	$conn = null;
	die($outp);
}

/* function to create an empty quiz */
function createQuiz(){
	global $conn;
	$name = "dummy";
	$stmt = $conn->prepare("INSERT INTO quizzes (QUIZ_ID, QUIZ_NAME) VALUES (NULL, :name)");
	$stmt->bindParam(':name', $name);
    
    $stmt->execute();
	
	    $id = $conn->lastInsertId();
            //send id of the new quiz to the client
            echo $id;
    
    $conn = null;
}

function updateQuiz($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$jsonQuiz = file_get_contents('php://input');
	$data = json_decode($jsonQuiz, false);

	$name = $data->name;
	$questions = $data->questions;
	
	// delete old questions so that they can be replaced
	if($id >= 0){
	    	  
		$stmt = $conn->prepare("DELETE FROM questions WHERE quiz_id = :id");
		$stmt->bindParam(":id", $id);

	    if(!$stmt->execute()){
			  echo "Error: " . $sql . "<br> " . $conn->error;
		}
	}

	// update quiz name
	$stmt2 = $conn->prepare("UPDATE quizzes SET QUIZ_NAME = :name WHERE QUIZ_ID = :id1");
	$stmt2->bindParam(':name', $name);
	echo $name;
	$stmt2->bindParam(':id1', $id);
	if(!$stmt2->execute()){
		  echo "Error: " . $sql . "<br> " . $conn->error;
	}
	
	// prepared statement to insert a question
	$stmt = $conn->prepare("INSERT INTO questions (question_id, question_text, question_options, "
                                  . "question_correctOption, quiz_id) VALUES (NULL, :text, :options, :correct, :id)");
	
	// insert questions into questions table
	$length = count($questions);
	for ($x = 0; $x < $length; $x++){
		$text = $questions[$x]->text;
		
		$options = $questions[$x]->options;
		$optionsText = "";
		
		$numOptions = count($options);
		for($y = 0; $y < $numOptions - 1; $y++){
			$option = $options[$y]->text;
			$optionsText = $optionsText . "{\"text\":\"" . $option . "\"},";
		}
		$optionsText = $optionsText . "{\"text\":\"" . $options[$numOptions - 1]->text . "\"}";
		$optionsText = "[" . $optionsText . "]";
		
		$correctIndex = $questions[$x]->correctIndex;
	  
		$stmt->bindParam(':text', $text);
		$stmt->bindParam(':options', $optionsText);
		$stmt->bindParam(':correct', $correctIndex, PDO::PARAM_INT);
		$stmt->bindParam(':id', $id);
		
		if(!$stmt->execute()){
			  echo "Error: " . $sql . "<br> " . $conn->error;
		} 
	}
	$conn = null;
}

function deleteQuiz($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$stmt1 = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = :id");
	$stmt2 = $conn->prepare("DELETE FROM questions WHERE quiz_id = :id");
	
	$stmt1->bindParam(':id', $id);
	$stmt2->bindParam(':id', $id);
		
	if ((!$stmt1->execute()) or (!$stmt2->execute())) {
		echo "Error deleting quiz: " ;
	} else {
		echo "200";
	}
	$conn = null;
	
}

?>
