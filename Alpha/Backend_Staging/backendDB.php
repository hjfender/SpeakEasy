<?php

include "utils.php";

// <editor-fold defaultstate="collapsed" desc="Main Code">
/**
 * Creates Log array to return to with request. Authenticates user. Creates
 * Response from Log array
 */
function main() {
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
}

/**
 * Authenticates user. Retireves ID of user from token
 * @param string $token Token provided with request.
 * @param array $log Log file to return to sender
 * @return boolean Returns FALSE if unable to authenticate, otherwise returns 
 * user ID of associated with token
 */
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
        $updated->add(new DateInterval("PT15M"));
        $now = new DateTime();
        if ($now > $updated) {
            //Handle timeout
        }
        return $array[0];
    }
}

/**
 * Executes the command requested by the frontend
 * @param type $command Command to execute
 * @param type $user_id ID of current user, retrieved using token
 * @param type $log Array for response to javascript
 */
function executeFunction($command, $user_id, &$log) {
    if ($command == 'send message') {
        prepSendMessage($log, $user_id);
    } else if ($command == 'last n messages') {
        prepRetrieveLastNMessages($log, $user_id);
    } else if ($command == 'retrive chat ids') {
        getUserChats($log, $user_id);
    } else if ($command == 'new messages') {
        prepRetrieveNewMessages($log, $user_id);
    } else {
        $log['success'] = "false";
        $log['error'] = "invalid function";
    }
}

//</editor-fold>
// <editor-fold defaultstate="collapsed" desc="Executable Functions">
// <editor-fold defaultstate="collapsed" desc="Create Chat From IDs">
/**
 * Gets POST data from Ajax request to create chat for given users
 * @param type $log Log for Javascript response
 */
function prepCreateChat(&$log) {
    if (count(checkSet(array("idOne", "idTwo"), $_POST)) != 0) {
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
        $chatMetaData = "{chatid:'" . $uuid . "', userOneID:'" . $user_id_one . "', userOneName:'user one', userTwoID:'" . $user_id_two + "', userTwoName:'user two', count:0}";
        fwrite(fopen("chats/" . $uuid . ".txt", 'a'), $chatMetaData);
        addChatToUserFile($user_id_one, $uuid);
        addChatToUserFile($user_id_two, $uuid);
        return TRUE;
    } else {
        $log['success'] = "false";
        $log['error'] = "unknown creation error";
        return FALSE;
    }
}

//</editor-fold>
// <editor-fold defaultstate="collapsed" desc="Send Message">

/**
 * Fetches POST to send message
 * @param array $log Log for Javascript response
 * @param string $user_id ID of authenticated user
 */
function prepSendMessage(&$log, $user_id) {
    if (count(checkSet(array("message", "chatID"), $_POST)) != 0) {
        $log['success'] = "false";
        $log['error'] = "missing inputs";
        return;
    }
    $message = $_POST['message'];
    $chat_id = $_POST['chatID'];
    sendMessage($log, $user_id, $chat_id, $message);
}

function sendMessage(&$log, $user_id, $chat_id, $message) {
    $chatInfo = getChatByID($chat_id);
    //No chat by given ID
    if ($chatInfo === FALSE) {
        $log['success'] = "false";
        $log['error'] = "invalid chat id";
        return;
    }
    //User not part of given chat ID
    if (!($chatInfo['userOneID'] == $user_id || $chatInfo['userTwoID'] == $user_id)) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    }
    $chatFilePath = getChatFilePath($chat_id);
    
    //Update chat meta data
    incrementChatCount($chatFilePath);

    //Add Message to file
    $now = date("Y-m-d H:i:s");
    $messageData = "{sender:'" . $user_id . "', sent:'" . $now . "', message:'" . $message . "'}";
    $chat = fopen($chatFilePath, 'a');
    fwrite($chat, $messageData . "\n");
    fclose($chat);
    
    //Response
    $log['success'] = "true";
    $log['response'] = "message sent";
}
/**
 * Updates the count variable of the chat metadata. 
 * @param String $chatFilePath File path of the chat to increment. 
 */
function incrementChatCount($chatFilePath) {
    //Get Chat Data
    $chat = fopen($chatFilePath, 'r');
    $firstLine = fgets($chat);
    error_log("First Line:" .$firstLine);
    $chatData = json_decode($firstLine,TRUE);
    fclose($chat);
    
    //Increment count
    $chatData['count'] = $chatData['count'] + 1;
   
    
    //Write data to first line
    $chat = fopen($chatFilePath, 'r+');
    fwrite($chat, json_encode($chatData));
    fclose($chat);
}

//</editor-fold>
// <editor-fold defaultstate="collapsed" desc="Retrieve New Messages">
function prepRetrieveNewMessages(&$log, $user_id) {
    if (count(checkSet(array("chatID", "state"), $_POST)) != 0) {
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
    $fileLoc = getChatFilePath($chat_id);
    $seconds = 0;
    while ($seconds < 28) {
        $chatFile = fopen($fileLoc, 'r');
        $chatData = json_decode(fgets($chatFile)); //Decode chat metadata
        $count = $chatData['count'];
        if ($state < $count) {
            $log['success'] = "true";
            $text = array();
            $line_num = 1;
            while (($line = fgets($chatFile)) !== FALSE) {
                if ($line_num >= $state) {
                    $text[] = $line = str_replace("\r", "", str_replace("\n", "", $line));
                }
                $line_num = $line_num + 1;
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
    if (count(checkSet(array("chatID", "numMessages"), $_POST)) != 0) {
        missingInputs($log);
        return;
    }

    $chat_id = $_POST['chatID'];
    $num_messages = $_POST['numMessages'];

    if (!userHasAccessToChat($user_id, $chat_id)) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    }
    retrieveLastNMessages($log, $chat_id, $num_messages);
}

function retrieveLastNMessages(&$log, $chat_id, $num_messages) {
    $fileName = getChatFilePath($chat_id);
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
    $fileLoc = getUserChatsFilePath($user_id);
    $file = file($fileLoc);
    foreach ($file as $line_num => $line) {
        $chat_ids[] = $line = str_replace("\r", "", str_replace("\n", "", $line));
    }
    $log['chatIDs'] = $chat_ids;
    $log['success'] = "true";
    $log['response'] = "retrieved chat ids";
}

//</editor-fold>
// <editor-fold defaultstate="collapsed" desc="Misc Utility Functions">
function userHasAccessToChat($user_id, $chat_id) {
    $chatFileName = getUserChatsFilePath($user_id);
    $chatFile = openf($chatFileName, 'r');
    while (feof($chatFile)) {
        $line = str_replace("\n", "", fgets($chatFile));
        if ($line === $chat_id) {
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
    return $chat;
}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Old Code">
function connectToChat(&$log) {
    if (count(checkSet(array("emailOne", "emailTwo"), $_POST)) != 0) {
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

function missingInputs(&$log) {
    $log['success'] = "false";
    $log['error'] = "missing inputs";
}

function getFileLocation($fileName) {
    return "chats/" . $fileName . ".txt";
}

// </editor-fold>

main();
