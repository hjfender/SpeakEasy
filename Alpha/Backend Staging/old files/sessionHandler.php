<?php
	$function = $_POST['function'];
	if($function == "login") {
		$email = $_POST['email'];
		$password = $_POST['password'];
		$idQuery = "SELECT id FROM profiles WHERE email LIKE '$email' AND password LIKE '$password'";
		$conn = connectToDatabase("speakeasy");
		$profiles = $conn->query($idQuery);
		$data = array();
		if($profiles -> num_rows == 0) {
			$data['success'] = "false";
			$data['error'] = "email or password not found";
		} else {
			$token = generateRandomID(32);
			$userID = $profiles->fetch_array()[0];
			$created = time();
			$validUntil = $created;
			$tokenCreationQuery = "INSERT INTO sessions (token, user_id, created, valid_until) VALUES ('$token', '$userID','$created','$validUntil')";
			$tokenDeletionQuery = "DELETE FROM sessions WHERE user_id LIKE '$userID'";
			$conn -> query($tokenDeletionQuery);
			$conn -> query($tokenCreationQuery);
			$data['success'] = "true";
			$data['token'] = $token;
		}
		echo json_encode($data);
		exit;
	} else if($function == "retrieveChats") {
		$token = $_POST['token'];
		$conn = connectToDatabase("speakeasy");
		$idSelectionQuery = "SELECT user_id FROM sessions WHERE token LIKE '$token'";
		$results = $conn ->query($idSelectionQuery);
		$data = array();
		if($results ->num_rows == 0) {
			$data['success'] = "false";
			$data['error'] = "invalid token";
		} else {
			$id = $results ->fetch_array()[0];
			$messageQuery = "SELECT id FROM chats WHERE user_one LIKE '$id' OR user_two LIKE '$id'";
			$results2 = $conn ->query($messageQuery);
			if($results2 -> num_rows == 0) {
				$data['success'] = "false";
				$data['error'] = "no messages";
			} else {
				$data['success'] = "true";
				$rows = $results2 ->num_rows;
				$data['messages'] = array();
				for($i=0; $i < $rows; $i++) {
					$data['messages'][$i] = $results2 ->fetch_array()[0];
				}
			}
		}
		echo json_encode($data);
		exit;
	} else if($function == 'retrieveMessages') {
		$chatID = $_POST['chatID'];
		$token = $_POST['token'];
		$userQuery = "SELECT user_id FROM sessions WHERE token LIKE '$token'";
		$conn = connectToDatabase("speakeasy");
		$userIDResults = $conn ->query($userQuery);
		$data = array();
		if($userIDResults -> num_rows == 0) {
			$data['success'] = "false";
			$data['error'] = "invalid token";
		} else {
			$userID = $userIDResults -> fetch_array()[0];
			$chatQuery = "SELECT file_name FROM chats WHERE (id LIKE '$chatID') AND (user_one LIKE '$userID' OR user_two LIKE '$userID')";
			$chatResults = $conn -> query($chatQuery);
			if($chatResults -> num_rows == 0) {
				$data['success'] = "false";
				$data['error'] = "invalid chat id";
			} else {
				$fileName = "chats/" . ($chatResults -> fetch_array()[0]) . ".txt";
				$lines = file($fileName);
				$text = array();
				foreach ($lines as $line_num => $line) {
					$text[] = $line = str_replace("\n", "", $line);
				}
				$data['success'] = "true";
				$data['text'] = $text;
			}
		}
		echo json_encode($data);
		exit;
	}
	
	
	function connectToDatabase($database) {
		$serverfirst = "localhost";
		$userfirst = "root";
		$password = "";
		$database = "speakeasy";
		// Create connection
		$conn = new mysqli($serverfirst, $userfirst, $password, $database);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 
		return $conn;
	}
	
	function generateRandomID($length) {
		$id = "";
		for ($x=0; $x<$length; $x++) {
			$number = floor(((float)rand()/(float)getrandmax())*10);
			$id = $id.$number;
		}
		return $id;
	}
?>