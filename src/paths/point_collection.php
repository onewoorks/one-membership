<?php

$app->get('/points/[{id}]',function($request,$response,$args){
    $sql = "SELECT * FROM point_collection WHERE person_id=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $points = $sth->fetchAll();
    return $this->response->withJson($points);
});