<?php

//include "template.php";

// Template::view('index2.html');

$title = "Home Page";
if (isset($params['title'])){
    $title = $params['title'];
}

Template::view('demo.html', [
    'title' => $title,
    'colors' => ['red','blue','green']
]);
