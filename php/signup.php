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
   $stmt = $conn->prepare('select count(*) FROM users WHERE username = :user');   
   $stmt->bindParam(':user', $user);
   $stmt->execute();
   $numRows= $stmt->fetchColumn();
   
   
   if($numRows > 0){
      echo "0";
   } else {
      $stmt = $conn->prepare('INSERT INTO users (USERNAME, PASSWORD, ID) VALUES (:user, :pw, NULL)');
      $stmt->bindParam(':user', $user);
      $stmt->bindParam(':pw', $pass);
      $stmt->execute();
      echo "1";
   }
   
   
}
catch(PDOException $e)
{
    echo "Error: " . $e->getMessage();
}
$conn = null;

?>

