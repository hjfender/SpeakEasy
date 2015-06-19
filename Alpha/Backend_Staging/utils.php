<?php

/**
 * Generate v4 UUID without dashes
 * 
 * Version 4 UUIDs are pseudo-random.
 */
function generateUUID() {
    return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Connect to the database
 * @param string $database
 * @return \mysqli connections
 */
function connectToDatabase($database = "speakeasy") {
    $serverip = "localhost";
    $username = "root";
    $password = "";

    // Create connection
    $conn = new mysqli($serverip, $username, $password, $database);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

/**
 * Gets the IP Address of the user
 * @return string Client's IP Address
 */
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function getUserChatsFilePath($user_id) {
    return "userchats/" . $user_id . ".chats";
}

function getChatFilePath($chat_id) {
    return "chats/" . $chat_id . ".chat";
}

function addChatToUserFile($user_id, $chat_id) {
    $fileLoc = getUserChatsFilePath($user_id);
    fwrite(fopen($fileLoc, 'a'), $chat_id . "\n");
}

/**
 * Checks to make sure array variables are set
 * @param array $toCheck The Array to check set
 * @return array Of unset variables
 */
function checkSet($toCheck,$array) {
    $unchecked = array();
    foreach ($toCheck as $value) {
        if (!isset($array, $value))  {
            error_log("ERROR");
            array_push($unchecked, $value);
        }
    }
    return $unchecked;
}
