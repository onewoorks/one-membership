<?php

//require 'vendor\autoload.php';

function linkedDomain($domain) {
    $sql = "SELECT config FROM setup WHERE domain='".$domain."'";
    $sth = $this->db->prepare($sql);
    $sth->execute();
    $setup = $sth->fetchObject();
    return $setup;
}
