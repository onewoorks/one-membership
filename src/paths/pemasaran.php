<?php

$app->get('/pemasaran/sms/template-list/[{type}]', function($request, $response, $args){
    $query = "SELECT * FROM sms_template WHERE code=:type";
    $sth = executeQuery2($query);
    $sth->bindParam(":type", $args['type']);
    $sth->execute();
    $templates = $sth->fetchAll(\PDO::FETCH_ASSOC);
    return $this->response->withJson($templates);
});