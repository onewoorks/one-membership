<?php

$app->get('/persons', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT * FROM person ORDER BY person_id");
    $sth->execute();
    $todos = $sth->fetchAll();
    return $this->response->withJson($todos);
});

$app->get('/person/[{id}]', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT * FROM person WHERE person_id=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

$app->get('/persons/search/[{query}]', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT * FROM person WHERE full_name LIKE :query ORDER BY person_id");
    $query = "%" . $args['query'] . "%";
    $sth->bindParam("query", $query);
    $sth->execute();
    $todos = $sth->fetchAll();
    return $this->response->withJson($todos);
});