<?php

require_once('logged_in_check.php');

// if logged in -> redirect to profile
header('Location: profile.php');
exit;
