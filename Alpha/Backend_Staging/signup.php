<?php

include 'utils.php';

echo "Signing up...";
if(!(isset($_POST['first_name']) || isset($_POST['last_name']) || isset($_POST['email']) || isset($_POST['password']))) {
    echo "Missing input";
    exit();
}

//Get inputs
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$email = $_POST['email'];
$password = $_POST['password'];

//Connect to database
$conn = connectToDatabase();

//Check to see if email already registered
$query = "SELECT `email` FROM `profiles` WHERE `email` = '$email' LIMIT 1";
if($conn -> query($query) -> num_rows != 0) {
    echo "<br/>Email already registered";
    exit();
}

//Verify password integrity
if(strlen($password) < 6) {
    echo "<br/>Password must be at least 6 characters long";
    exit();
}

//Insert new profile into database
$uuid = "";
$foundUnique = FALSE;
while(!$foundUnique) {
    $uuid = generateUUID();
    $query = "SELECT `id` FROM `profiles` WHERE `id` = '$uuid' LIMIT 1";
    if($conn -> query($query) -> num_rows == 0) {
        $foundUnique = TRUE;
    }
}

$query = "INSERT INTO `profiles` (`id`, `first_name`, `last_name`, `email`, `password`) VALUES ('$uuid','$firstName','$lastName','$email','$password')";
if($conn -> query($query)) {
    fwrite(fopen(getUserChatsFilePath($uuid), 'a'),"");
    echo "<br/>Profile creation successful";
} else {
    echo "<br/>Unknown creation error";
}

