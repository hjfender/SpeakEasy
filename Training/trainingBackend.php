<?php

function main() {
    $log = array();
    if (!isset($_POST['function'])) {
        $log['success'] = false;
        $log['error'] = "You didn't provide a function";
    } else {
        $function = $_POST['function'];
        executeFunction($function, $log);
    }

    echo json_encode($log);
}

function executeFunction($function, &$log) {
    if ($function === "my first request") {
        myFirstRequest($log);
    } else if ($function === "request with input") {
        requestWithInput($log);
    } else if ($function === "add") {
        addRequest($log);
    } else {
        $log['success'] = false;
        $log['error'] = "That's an invalid function";
    }
}

function myFirstRequest(&$log) {
    $log['success'] = true;
    $log['response'] = "Good job!";
}

function requestWithInput(&$log) {
    if (!isset($_POST['name'])) {
        $log['success'] = false;
        $log['error'] = "You need to provide your name";
        return;
    }
    $log['success'] = true;
    $log['response'] = "Hello " . $_POST['name'] . "! I'm glad you're here! :D";
    return;
}

function addRequest(&$log) {
    if (!(isset($_POST['firstNumber']) && isset($_POST['secondNumber']))) {
        $log['success'] = false;
        $log['error'] = "You must provide two numbers";
        return;
    }
    $log['success'] = true;
    $log['response'] = "I added those numbers for ya!";
    $log['equals'] = $_POST['firstNumber'] + $_POST['secondNumber'];
    return;
}

main();
