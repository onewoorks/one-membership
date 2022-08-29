<?php

$app->get('/persons', function ($request, $response, $args) {
    $sth = executeQuery2("SELECT * FROM person ORDER BY person_id");
    $sth->execute();
    $person = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $this->response->withJson($person);
});

$app->post('/person/lookup', function ($request, $response, $args) {
    $body = $request->getParsedBody();
    $query = "SELECT
            p.*,
            (SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = p.person_id) AS collected,
            (SELECT sum(jumlah_guna) FROM point_consume WHERE person_id = p.person_id) as consume
            FROM person p where (p.card_no = :search OR p.contact_no = :search OR p.identification_no = :search ) LIMIT 1";
    $sth = executeQuery2($query);
    $sth->bindParam(":search", $body['search']);
    $sth->execute();
    $result = $sth->fetchObject();
    return $this->response->withJson($result);
});

$app->get('/persons-and-point', function ($request, $response, $args) {
    $sql = "SELECT p.person_id, pe.full_name, pe.card_no, pe.contact_no, sum(p.jumlah_mata) AS point "
        . " FROM point_collection p LEFT JOIN person pe ON (pe.person_id=p.person_id) "
        . " WHERE p.domain IN ('husnu_arba','kemaman') "
        . " GROUP BY person_id";
    $sth = executeQuery2($sql);
    $sth->execute();
    $todos = $sth->fetchAll();
    return $this->response->withJson($todos);
});

$app->get('/persons-and-point-domain/[{domain}]', function ($request, $response, $args) {
    $linkedDomain = getLinkedDomain($args['domain']);

    $sql = "SELECT pe.person_id, pe.full_name, pe.card_no, pe.contact_no,  pe.status AS status_ahli,
(SELECT COALESCE(SUM(jumlah_mata),0) FROM point_collection WHERE person_id=pe.person_id AND domain IN ($linkedDomain))
 AS point_in,
 (SELECT COALESCE(SUM(jumlah_guna),0) FROM point_consume WHERE person_id=pe.person_id AND domain IN ($linkedDomain)) AS point_out
FROM person pe
WHERE pe.domain_daftar IN ($linkedDomain) AND pe.status = 0 ";
    if ($args['domain'] == 'sridelima'):
        $sql .= "AND pe.card_no <> pe.identification_no ";
        $sql .= "GROUP BY pe.card_no";
    else:
        $sql .= "GROUP BY pe.person_id";
    endif;
    $sth = executeQuery2($sql);
    $sth->execute();
    $personPoints = $sth->fetchAll();
    return $this->response->withJson($personPoints);
});

$app->get('/person/[{id}]', function ($request, $response, $args) {
    $sth = executeQuery2("SELECT * FROM person WHERE person_id=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

$app->get('/person-and-point/[{id}]', function ($request, $response, $args) {
    $query = "SELECT
p.*,
(SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = :id) AS collected,
(SELECT sum(jumlah_guna) FROM point_consume WHERE person_id =:id) as consume
FROM person p where p.person_id = :id";
    $sth = executeQuery2($query);
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

$app->get('/person-and-point-2/[{id}]', function ($request, $response, $args) {
    $sql = "SELECT
p.*,
(SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = :id) AS collected,
(SELECT sum(jumlah_guna) FROM point_consume WHERE person_id =:id) as consume
FROM person p where p.person_id = :id";

    $sth = executeQuery2($sql);
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

$app->get('/person/point-log/[{params:.*}]', function ($request, $response, $args) {
    $params = explode('/', $request->getAttribute('params'));
    $person_group = getPersonGroup($params[0]);
    $linkedDomain = getLinkedDomain($params[0]);
    $sql = "select p.type, p.tarikh, p.person_id, p.perkara, p.mata_ganjaran, p.resit, p.domain FROM (
            SELECT CONCAT('KUMPUL') as type, tarikh_kumpul AS tarikh, person_id, perkara, jumlah_mata AS mata_ganjaran, no_resit AS resit, domain
            FROM point_collection
            WHERE person_id=:person_id AND domain IN ($linkedDomain) AND DATE(tarikh_kumpul) <= :tarikh
            UNION SELECT CONCAT('GUNA') AS type, tarikh_guna AS tarikh, person_id, perkara, jumlah_guna AS mata_ganjaran, CONCAT('') AS resit, domain
            FROM point_consume WHERE person_id=:person_id AND domain IN($linkedDomain) AND DATE(tarikh_guna) <= :tarikh ) AS p
            LEFT JOIN $person_group person ON (p.person_id=person.person_id) ORDER BY p.tarikh DESC";
    $sth = executeQuery2($sql);
    $sth->bindParam("person_id", $params[1]);
    $sth->bindParam("tarikh", isset($params[2]) ? $params[2] : date('Y-m-d'));
    $sth->execute();
    $pointlog = $sth->fetchAll();
    return $this->response->withJson($pointlog);
});

$app->get('/person/point-log-2/[{params:.*}]', function ($request, $response, $args) {
    $params = explode('/', $request->getAttribute('params'));
    $person_id = $params[0];
    $tarikh = isset($params[1]) ? $params[1] : date('Y-m-d');
    $sql = "select p.type, p.tarikh, p.person_id, p.perkara, p.mata_ganjaran, p.resit, p.domain FROM (
            SELECT CONCAT('KUMPUL') as type, tarikh_kumpul AS tarikh, person_id, perkara, jumlah_mata AS mata_ganjaran, no_resit AS resit, domain
            FROM point_collection
            WHERE person_id=$person_id AND DATE(tarikh_kumpul) <= '$tarikh'
            UNION SELECT CONCAT('GUNA') AS type, tarikh_guna AS tarikh, person_id, perkara, jumlah_guna AS mata_ganjaran, CONCAT('') AS resit, domain
            FROM point_consume WHERE person_id=$person_id AND DATE(tarikh_guna) <= '$tarikh' ) AS p
            LEFT JOIN person person ON (p.person_id=person.person_id) ORDER BY p.tarikh DESC";
    $sth = executeQuery2($sql);
    $sth->bindParam(':person_id', $person_id);
    $sth->execute();
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $this->response->withJson($result);
});

$app->get('/persons/search/[{query}]', function ($request, $response, $args) {
    $sth = executeQuery2("SELECT * FROM person WHERE full_name LIKE :query ORDER BY person_id");
    $query = "%" . $args['query'] . "%";
    $sth->bindParam("query", $query);
    $sth->execute();
    $todos = $sth->fetchAll();
    return $this->response->withJson($todos);
});

$app->get('/person/domain/[{identification_id}]', function ($request, $response, $args) {
    $query = "SELECT p.*, "
    . "(SELECT COALESCE(sum(jumlah_mata),0) FROM point_collection WHERE person_id=p.person_id) as total_point, "
    . "(SELECT COALESCE(sum(jumlah_guna),0) FROM point_consume WHERE person_id=p.person_id) as total_consume "
    . "FROM person p WHERE  p.person_id = (SELECT person_id FROM person WHERE  identification_no = :identification_no) ";
    $sth = executeQuery2($query);
    $sth->bindParam(":identification_no", $args['identification_id']);
    $sth->execute();
    $person = $sth->fetchObject();
    return $this->response->withJson($person);
});

$app->get('/person/check/[{card_no}]', function ($request, $response, $args) {
    $query = "SELECT
            p.*,
            (SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = p.person_id) AS collected,
            (SELECT sum(jumlah_guna) FROM point_consume WHERE person_id = p.person_id) as consume
            FROM person p where p.card_no = :card_no ";

    $sth = executeQuery2($query);
    $sth->bindParam(":card_no", $args['card_no']);
    $sth->execute();
    $person = $sth->fetchObject();

    if ($person->domain_daftar != '') {
        $person->jenis_pelanggan = ($person->domain_daftar == 'sridelima' && $person->card_no == $person->identification_no) ? 'Pelanggan Biasa' : 'Pelanggan Membership';
    }

    return $this->response->WithJson($person);
});

$app->get('/person/check-2/[{params:.*}]', function ($request, $response) {
	$params = explode('/', $request->getAttribute('params'));
//	print_r($params);
//	die();
    $linkedDomain = getLinkedDomain($params[0]);
    //print_r($linkedDomain);
   // die();
   // $personLink = linkedDomain($params[1], $linkedDomain);
   // print_r($personLink);
    $query = "SELECT
            p.*,
            (SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = p.person_id) AS collected,
            (SELECT sum(jumlah_guna) FROM point_consume WHERE person_id = p.person_id) as consume
            FROM person p where p.card_no = :card_no AND domain_daftar IN ($linkedDomain)";

    $sth = executeQuery2($query);
    $sth->bindParam(":card_no", $params[1]);
    $sth->execute();
    $person = $sth->fetchObject();

    if ($person->domain_daftar != '') {
        $person->jenis_pelanggan = ($person->domain_daftar == 'sridelima' && $person->card_no == $person->identification_no) ? 'Pelanggan Biasa' : 'Pelanggan Membership';
    }
    return $this->response->WithJson($person);
});

$app->post('/person/new', function ($request, $response) {
    $input = $request->getParsedBody();

    switch ($input['validiti']):
case 'tahunan':
    $tarikhLuput = date('Y-m-d H:i:s', strtotime('+1 year'));
    break;
case 'bulanan':
    $tarikhLuput = date('Y-m-d H:i:s', strtotime('+1 month'));
    break;
default:
    $tarikhLuput = date('Y-m-d H:i:s', strtotime('+50 year'));
    break;
    endswitch;

    $domain = $input['domain'];
    $staffName = strtoupper($input['staff_name']);
    $noKad = $input['no_kad'];
    $namaAhli = strtoupper($input['nama_ahli']);
    $identificationNo = $input['identification_no'];
    $contactNo = $input['contact_no'];
    $alamat = $input['alamat'];
    $referal = $input['referal'];
    $email = $input['email'];
    $tarikhDaftar = date('Y-m-d H:i:s');
    $mLastDate = $tarikhLuput;
    $point = 0;
    $status = 0;

    $sql = "INSERT INTO person "
        . "(person_id,card_no,full_name,identification_no,alamat,contact_no,tarikh_daftar,m_lastdate, point, diurus_oleh,status,domain_daftar,referal,email) "
        . " VALUES "
        . "(null,:card_no,:full_name,:identification_no,:alamat,:contact_no,:tarikh_daftar,:m_lastdate, :point, :diurus_oleh,:status,:domain_daftar,:referal,:email)";

    $sth = executeQuery2($sql);

    $sth->bindParam(':card_no', $noKad);
    $sth->bindParam(':full_name', $namaAhli);
    $sth->bindParam(':identification_no', $identificationNo);
    $sth->bindParam(':alamat', $alamat);
    $sth->bindParam(':contact_no', $contactNo);
    $sth->bindParam(':tarikh_daftar', $tarikhDaftar);
    $sth->bindParam(':m_lastdate', $mLastDate);
    $sth->bindParam(':point', $point);
    $sth->bindParam(':diurus_oleh', $staffName);
    $sth->bindParam(':status', $status);
    $sth->bindParam(':domain_daftar', $domain);
    $sth->bindParam(':referal', $referal);
    $sth->bindParam(':email', $email);
    $sth->execute();
});

$app->put('/person/deactive', function ($request, $response, $args) {
    $input = $request->getParsedBody();
    $sql = "UPDATE person SET status=1 WHERE person_id = :person_id AND domain_daftar = :domain";
    $sth = executeQuery2($sql);
    $sth->bindParam(':person_id', $input['person_id']);
    $sth->bindParam(':domain', $input['domain']);
    $sth->execute();
});

$app->get('/person/search/[{filter}]', function ($request, $response, $args) {
    $sql = "SELECT *, person_id as 'key' FROM person where `card_no` like :filter or `identification_no` like :filter or contact_no like :filter GROUP BY identification_no";
    $sth = executeQuery2($sql);
    $sth->bindValue(':filter', '%' . $args['filter'] . '%');
    $sth->execute();
    $found = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $this->response->withJson($found);
});
