<?php

// Routes
//$app->get('/[{name}]', function ($request, $response, $args) {
//    // Sample log message
//    $this->logger->info("Slim-Skeleton '/' route");
//
//    // Render index view
//    return $this->renderer->render($response, 'index.phtml', $args);
//});
// get all todos

date_default_timezone_set("Asia/Kuala_Lumpur");

define("host",'127.0.0.1');
define("dbname", 'onewoork_membership');
define("dbuser", 'onewoork_members');
define("dbpassword", '6G1?U9gL=QS$');

include_once 'Class/dirty_functions.php';

include_once 'paths/persons.php';
include_once 'paths/point_collection.php';
include_once 'paths/setup.php';
include_once 'paths/hadiah.php';
