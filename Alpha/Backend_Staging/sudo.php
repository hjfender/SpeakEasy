<?php

include "utils.php";

main();

function main() {
    $log = array();
    if (!isset($_POST['function'])) {
        $log['success'] = "false";
        $log['error'] = "no function given";
    } else {
        $function = $_POST['function'];
        executeFunction($log, $function);
    }
    echo json_encode($log);
    exit;
}

function executeFunction(&$log, $function) {
    if ($function == "create chat by email") {
        prepCreateChatByEmails($log);
    }
}

function prepCreateChatByEmails(&$log) {
    if (count(checkSet(array("emailOne", "emailTwo"), $_POST)) != 0) {
        error_log("Missing inputs");
        missingInputs($log);
        return;
    }
    $email_one = $_POST['emailOne'];
    $email_two = $_POST['emailTwo'];

    createChatByEmails($log, $email_one, $email_two);
}

//Written by Ben
//matches users for chat, and adds user to the chat_queue if no one is waiting to discuss given topic.
function matchUsersForChat(&$log, $topic_id, $user_id){
	$query = "SELECT 'user_id' FROM 'chat_queue' WHERE 'topic_id'=".$topic_id;
	$conn = connectToDatabase();
	
	$result = ($conn->query($query));
	if($result->num_rows > 0){
		$match_id = $result->fetch_array()[0];
		//Given we develop some kind of algorithm, we could add that code here to be more choosy, rather than just choosing the first match
		createChatByIDs($log, $user_id, $match_id);
	}
	else{
		$queue_query = "INSERT INTO 'speakeasy' VALUES(".$topic_id.", ".$user_id.")";
		$queue_result = $conn->query($queue_query);
		if(!$queue_result){
			$log['success'] = "false";
			$log['error'] = "queue update failed";
		}
	}
}

//Written by Ben
//testing function for matchUsersForChat so I don't have to convert ids to emails just to convert back. Written by Ben
function createChatByIDs(&$log, $id_one, $id_two){
		$conn = connectToDatabase();
        $chat_id = generateUUID();

        $query = "SELECT `id` FROM `chats` WHERE (`user_one` = '$id_one' AND `user_two` = '$id_two') OR (`user_one` = '$id_two' AND `user_two` = '$id_one') LIMIT 1";
        if ($conn->query($query)->num_rows != 0) {
            $log['success'] = "false";
            $log['error'] = "chat already exists";
            return;
        }

        $query = "INSERT INTO chats (id,user_one,user_two) VALUES ('$chat_id','$id_one','$id_two')";
        if ($conn->query($query) === TRUE) {
            $log['success'] = "true";
            $log['response'] = "chat created";
            addChatToUserFile($id_one, $chat_id);
            addChatToUserFile($id_two, $chat_id);
            
            $chatMetaData = array();
            $chatMetaData['chatID'] = $chat_id;
            $chatMetaData['userOne'] = $id_one;
            $chatMetaData['userOneName'] = $email_one;
            $chatMetaData['userTwo'] = $id_two;
            $chatMetaData['userTwoName'] = $email_two;
            $chatMetaData['count'] = 0;
            $chatFile = fopen(getChatFilePath($chat_id),'w');
            if($chatFile === FALSE){
                error_log("ERROR");
            }
            //With a buffer to avoid overriding next line in the future. 
            fwrite($chatFile, json_encode($chatMetaData) . "      ");
        } else {
            $log['success'] = "false";
            $log['error'] = "unable to create chat";
        } 
}

function createChatByEmails(&$log, $email_one, $email_two) {
    $queryOne = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$email_one'";
    $queryTwo = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$email_two'";

    $conn = connectToDatabase();

    $resultsOne = ($conn->query($queryOne));
    $resultsTwo = ($conn->query($queryTwo));

    if ($resultsOne->num_rows > 0 && $resultsTwo->num_rows > 0) {
        $chat_id = generateUUID();

        $id_one = $resultsOne->fetch_array()[0];
        $id_two = $resultsTwo->fetch_array()[0];

        $query = "SELECT `id` FROM `chats` WHERE (`user_one` = '$id_one' AND `user_two` = '$id_two') OR (`user_one` = '$id_two' AND `user_two` = '$id_one') LIMIT 1";
        if ($conn->query($query)->num_rows != 0) {
            $log['success'] = "false";
            $log['error'] = "chat already exists";
            return;
        }

        $query = "INSERT INTO chats (id,user_one,user_two) VALUES ('$chat_id','$id_one','$id_two')";
        if ($conn->query($query) === TRUE) {
            $log['success'] = "true";
            $log['response'] = "chat created";
            addChatToUserFile($id_one, $chat_id);
            addChatToUserFile($id_two, $chat_id);
            
            $chatMetaData = array();
            $chatMetaData['chatID'] = $chat_id;
            $chatMetaData['userOne'] = $id_one;
            $chatMetaData['userOneName'] = $email_one;
            $chatMetaData['userTwo'] = $id_two;
            $chatMetaData['userTwoName'] = $email_two;
            $chatMetaData['count'] = 0;
            $chatFile = fopen(getChatFilePath($chat_id),'w');
            if($chatFile === FALSE){
                error_log("ERROR");
            }
            //With a buffer to avoid overriding next line in the future. 
            fwrite($chatFile, json_encode($chatMetaData) . "      ");
        } else {
            $log['success'] = "false";
            $log['error'] = "unable to create chat";
        }
    } else {
        $log['success'] = "false";
        $log['error'] = "profile(s) not found";
    }
}
