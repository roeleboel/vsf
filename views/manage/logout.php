<?php
// Initialize the session
session_start();
// Destroy the session
session_destroy();
//get config
if (!isset($config)){
    $config = new config();
}

// Redirect to the login page
header('Location: '.$config->logout_redirect);
exit; 
