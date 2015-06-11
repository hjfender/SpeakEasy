<?php

include "utils.php";

$log = array();
if (!isset($_POST['token'])) {
    $log['success'] = "false";
    $log['error'] = "no authentication token given";
    error_log("Unable to Authenticate2");
} else if (!isset($_POST['function'])) {
    $log['success'] = "false";
    $log['error'] = "no function given";
} else {
    $token = $_POST['token'];
    $user_id = authenticate($token, $log);
    if ($user_id === FALSE) {
        $log['success'] = "false";
        $log['error'] = "invalid token";
    } else {
        $function = $_POST['function'];
        executeFunction($function, $user_id, $log);
    }
    echo json_encode($log);
    exit;
}


/**
 * Executes the command requested by the frontend
 * @param type $command Command to execute
 * @param type $user_id ID of current user, retrieved using token
 * @param type $log Array for response to javascript
 */
function executeFunction($command, $user_id, &$log) {
    if ($command == 'send') {
        sendMessage($log, $user_id);
    } else if ($command == 'createChat') {
        createChatByEmails($log);
    } else if ($command == 'retrieveLastN') {
        prepRetrieveLastNMessages($log,$user_id);
    } else if ($command == 'connect') {
        error_log("Connecting to chat");
        connectToChat($log);
    } else if ($command == 'getChatIDs') {
        getUserChats($log, $user_id);
    } else if ($command == 'getNewMessages') {
        prepRetrieveNewMessages($log, $user_id);
    }
}

function authenticate($token, &$log) {
    $ipAddress = getClientIP();
    $query = "SELECT `user_id`,`updated` FROM `sessions` WHERE `token` = '$token' AND `ip_address` = '$ipAddress' LIMIT 1";
    $conn = connectToDatabase();
    $results = $conn->query($query);
    if ($results->num_rows === 0) {
        error_log("Unable to Authenticate2" . $token);
        return FALSE;
    } else {
        $array = $results->fetch_array();
        $updated = new DateTime($array[1]);
        $now = new DateTime();
        $updated->add(new DateInterval("PT15M"));
        if ($now > $updated) {
            //Handle timeout
        }
        return $array[0];
    }
}

/**
 * Fetches POST to send message
 * @param array $log Log for Javascript response
 * @param string $user_id ID of authenticated user
 */
function prepSendMessage(&$log, $user_id) {
    if (count(checkSet(array("message", "chatID"))) == 0) {
        $log['success'] = "false";
        $log['error'] = "missing inputs";
        return;
    }
    $message = $_POST['message'];
    $chat_id = $_POST['chatID'];
    sendMessage($log, $user_id, $chat_id, $message);
}

function sendMessage(&$log, $user_id, $chat_id, $message) {
    $chat = getChatByID($chat_id);
    if ($chat === FALSE) {
        $log['success'] = "false";
        $log['error'] = "invalid chat id";
        return;
    }
    if (!($chat['userOne'] == $user_id || $chat['userTwo'] == $user_id)) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    }
    $fileName = $chat['fileName'];
    fwrite(fopen("chats/" . $fileName . ".txt", 'a'), "<span>" . $chat_id . ": </span>" . (str_replace("\n", " ", $message)) . "\n");
    $log['success'] = "true";
    $log['response'] = "message sent";
}

// <editor-fold defaultstate="collapsed" desc="Executable Functions">
// <editor-fold defaultstate="collapsed" desc="Create Chat From IDs">
/**
 * Gets POST data from Ajax request to create chat for given users
 * @param type $log Log for Javascript response
 */
function prepCreateChat(&$log) {
    if (count(checkSet(array("idOne", "idTwo"))) == 0) {
        missingInputs($log);
        return;
    }
    $user_id_one = $_POST['idOne'];
    $user_id_two = $_POST['idTwo'];

    if (!validUserID($user_id_one) || !validUserID($user_id_two)) {
        $log['success'] = "false";
        $log['error'] = "invalid user id";
        return;
    }
    createChat($log, $user_id_one, $user_id_two);
}

/**
 * Creates a chat for the two users. Returns FALSE if users already chatting or 
 * unable to add chat to the database
 * @param type $log Log for Javascript response
 * @param type $user_id_one ID of one of the users
 * @param type $user_id_two ID of the other user
 * @return boolean If chat was created successfully
 */
function createChat(&$log, $user_id_one, $user_id_two) {
    $selectQuery = "SELECT `id` FROM `chats` WHERE (`user_one` = '$user_id_one' AND `user_two` = '$user_id_two') OR (`user_one` = '$user_id_two' AND `user_two` = '$user_id_one')";
    $conn = connectToDatabase();
    if ($conn->query($selectQuery)->num_rows != 0) {
        $log['success'] = "FALSE";
        $log['error'] = "chat already exists";
        return FALSE;
    }
    $uuid = generateUUID();
    $insertQuery = "INSERT INTO `chats` (id, user_one, user_two, file_name) VALUES ('$uuid','$user_id_one','$user_id_two',$uuid')";
    if ($conn->query($insertQuery)) {
        $log['success'] = "true";
        $log['response'] = "chat created";
        fwrite(fopen("chats/" . $uuid . ".txt", 'a'), $emailOne . " " . $emailTwo);
        $addChatToUserFile($user_id_one, $uuid);
        $addChatToUserFile($user_id_two, $uuid);
        return TRUE;
    } else {
        $log['success'] = "false";
        $log['error'] = "unknown creation error";
        return FALSE;
    }
}


function addChatToUserFile($user_id, $chat_id) {
    $fileLoc = "userchats/" . $user_id . ".chats";
    fwrite(fopen($fileLoc, 'a'), $chat_id . "\n");
}
//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="Retrieve New Messages">
function prepRetrieveNewMessages(&$log, $user_id) {
    if (count(checkSet(array("chatID", "state"))) != 0) {
        $log['success'] = "false";
        $log['error'] = "missing inputs";
        return;
    }
    $chat_id = $_POST['chatID'];
    $state = $_POST['state'];
    retrieveNewMessages($log, $user_id, $chat_id, $state);
}

function retrieveNewMessages(&$log, $user_id, $chat_id, $state) {
    $chat = getChatByID($chat_id);
    if ($chat === FALSE) {
        $log['success'] = "false";
        $log['error'] = "invalid chat id";
        return;
    }
    if (!($chat['userOneID'] == $user_id) || $chat['userTwoID'] == $user_id) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    }
    $fileLoc = getFileLocation($chat['fileName']);
    $seconds = 0;
    while ($seconds < 28) {
        $lines = file($fileLoc);
        $count = count($lines);
        if ($state < $count) {
            $log['success'] = "true";
            $text = array();
            foreach ($lines as $line_num => $line) {
                if ($line_num >= $state) {
                    $text[] = $line = str_replace("\n", "", $line);
                }
            }
            $log['state'] = $count;
            $log['text'] = $text;
            return;
        }
        sleep(1);
        $seconds = $seconds + 1;
    }
    $log['state'] = $state;
    $log['text'] = "false";
    $log['success'] = "true";
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Get Last N Messages">
function prepRetrieveLastNMessages(&$log, $user_id) {
    if (count(checkSet(array("chatID", "numMessages"))) != 0) {
        missingInputs($log);
        return;
    }
    
    $chat_id = $_POST['chatID'];
    $num_messages = $_POST['numMessages'];
    
    if(!userHasAccessToChat($user_id, $chat_id)) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    } 
    retrieveLastNMessages($log, $chat_id, $num_messages);
}

function retrieveLastNMessages(&$log, $chat_id, $num_messages) {
    $fileName = getChatFileName($chat_id);
    $chat = open($fileName);
    $start = count($chat) - $num_messages;
    $text = array();
    foreach ($chat as $line_num => $line) {
        if ($line_num >= $start) {
            $text[] = $line = str_replace("\n", "", $line);
        }
    }
    $log['text'] = $text;
    $log['success'] = "true";
}
//</editor-fold>

function getUserChats(&$log, $user_id) {
    $chat_ids = array();
    $fileLoc = "userchats/" . $user_id . ".chats";
    $file = file($fileLoc);
    foreach ($file as $line_num => $line) {
        $chat_ids[] = $line = str_replace("\n", "", $line);
    }
    $log['chatIDs'] = $chat_ids;
    $log['success'] = "true";
    $log['response'] = "retrieved chat ids";
}
//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="Misc Utility Functions">
function userHasAccessToChat($user_id, $chat_id) {
    $chatFileName = getUserChatsFileName($user_id);
    $chatFile = openf($chatFileName,'r');
    while(feof($chatFile)) {
        $line = str_replace("\n", "", fgets($chatFile));
        if($line === $chat_id){
            return TRUE;
        }
    }
    return FALSE;
}

/**
 * Fetches the chat data from the database based on chat ID
 * @param string $chat_id ID of chat
 * @return FALSE if no chat exists for that ID, otherwise array with chat data
 */
function getChatByID($chat_id) {
    $query = "SELECT * FROM `chats` WHERE `id` = '$chat_id'";
    $conn = connectToDatabase();
    $results = $conn->query($query);
    if ($results->num_rows == 0) {
        return FALSE;
    }
    $chat = array();
    $results_array = $results->fetch_array();
    $chat['chatID'] = $results_array[0];
    $chat['userOneID'] = $results_array[1];
    $chat['userTwoID'] = $results_array[2];
    $chat['fileName'] = $results_array[3];
    return $chat;
}

function getUserChatsFileName($user_id) {
    return "userchats/".$user_id.".chats";
}

function getChatFileName($chat_id) {
    return "chats/".$chat_id.".txt";
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Old Code">
function connectToChat(&$log) {
    if (count(checkSet(array("emailOne", "emailTwo"))) != 0) {
        missingInputs($log);
        return;
    }
    $emailOne = $_POST['emailOne'];
    $emailTwo = $_POST['emailTwo'];
    $queryOne = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$emailOne'";
    $queryTwo = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$emailTwo'";
    $conn = connectToDatabase("speakeasy");
    $resultsOne = ($conn->query($queryOne));
    $resultsTwo = ($conn->query($queryTwo));
    if ($resultsOne->num_rows > 0 && $resultsTwo->num_rows > 0) {
        $log['success'] = "true";
        $log['idOne'] = $resultsOne->fetch_array()[0];
        $log['idTwo'] = $resultsTwo->fetch_array()[0];
        $log['response'] = "found both emails";
    } else {
        $log['success'] = "false";
        $log['error'] = "profiles not found";
    }
}

function createChatByEmails(&$log) {
    if (count(checkSet(array("emailOne", "emailTwo"))) != 0) {
        missingInputs($log);
        return;
    }
    $emailOne = $_POST['emailOne'];
    $emailTwo = $_POST['emailTwo'];
    $queryOne = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$emailOne'";
    $queryTwo = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$emailTwo'";
    $conn = connectToDatabase("speakeasy");
    $resultsOne = ($conn->query($queryOne));
    $resultsTwo = ($conn->query($queryTwo));
    if ($resultsOne->num_rows > 0 && $resultsTwo->num_rows > 0) {
        $log['success'] = "true";
        $fileName = generateUUID();
        $idOne = $resultsOne->fetch_array()[0];
        $idTwo = $resultsTwo->fetch_array()[0];
        addChatToDatabase($idOne, $idTwo, $fileName);
        fwrite(fopen("chats/" . $fileName . ".txt", 'a'), $emailOne . " " . $emailTwo);
    } else {
        $log['success'] = "false";
        $log['error'] = "profiles not found";
    }
}

function validUserID($id) {
    $conn = connectToDatabase("speakeasy");
    $query = "SELECT * FROM `profiles` WHERE `id`= $id LIMIT 1";
    if (count($conn->query($query)->fetch_array()) == 0) {
        return false;
    } else {
        return true;
    }
}

function retrieveFileNameByUserIDs($idOne, $idTwo) {
    $conn = connectToDatabase("speakeasy");
    $query = "SELECT `file_name` FROM `chats` WHERE (`user_one` LIKE '$idOne' AND `user_two` LIKE '$idTwo') OR (`user_two` LIKE '$idOne' AND `user_one` LIKE '$idTwo')";
    $results = $conn->query($query);
    if ($results->num_rows > 0) {
        $fileName = $results->fetch_array()[0];
    } else {
        $fileName = generateRandomID(10);
        addChatToDatabase($idOne, $idTwo, $fileName);
        $myfile = fopen("chats/" . $fileName . ".txt", "w");
    }
    return $fileName;
}

function retrieveFileNamesByChatID($chatID) {
    $conn = connectToDatabase("speakeasy");
    $query = "SELECT `file_name` FROM `chats` WHERE `id` LIKE '$chatID'";
}

function addChatToDatabase($idOne, $idTwo, $fileName) {
    $serverfirst = "localhost";
    $userfirst = "root";
    $password = "";
    $dbfirst = "speakeasy";

    // Create connection
    $conn = new mysqli($serverfirst, $userfirst, $password, $dbfirst);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $id = $fileName;
    $sql = "INSERT INTO chats (id,user_one,user_two,file_name) VALUES ('$id','$idOne','$idTwo', '$fileName')";
    if ($conn->query($sql) === TRUE) {
        error_log("Added chat");
        addChatToUserFile($idOne, $id);
        addChatToUserFile($idTwo, $id);
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
    $conn = new mysqli($serverfirst, $userfirst, $password, $dbfirst);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT `file_name` FROM `chats` WHERE `id_one` LIKE '$idOne' AND `id_two` LIKE '$idTwo'";
    $results = $conn->query($query)->fetch_assoc();
    $results["file_name"][0];
    return $results;
}

/**
 * Checks to make sure array variables are set
 * @param array $toCheck The Array to check set
 * @return array Of unset variables
 */
function checkSet($toCheck) {
    $unchecked = array();
    foreach ($toCheck as $value) {
        if (!isset($_POST, $value)) {
            array_push($unchecked, $value);
        }
    }
    return $unchecked;
}

function missingInputs(&$log) {
    $log['success'] = "false";
    $log['error'] = "missing inputs";
}

function getFileLocation($fileName) {
    return "chats/" . $fileName . ".txt";
}
// </editor-fold>

?>