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
        echo json_encode($log);
        exit;
    }
}

function executeFunction(&$log, $function) {
    if ($function == "create chat by email") {
        createChatByEmails($log);
    }
}

function prepCreateChatByEmails(&$log) {
    if (count(checkSet(array("emailOne", "emailTwo"))) != 0) {
        missingInputs($log);
        return;
    }
    $email_one = $_POST['emailOne'];
    $email_two = $_POST['emailTwo'];

    createChatByEmails($log, $email_one, $email_two);
}

function createChatByEmails(&$log, $email_one, $email_two) {
    $queryOne = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$email_one'";
    $queryTwo = "SELECT `id` FROM `profiles` WHERE `email` LIKE '$email_two'";

    $conn = connectToDatabase("speakeasy");

    $resultsOne = ($conn->query($queryOne));
    $resultsTwo = ($conn->query($queryTwo));

    if ($resultsOne->num_rows > 0 && $resultsTwo->num_rows > 0) {
        $chat_id = generateUUID();

        $id_one = $resultsOne->fetch_array()[0];
        $id_two = $resultsTwo->fetch_array()[0];

        $conn = connectToDatabase();
        $query = "INSERT INTO chats (id,user_one,user_two) VALUES ('$chat_id','$id_one','$id_two')";
        if ($conn->query($query) === TRUE) {
            $log['success'] = "true";
            $log['response'] = "chat created";
            addChatToUserFile($id_one, $chat_id);
            addChatToUserFile($id_two, $chat_id);

            fwrite(fopen(getChatFilePath($chat_id)), $email_one . " " . $email_two);
        } else {
            $log['success'] = "false";
            $log['error'] = "unable to create chat";
        }
    } else {
        $log['success'] = "false";
        $log['error'] = "profiles not found";
    }
}
