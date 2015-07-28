<?php
    $log = array();
    if(!isset($_POST['function'])) {
        $log['success'] = "false";
        $log['error'] = "no function given";
    } else {
        $function = $_POST['function'];
        if($function == 'send') {
            sendMessage($log);
        } else if($function == 'createChat') {
            createChat($log);
        } else if($function == 'createProfile') {
            createProfile($log);
        } else if($function == 'retrieve') {
            retrieveMessages($log);
        } else if($function == 'connect') {
            connectToChat($log);
        }
        echo json_encode($log);
        exit;
    }

    function sendMessage(&$log) {	
        if(count(checkSet(array("name","message","idOne","idTwo"))) == 0) {
            $log['success'] = "false";
            $log['error'] = "missing inputs";
            return;
        }
        $name = $_POST['name'];
        $message = $_POST['message'];
        $idOne = $_POST['idOne'];
        $idTwo = $_POST['idTwo'];
        $fileName = retrieveFileNameByUserIDs($idOne, $idTwo);
        fwrite(fopen("chats/" . $fileName . ".txt", 'a'), "<span>". $name . ": </span>" . ($message = str_replace("\n", " ", $message)) . "\n");
        $log['success'] = "true";
        $log['response'] = "message sent";
    }

    function createChat(&$log) {
        if(count(checkSet(array("idOne","idTwo"))) == 0) {
            missingInputs($log);
            return;
        }
        $idOne = $_POST['idOne'];
        $idTwo = $_POST['idTwo'];

        if(!validChatID($idOne) || !validChatID($idTwo)){
            $log['success'] = "false";
            $log['error'] = "invalid chat id";
            return;
        }

        $fileName = $_POST['fileName'];
        addChatToDatabase($idOne,$idTwo,$fileName);
        $log['success'] = "true";
        $log['response'] = "chat created";
    }

    function createProfile(&$log) {     
        if(count(checkSet(array("firstName","lastName","email","password"))) != 0) {
            $log['success'] = "false";
            $log['error'] = "missing inputs";
            return;
        }
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $id = generateRandomID(15);

        $conn = connectToDatabase("speakeasy");

        $emailQuery =  "SELECT `email` FROM `profiles` WHERE `email` = '$email' LIMIT 1";
        $creationQuery = "INSERT INTO profiles (id,first_name, last_name,email,password,chats) VALUES ('$id','$firstName','$lastName','$email','$password','n/a')";

        if($conn ->query($emailQuery)->num_rows > 0){
            $log['success'] = "false";
            $log['error'] = "Email already registered";
        } else if($conn->query($creationQuery) === FALSE) {
            $log['success'] = "false";
            $log['error'] = "Unknown creation error";
        } else {
                fopen("profiles/".$id."chatlist",'w');
            $log['success'] = "true";
            $log['message'] = "new profile created";
        }
    }

    function retrieveMessages(&$log) {
        if(count(checkSet(array("idOne","idTwo","state"))) == 0) {
            $log['success'] = "false";
            $log['error'] = "missing inputs";
            return;
        }
        $seconds = 0;
        $idOne = $_POST['idOne'];
        $idTwo = $_POST['idTwo'];
        $fileName = "chats/".retrieveFileNameByUserIDs($idOne, $idTwo).".txt";

        while($seconds < 28) {
            $state = $_POST['state'];
            $lines = file($fileName);
            $count = count($lines);
            if($state != $count) {
                $log['success'] = "true";
                $text = array();
                $log['state'] = count($lines);
                foreach ($lines as $line_num => $line) {
                    if($line_num >= $state) {
                        $text[] = $line = str_replace("\n", "", $line);
                    }
                }
                $log['text'] = $text;
                return;
            }
            sleep(1);
            $seconds = $seconds + 1;
        }
        $log['state'] = $state;
        $log['text'] = false;
    }

    function retrieveLastNMessages(&$log) {
        if(count(checkSet(array("chatID","numMessages"))) != 2) {
            missingInputs($log);
            return;
        }

        $chatID = $_POST['chatID'];
        $numMessages = $_POST['numMessages'];
        $fileName = "chats/".retrieveFileNamesByChatID($chatID);

        $count = count($fileName);
        $start = $count - $numMessages;
        $text = array();
        foreach($lines as $line_num => $line) {
            if ($line_num >= $start) {
                $text[] = $line = str_replace("\n", "", $line);
            }
        }

        $log['text'] = $text;
        $log['success'] = "true";
    }

    function connectToChat(&$log) {
        if(count(checkSet(array("emailOne","emailTwo"))) == 0) {
            missingInputs($log);
            return;
        }
        $emailOne = $_POST['emailOne'];
        $emailTwo = $_POST['emailTwo'];
        $queryOne = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$emailOne'" ;
        $queryTwo = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$emailTwo'";
        $conn = connectToDatabase("speakeasy");
        $resultsOne = ($conn->query($queryOne));
        $resultsTwo = ($conn->query($queryTwo));
        if($resultsOne->num_rows > 0 && $resultsTwo->num_rows > 0) {
            $log['success'] = "true";
            $log['idOne'] = $resultsOne->fetch_array()[0];
            $log['idTwo'] = $resultsTwo->fetch_array()[0];
            $log['response'] = "found both emails";
        } else {
            $log['success'] = "false";
            $log['error'] = "profiles not found";
        }
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

    function validChatID($id) {
        $conn = connectToDatabase("speakeasy");
        $query = "SELECT * FROM `profiles` WHERE `id`= $id";
        if(count($conn ->query($query) ->fetch_array()) == 0) {
            return false;
        } else {
            return true;
        }
    }

    function retrieveFileNameByUserIDs($idOne, $idTwo) {
        $conn = connectToDatabase("speakeasy");
        $query = "SELECT `file_name` FROM `chats` WHERE (`user_one` LIKE '$idOne' AND `user_two` LIKE '$idTwo') OR (`user_two` LIKE '$idOne' AND `user_one` LIKE '$idTwo')";
        $results = $conn ->query($query);
        if($results->num_rows > 0) {
            $fileName = $results->fetch_array()[0];
        } else {
            $fileName = generateRandomID(10);
            addChatToDatabase($idOne, $idTwo, $fileName);
            $myfile = fopen("chats/".$fileName.".txt", "w");
        }
        return $fileName;
    }

    function retrieveFileNamesByChatID($chatID) {
        $conn = connectToDatabase("speakeasy");
        $query = "SELECT `file_name` FROM `chats` WHERE `id` LIKE '$chatID'";
    }

    function generateRandomID($length) {
        $id = "";
        for ($x=0; $x<$length; $x++) {
            $number = floor(((float)rand()/(float)getrandmax())*10);
            $id = $id.$number;
        }
        return $id; 
    }

	//TODO add functionality if there are no unmatched users
    function addChatToDatabase($idOne, $idTwo, $fileName) {
        $serverfirst = "localhost";
        $userfirst = "root";
        $password = "";
        $dbfirst = "speakeasy";

        // Create connection
        $conn = new mysqli($serverfirst, $userfirst, $password,$dbfirst);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        $id = generateRandomID(15);
        $sql = "INSERT INTO chats (id,user_one,user_two,file_name) VALUES ('$id','$idOne','$idTwo', '$fileName')";
        if ($conn->query($sql) === TRUE) {
            error_log("Added chat");
        } else {
            error_log("Chat could not be added");
        }
    }

    function getFileName($idOne, $idTwo) {
        $serverfirst = "localhost";
        $userfirst = "root";
        $password = "";
        $dbfirst = "speakeasy";

        // Create connection
        $conn = new mysqli($serverfirst, $userfirst, $password,$dbfirst);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 

        $query = "SELECT `file_name` FROM `chats` WHERE `id_one` LIKE '$idOne' AND `id_two` LIKE '$idTwo'";
        $results = $conn -> query($query) -> fetch_assoc();
        $results["file_name"][0];
        return $results;
    }

    function checkSet($toCheck) {
        $unchecked = array();
        foreach($toCheck as $value) {
            if(!isset($_POST, $value)) { //always evaluates to false? aren't POST and value always set?
                array_push($unchecked, $value);
            }
        }
        return $unchecked;
    }

    function missingInputs(&$log) {
        $log['success'] = "false";
        $log['error'] = "missing inputs";
    }
	
	//Written by Ben
	/**Returns the active topics for the topics page. Sends them as a numeric array, with each index being an
	associative array of the necessary data for each topic**/
	function getActiveTopics(&$log){
		$targetNum = 16; //number of necessary topics for the topics page. Can be modified
		$query = "SELECT `topic_id`,`topic_name`,`description`,`type` FROM `topics_queue` WHERE `active` = TRUE";
		$conn = connectToDatabase();
		$results = ($conn->query($query));
		$activeTopics = [];
		
		if($results->num_rows > $targetNum){
			$log['error'] = "too many topics. truncating extras";
		}
		else if($results->num_rows < $targetNum){
			$log['error'] = "too few topics."
			//need a way to handle this. Pick random topic?
		}
		else{
			$log['success'] = 'topics found and passed';
		}
		
		for($i=0; $i<$results->num_rows; $i++){
			$activeTopics[$i] = $results->fetch_array(MYSQLI_ASSOC);		
		}
		
		return $activeTopics;
	}
?>