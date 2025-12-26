<?php

// Initialize the session
session_start();

function isUserLoggedIn() {
    if (!isset($config)){
        $config = new config();
    }
    // Check if the user is logged in, if not then redirect to login page
    if (!isset($_SESSION['google_loggedin'])) {
        header('Location: '.$config->loginpageRedirectRoute);
        exit;
    }
}else{
    return true;
}

