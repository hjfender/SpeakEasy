<?php

include "utils.php";
error_reporting(E_ALL & ~E_NOTICE);

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
        error_log("Unable to Authenticate ~ unable to find token " . $token);
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
    if ($command == 'chat:send:message') {
        prepSendMessage($log, $user_id);
    } else if ($command == 'chat:retrieve:last') {
        prepRetrieveLastNMessages($log, $user_id);
    } else if ($command == 'profile:chats:list') {
        getUserChats($log, $user_id);
    } else if ($command == 'chat:retrieve:new') {
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
    $count = incrementChatCount($chatFilePath);

    //Add Message to file
    $now = date("Y-m-d H:i:s");
    $messageData = array();
    $messageData['index'] = $count - 1;
    $messageData['sender'] = $user_id;
    $messageData['sent'] = $now;
    $messageData['message'] = $message;
    $chat = fopen($chatFilePath, 'a');
    fwrite($chat, json_encode($messageData) . "\n");
    fclose($chat);

    //Response
    $log['success'] = "true";
    $log['response'] = "message sent";
    $log['index'] = $messageData['index'];
    
    }

/**
 * Updates the count variable of the chat metadata. 
 * @param String $chatFilePath File path of the chat to increment. 
 */
function incrementChatCount($chatFilePath, $amount = 1) {
    //Get Chat Data
    $chat = fopen($chatFilePath, 'r');
    $firstLine = fgets($chat);
    $chatData = json_decode($firstLine, TRUE);
    fclose($chat);

    //Increment count
    $chatData['count'] = $chatData['count'] + $amount;


    //Write data to first line
    $chat = fopen($chatFilePath, 'r+');
    fwrite($chat, json_encode($chatData));
    fclose($chat);
    return $chatData['count'];
}

//</editor-fold>
// <editor-fold defaultstate="collapsed" desc="Retrieve Range of Messages">
function prepRetrieveMessageRange(&$log, $user_id) {
    if (count(checkset(array("chatID", "begin", "end"), $_POST)) != 0) {
        missingInputs($log);
        return;
    }
    $chat_id = $_POST['chatID'];
    $begin = $_POST['begin'];
    $end = $_POST['end'];
    retrieveMessageRange($log, $user_id, $chat_id, $begin, $end);
}

function retrieveMessageRange(&$log, $user_id, $chat_id, $begin, $end) {
    if (!isValidRange($begin, $end)) {
        $log['success'] = "false";
        $log['error'] = "invalid range";
        return;
    }
    $chat = getChatById($chat_id);
    //Unable to find chat with given ID
    if ($chat === FALSE) {
        $log['success'] = "false";
        $log['error'] = "invalid chat id";
        return;
    }
    //User assoiated with token not part of given chat
    if (!($chat['userOneID'] == $user_id || $chat['userTwoID'] == $user_id)) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    }
    $log['success'] = "true";

    $fileLoc = getChatFilePath($chat_id);
    $chatMetaData = getChatMetaData($chat_id);
    $count = $chatMetaData['count'];
    if ($begin > $count) {
        $log['response'] = "too few messages";
        $log['messages'] = "false";
        return;
    }
    if ($end > $count) {
        $end = $count;
    }

    $names = getNamesFromChatMetaData($chatMetaData);
    $chatFile = fopen($fileLoc, "r");
    $messages = array();
    fgets($chatFile); //Skip metadata
    $line_num = 0;
    while (($line = fgets($chatFile) !== FALSE)) {
        if ($line_num >= $begin && $line_num <= $end) {
            $messageArray = json_decode($line, TRUE);
            $messageArray['sender'] = $names[$messageArray['sender']]; //Replace id with name in chat
            $messages[] = json_encode($messageArray);
        }
        $line_num = $line_num + 1;
    }

    $log['messages'] = $messages;
    $log['response'] = "retrieved messages";
}

/**
 * True ifthe range is valid and not less than zero
 * @param type $begin begining of range
 * @param type $end End of range
 * @return boolean 
 */
function isValidRange($begin, $end) {
    if ($end < $begin) {
        return FALSE;
    } else if ($end < 0 || $begin < 0) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * Gets names for the users in the chat in an associative array
 * @param Array $chatMetaData MetaData for a chat
 * @return Array Associative array with userID matched with name in chat
 */
function getNamesFromChatMetaData($chatMetaData) {
    $names = array();
    $names[$chatMetaData['userOne']] = $chatMetaData['userOneName'];
    $names[$chatMetaData['userTwo']] = $chatMetaData['userTwoName'];
    return $names;
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
    //Unable to find chat with given ID
    if ($chat === FALSE) {
        $log['success'] = "false";
        $log['error'] = "invalid chat id";
        return;
    }
    //User associated with token not part of given chat
    if (!($chat['userOneID'] == $user_id || $chat['userTwoID'] == $user_id)) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    }
    $log['success'] = "true";
    $fileLoc = getChatFilePath($chat_id);
    $seconds = 0;
    while ($seconds < 28) {
        //Get the metadata to know how many messages snet
        $chatFile = fopen($fileLoc, 'r');
        $chatData = json_decode(fgets($chatFile), TRUE); //Decode chat metadata
        $count = $chatData['count'];

        //If true, new message sent since sent query
        if ($state < $count) {
            
            $names = array();
            $names[$chatData['userOne']] = $chatData['userOneName'];
            $names[$chatData['userTwo']] = $chatData['userTwoName'];

            $messages = array();
            $line_num = 1;
            //Get only but all new messages
            while (($line = fgets($chatFile)) !== FALSE) {
                if ($line_num > $state) {
                    $messageData = json_decode($line, TRUE);
                    $messageData['sender'] = $names[$messageData['sender']];
                    $messages[] = json_encode($messageData);
                }
                $line_num = $line_num + 1;
            }
            $log['response'] = "new messages found";
            $log['state'] = $count;
            $log['messages'] = $messages;
            return;
        }
        fclose($chatFile);
        sleep(1);
        $seconds = $seconds + 1;
    }
    $log['state'] = $state;
    $log['response'] = "no new messgaes";
    $log['messages'] = "false";
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

   /* if (!userHasAccessToChat($user_id, $chat_id)) {
        $log['success'] = "false";
        $log['error'] = "no access";
        return;
    } */
    retrieveLastNMessages($log, $chat_id, $num_messages);
}

function retrieveLastNMessages(&$log, $chat_id, $num_messages) {
    $chatData = getChatMetaData($chat_id);
    $fileName = getChatFilePath($chat_id);

    $names = getNamesFromChatMetaData($chatData);
    $start = $chatData['count'] - $num_messages;
    $messages = array();

    $chatFile = fopen($fileName, "r");
    fgets($chatFile); //Skip Metadata
    $line_num = 0;
    while (($line = fgets($chatFile)) !== FALSE) {
        if ($line_num >= $start) {
            $messageData = json_decode($line, TRUE);
            $messageData['sender'] = $names[$messageData['sender']];
            $messages[] = json_encode($messageData);
        }
        $line_num = $line_num + 1;
    }
    $log['response'] = "able to retrieve messages";
    $log['messages'] = $messages;
    $log['success'] = "true";
}

//</editor-fold>
// <editor-fold defaultstate="collapsed" desc="Get User Chats">
/**
 * Gets a list of the chats a user is a part of 
 * @param type $log 
 * @param type $user_id UUID of the user
 */
function getUserChats(&$log, $user_id) {
    $chat_ids = array();
    $query = "SELECT `id` FROM `chats` WHERE `user_one` = '$user_id' OR `user_two` = '$user_id'";
    $conn = connectToDatabase();
    $results = $conn->query($query);
    $conn->close();
    
    while($row = $results->fetch_assoc()) {
        $chat_ids[] = $row['id'];
    }
    
    $log['chatIDs'] = $chat_ids;
    $log['success'] = "true";
    $log['response'] = "retrieved chat ids";
}
// </editor-fold>

/**
 * Retrieves the chat data for the given id. See backend notes for more info
 * @param string $chat_id UUID of the chat
 * @return multiple Associative array of chat data or FALSE if no chat
 */
function getChatMetaData($chat_id) {
    $fileLoc = getChatFilePath($chat_id);
    $chatFile = fopen($fileLoc, 'r');
    if ($chatFile === FALSE) {
        return FALSE;
    }
    $firstLine = fgets($chatFile);
    fclose($chatFile);
    return json_decode($firstLine, TRUE);
}

//</editor-fold>
// <editor-fold defaultstate="collapsed" desc="Misc Utility Functions">
function userHasAccessToChat($user_id, $chat_id) {
    $chatFileName = getUserChatsFilePath($user_id);
    $chatFile = fopen($chatFileName, 'r');
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

function missingInputs(&$log) {
    $log['success'] = "false";
    $log['error'] = "missing inputs";
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

// </editor-fold>

main();
