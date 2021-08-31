<?php

$app->get('/setup/[{domain}]', function($request, $response, $args) {
    $sql = "SELECT config, linked_domain FROM setup WHERE domain=:domain";
    $sth = $this->db->prepare($sql);
    $sth->bindParam(":domain", $args['domain']);
    $data = getLinkedDomain($args['domain']);
    $sth->execute();
    $setup = $sth->fetchObject();
    $domain_config = json_decode($setup->config);
    $domain = array_map('trim',explode(',',str_replace("'",'',$setup->linked_domain)));
    $domain_config->linked_domain = $domain;
    $json_setup = json_decode(json_encode($domain_config),true);
    return $this->response->withJson($json_setup);
});

$app->get('/linkeddomain/[{domain}]', function($request, $response, $args) {
    $domain = getLinkedDomain($args['domain']);
    return $this->response->withJson($domain);
});


$app->get('/domain-daftar/[{domain}]', function($request, $response, $args){
    // $sql = "SELECT * FROM `person` WHERE domain_daftar=:domain GROUP BY identification_no";
    $sql = "SELECT p.person_id, p.card_no, p.full_name, p.identification_no, p.`contact_no`, p.domain_daftar, "
    . "(SELECT sum(jumlah_mata) FROM point_collection WHERE person_id=p.person_id) as kumpul, "
    . "(SELECT COALESCE(sum(jumlah_guna),0) FROM point_consume WHERE person_id=p.person_id) as guna "
    . "FROM person p "
    . "WHERE p.domain_daftar=:domain GROUP BY p.identification_no "
    . "ORDER BY person_id DESC";
    $sth = $this->db->prepare($sql);
    $sth->bindParam(":domain", $args['domain']);
    $sth->execute();
    $persons = $sth->fetchAll();
    return $this->response->withJson($persons);
});

$app->get('/domain-summary/[{domain}]', function($request, $response, $args){
    $domain = getLinkedDomain($args['domain']);
    $sql = "SELECT domain_daftar, count(DISTINCT(identification_no)) as total FROM person "
     . "WHERE domain_daftar IN ($domain) "
     . "AND status = 0 "
     . "GROUP by domain_daftar";
    $sth = $this->db->prepare($sql);
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
    $sth = $this->db->prepare($sql);
    $sth->execute();
    $persons = $sth->fetchAll();
    return $this->response->withJson($persons);
});
