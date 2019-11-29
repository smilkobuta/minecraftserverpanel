<?php

function h($str) {
    return htmlspecialchars($str);
}

function add_session_message($message) {
    $_SESSION['message'] = $message;
}

function add_session_error_message($error_message) {
    $_SESSION['error_message'] = $error_message;
}

function get_session_message() {
    if ($_SESSION['message'] ?? null) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    };
    return false;
}

function get_session_error_message() {
    if ($_SESSION['error_message'] ?? null) {
        $error_message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $error_message;
    };
    return false;
}

?>