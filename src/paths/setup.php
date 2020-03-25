<?php

$app->get('/setup/[{domain}]', function($request, $response, $args) {
    $sql = "SELECT config FROM setup WHERE domain=:domain";
    $sth = executeQuery2($sql);
    $sth->bindParam(":domain",$args['domain']);
    $sth->execute();
    $config = $sth->fetchObject();
    if($config){
        $config->config = json_decode($config->config);
        return $this->response->withJson($config);
    }
});

$app->get('/linkeddomain/[{domain}]', function($request, $response, $args) {
    $domain = getLinkedDomain($args['domain']);
    return $this->response->withJson($domain);
});

$app->get('/domain-daftar/[{domain}]', function($request, $response, $args){
    $domain = $args['domain'];
    $sql = "SELECT p.person_id, p.card_no, p.full_name, p.identification_no, p.`contact_no`, p.domain_daftar, "
    . "(SELECT sum(jumlah_mata) FROM point_collection WHERE person_id=p.person_id) as kumpul, "
    . "(SELECT COALESCE(sum(jumlah_guna),0) FROM point_consume WHERE person_id=p.person_id) as guna "
    . "FROM person p "
    . "WHERE p.domain_daftar='$domain' GROUP BY p.identification_no "
    . "ORDER BY person_id DESC";
    return $this->response->withJson(executeQuery($sql));
});

$app->get('/domain-summary/[{domain}]', function($request, $response, $args){
    $domain = getLinkedDomain($args['domain']);
    $sql = "SELECT domain_daftar, count(DISTINCT(identification_no)) as total FROM person "
     . "WHERE domain_daftar IN ($domain) "
     . "GROUP by domain_daftar";
    $sth = executeQuery2($sql);
    $sth->execute();
    $persons = $sth->fetchAll();
    return $this->response->withJson($persons);
});

$app->get('/registration-summary/[{domain}]', function($request, $response, $args){
    $domain = getLinkedDomain($args['domain']);
    $sql = "select count(DISTINCT(identification_no)) as total, tarikh_daftar "
    . "FROM person "
    . "WHERE domain_daftar in ($domain) "
    . "GROUP by date(tarikh_daftar)"
    . "ORDER BY tarikh_daftar ASC";
    $sth = executeQuery2($sql);
    $sth->execute();
    $persons = $sth->fetchAll();
    return $this->response->withJson($persons);
});
