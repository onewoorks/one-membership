<?php

function getLinkedDomain($domain) {
    $pdo = new PDO("mysql:host=".HOST.";dbname=".DBNAME."", DBUSER, DBPASSWORD);
    $sql = "SELECT linked_domain FROM setup WHERE domain='".$domain."'";
    $sth = $pdo->prepare($sql);
    $sth->execute();
    $listDomain = $sth->fetchObject();
    return $listDomain->linked_domain;
}

function getPersonGroup($domain){
    $pdo = new PDO("mysql:host=".HOST.";dbname=".DBNAME."", DBUSER, DBPASSWORD);
    $sql = "SELECT person_group FROM setup WHERE domain='".$domain."'";
    $sth = $pdo->prepare($sql);
    $sth->execute();
    $personGroup = $sth->fetchObject();
    return $personGroup->person_group;
}

function executeQuery($sql){
    $pdo = new PDO("mysql:host=".HOST.";dbname=".DBNAME."", DBUSER, DBPASSWORD);
    $sth = $pdo->prepare($sql);
    $sth->execute();
    return $sth->fetchAll(\PDO::FETCH_ASSOC);
}

function executeQuery2($sql){
    $pdo = new PDO("mysql:host=".HOST.";dbname=".DBNAME."", DBUSER, DBPASSWORD);
    return $pdo->prepare($sql);
}