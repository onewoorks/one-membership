<?php

// please study this framework betul3

function getLinkedDomain($domain) {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=".dbname."", dbuser, dbpassword);
    $sql = "SELECT linked_domain FROM setup WHERE domain='".$domain."'";
    $sth = $pdo->prepare($sql);
    $sth->execute();
    $listDomain = $sth->fetchObject();
    return $listDomain->linked_domain;
}