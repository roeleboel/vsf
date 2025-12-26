<?php


session_start();

// router based on this source => https://helpincoding.com/php-routing-with-parameters/
// in case of problems, check this:
// https://www.phpclasses.org/package/7700-PHP-Authorize-and-access-APIs-using-OAuth.html

require_once '../config.php';
require_once "../base/route.php";
require_once '../base/utils.php';
require_once '../base/template.php';
require_once("../db/dbtables.php");
require_once('../db/generic_db.php');
require_once '../routes.php';

$route = new Route();
$route->addAllRoutes($normal_routes,$login_routes,$secured_routes);
$route->notFound("../views/404.php");
