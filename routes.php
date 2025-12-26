<?php

// router based on this source => https://helpincoding.com/php-routing-with-parameters/

// example route with multiple params
// "/download/{downID}/{filename}" => "download.php",

$login_routes = array(
    "/manage/login" => "../views/manage/login.php",
    "/manage/logout" => "../views/manage/logout.php",
);

$normal_routes = array(
    "/demo/{title}" => "../controller/democontroller.php",
    "/{title}" => "../controller/democontroller.php",
    "/" => "../controller/democontroller.php",
);

// secured routes which require (google) login
$secured_routes = array(
   "/user/{id}" => "../views/user.php",
   "/manage/profile" => "../views/manage/profile.php",
);
