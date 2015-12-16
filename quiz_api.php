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
 * /quiz/id          PUT     				updates quiz with id of: id
 * /quiz/id	         DELETE  				deletes quiz with id of: id
 * /highscores/id    GET     				returns the highscores quiz with id of: id
 * /highscores/id    POST     				puts a new record into the highscores for quiz with id of: id
 *
 */

include 'php/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);

$conn = new mysqli("localhost", $username, $password, "quiz_website");
if($conn->connect_error){
	die("Connection failed" . $conn->connect_error);
}

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
		}
		break;
	case 'PUT':
		if($request[0] == 'quiz'){
			updateQuiz($request[1]);
		} 
		break;
	case 'DELETE':
		deleteQuiz($request[1]);
		break;
}


function getQuiz($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_id");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	$outp = "";
	while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
		if ($outp != "") {$outp .= ",";}

		$outp .= '{"text":"'  . $rs["question_text"] . '",';
		$outp .= '"options":'   . $rs["question_options"]        . ',';
		$outp .= '"correctIndex":"'. $rs["question_correctOption"]     . '"}'; 
	}
	$outp ='['.$outp.']';	
	$conn->close();
	die($outp);	
}

function getNames(){
	global $conn;
		
	$result = $conn->query("SELECT quiz_name, quiz_id FROM quizzes ORDER BY quiz_id");
	$outp = "";
	while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
		if ($outp != "") {$outp .= ",";}
		$outp .= '{"name":"'  . $rs["quiz_name"] . '","id":"' . $rs["quiz_id"] . '"}';
	}
	$outp ='['.$outp.']';
	$conn->close();
	die($outp);	
}

function getHighscores($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$stmt = $conn->prepare("SELECT username, score FROM highscores WHERE quiz_id = ? ORDER BY score desc LIMIT 10");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	$outp = "";
	while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
		if ($outp != "") {$outp .= ",";}
		$outp .= '{"name":"'  . $rs["username"] . '",';
		$outp .= '"score":'   . $rs["score"]        . '}';		
	}
	$outp ='['.$outp.']';
	$conn->close();

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
		$stmt = $conn->prepare("INSERT INTO highscores (quiz_id, username, score, date) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
		$stmt->bind_param("isd", $id, $user, $score);

	    if($stmt->execute() === TRUE){
			$outp .= '200';
		} else {
			$outp .= "Error: " . $sql . "<br> " . $conn->error;
		}
	}
	$conn->close;
	die($outp);
}

/* function to create an empty quiz */
function createQuiz(){
	global $conn;
	
	$sql = "INSERT INTO quizzes (quiz_id, quiz_name) VALUES (NULL, '')";
    //$id = '0';
    if($conn->query($sql) === TRUE){
	    $id = mysqli_insert_id($conn);
	  
	    // send the id of the new quiz to the client
	    echo $id;
    } else {
	    echo "Error: " . $sql . "<br> " . $conn->error;
    }
    $conn->close();
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
	    	  
		$stmt = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
		$stmt->bind_param("i", $id);

	    if($stmt->execute() === TRUE){
			
		} else {
			  echo "Error: " . $sql . "<br> " . $conn->error;
		}
	}

	// update quiz name
	$stmt = $conn->prepare("UPDATE quizzes SET quiz_name = ? WHERE quiz_id = ?");
	$stmt->bind_param("si", $name, $id);
	if($stmt->execute() === TRUE){
	} else {
		  echo "Error: " . $sql . "<br> " . $conn->error;
	}
	
	// prepared statement to insert a question
	$stmt = $conn->prepare("INSERT INTO questions (question_id, question_text, question_options, question_correctOption, quiz_id) VALUES (NULL, ?, ?, ?, ?)");
	
	// insert questions into questions table
	$length = count($questions);
	for ($x = 0; $x < $length; $x++){
		
		//$sql = "INSERT INTO questions (question_id, question_text, question_options, question_correctOption, quiz_id) VALUES (NULL,'";
		$text = $questions[$x]->text;
		//$sql = $sql . $text . "', '[{\"text\":\"";

		$options = $questions[$x]->options;
		$optionsText = ""; // [{"text":"Winston Churchill"}, {"text":"David Cameron"}, {"text":"Margaret Thatcher"}, {"text":"Tony Blair"}]
		
		$numOptions = count($options);
		for($y = 0; $y < $numOptions - 1; $y++){
			$option = $options[$y]->text;
			//$sql = $sql . $option . "\"}, {\"text\":\"";
			$optionsText = $optionsText . "{\"text\":\"" . $option . "\"},";
		}
		//$sql = $sql . $options[$numOptions - 1]->text . "\"}]', '";
		$optionsText = $optionsText . "{\"text\":\"" . $options[$numOptions - 1]->text . "\"}";
		$optionsText = "[" . $optionsText . "]";
		
		$correctIndex = $questions[$x]->correctIndex;
		
		//$sql = $sql . $correctIndex . "', '" . $id . "')";
	  
		$stmt->bind_param("ssii", $text, $optionsText, $correctIndex, $id);
	  
		if($stmt->execute() === TRUE){

		} else {
			  echo "Error: " . $sql . "<br> " . $conn->error;
		} 
	}
	$conn->close();
}


function deleteQuiz($id){
	global $conn;
	
	//validate the id as an integer
	$id = filter_var($id, FILTER_VALIDATE_INT);
	
	$stmt1 = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
	$stmt2 = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
	
	$stmt1->bind_param("i", $id);
	$stmt2->bind_param("i", $id);
		
	if (($stmt1->execute() === TRUE) and ($stmt2->execute() === TRUE)) {
		echo "200";
	} else {
		echo "Error deleting quiz: " . $conn->error;
	}

	$conn->close();
	
}

?>