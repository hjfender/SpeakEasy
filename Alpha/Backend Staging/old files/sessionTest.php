<?php
    session_start();
    if(!isset($_GET['function'])) {
        error_log("no function set");
        exit();
    }
    $log = array();
    if($_GET['function'] == "set") {
        $_SESSION['color'] = "Blue";
        $_SESSION['cat'] = "Fred";
        
    } else {
        $log = array();
        $log['cat'] = $_SESSION['cat'];
        $log['color'] = $_SESSION['color'];
    }
    echo json_encode($log);
?>

