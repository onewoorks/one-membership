<?php

$app->get('/persons', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT * FROM person ORDER BY person_id");
    $sth->execute();
    $todos = $sth->fetchAll();
    return $this->response->withJson($todos);
});

$app->get('/persons-and-point', function ($request, $response, $args) {
    $sql = "SELECT p.person_id, pe.full_name, pe.card_no, pe.contact_no, sum(p.jumlah_mata) AS point "
            . " FROM point_collection p LEFT JOIN person pe ON (pe.person_id=p.person_id) "
            . " WHERE p.domain IN ('husnu_arba','kemaman') "
            . " GROUP BY person_id";
    $sth = $this->db->prepare($sql);
    $sth->execute();
    $todos = $sth->fetchAll();
    return $this->response->withJson($todos);
});

$app->get('/persons-and-point-domain/[{domain}]', function ($request, $response, $args) {
    $linkedDomain = getLinkedDomain($args['domain']);

//     $sql = "SELECT pe.person_id, pe.full_name, pe.card_no, pe.contact_no,  pe.status AS status_ahli,
// (SELECT COALESCE(SUM(jumlah_mata),0) FROM point_collection WHERE person_id=pe.person_id AND domain IN ($linkedDomain)) 
//  AS point_in,
//  (SELECT COALESCE(SUM(jumlah_berat),0) FROM point_collection WHERE person_id=pe.person_id AND domain IN ($linkedDomain)) 
//  AS berat_in,
//  (SELECT COALESCE(SUM(jumlah_guna),0) FROM point_consume WHERE person_id=pe.person_id AND domain IN ($linkedDomain)) AS point_out,
//  (SELECT COALESCE(SUM(berat_guna),0) FROM point_consume WHERE person_id=pe.person_id AND domain IN ($linkedDomain)) AS berat_out
// FROM person pe 
// WHERE pe.domain_daftar IN ($linkedDomain) AND pe.status = 0 ";
$sql = "SELECT pe.person_id, pe.full_name, replace(pe.card_no,'+6','') as card_no, replace(pe.contact_no,'+6','') as contact_no,  pe.status AS status_ahli,
pp.point_in,
pp.berat_in,
pp.point_out,
pp.berat_out
FROM person pe
left join person_point pp on pp.person_id = pe.person_id 
WHERE pe.domain_daftar IN ($linkedDomain) AND pe.status = 0 ";
    if($args['domain'] == 'sridelima'):
        $sql .= "AND pe.card_no <> pe.identification_no ";
        $sql .= "GROUP BY pe.card_no";
    else :
        $sql .= "GROUP BY pe.card_no";
    endif;
    $sql .= " ORDER BY pe.person_id DESC";
    $sth = $this->db->prepare($sql);
    $sth->execute();
    $personPoints = $sth->fetchAll();
    return $this->response->withJson($personPoints);
});

$app->get('/person/{id}/{domain}', function ($request, $response, $args) {
    $linkedDomain = getLinkedDomain($args['domain']);
    $sth = $this->db->prepare("SELECT * FROM person WHERE person_id=:id and domain_daftar IN ($linkedDomain)");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

$app->get('/person-and-point/[{id}]', function ($request, $response, $args) {
    $query = "SELECT 
p.*,
(SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = :id) AS collected,
(SELECT sum(jumlah_berat) FROM point_collection WHERE person_id = :id) AS collected_berat,
(SELECT sum(jumlah_guna) FROM point_consume WHERE person_id =:id) as consume,
(SELECT sum(berat_guna) FROM point_consume WHERE person_id =:id) as consume_berat
FROM person p where p.person_id = :id";
    $sth = $this->db->prepare($query);
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

$app->get('/person-and-point-card/[{card_no}]', function ($request, $response, $args) {
    $query = "SELECT 
p.*,
sum((SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = p.person_id)) AS collected,
sum((SELECT sum(jumlah_guna) FROM point_consume WHERE person_id =p.person_id)) as consume
FROM person p where p.card_no = :card_no";
    $sth = $this->db->prepare($query);
    $sth->bindParam("card_no", $args['card_no']);
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

$app->get('/person/point-log-x/[{params:.*}]', function($request, $response, $args) {
    $params = explode('/', $request->getAttribute('params'));
    $linkedDomain = getLinkedDomain($params[0]);
    $sql = "select p.type, p.tarikh, p.person_id, p.perkara, p.mata_ganjaran, p.mata_berat, p.resit, p.domain FROM (
            SELECT CONCAT('KUMPUL') as type, tarikh_kumpul AS tarikh, person_id, perkara, jumlah_mata AS mata_ganjaran, jumlah_berat AS mata_berat, no_resit AS resit, domain
            FROM point_collection 
            WHERE person_id=:person_id AND domain IN ($linkedDomain) AND DATE(tarikh_kumpul) <= :tarikh 
            UNION SELECT CONCAT('GUNA') AS type, tarikh_guna AS tarikh, person_id, perkara, jumlah_guna AS mata_ganjaran, berat_guna as mata_berat, CONCAT('') AS resit, domain
            FROM point_consume WHERE person_id=:person_id AND domain IN($linkedDomain) AND DATE(tarikh_guna) <= :tarikh ) AS p 
            LEFT JOIN  person person ON (p.person_id=person.person_id) ORDER BY p.tarikh DESC";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("person_id", $params[1]);
    $date_now = isset($params[2]) ? $params[2] : date('Y-m-d');
    $sth->bindParam("tarikh", isset($params[2]) ? $params[2] : date('Y-m-d'));
    // $sth->bindParam("tarikh", $date_now);
    // echo $sql;
    $sth->execute();
    $pointlog = $sth->fetchAll();
    return $this->response->withJson($pointlog);
});

$app->get('/person/point-log/[{params:.*}]', function($request, $response, $args) {
    $params = explode('/', $request->getAttribute('params'));
    $linkedDomain = getLinkedDomain($params[0]);
    $sql = "select p.type, p.tarikh, p.person_id, p.perkara, p.mata_ganjaran, p.mata_berat, p.resit, p.domain FROM (
            SELECT CONCAT('KUMPUL') as type, tarikh_kumpul AS tarikh, person_id, perkara, jumlah_mata AS mata_ganjaran, jumlah_berat AS mata_berat, no_resit AS resit, domain
            FROM point_collection 
            WHERE person_id=:person_id AND domain IN ($linkedDomain) AND DATE(tarikh_kumpul) <= :tarikh 
            UNION SELECT CONCAT('GUNA') AS type, tarikh_guna AS tarikh, person_id, perkara, jumlah_guna AS mata_ganjaran, berat_guna as mata_berat, CONCAT('') AS resit, domain
            FROM point_consume WHERE person_id=:person_id AND domain IN($linkedDomain) AND DATE(tarikh_guna) <= :tarikh ) AS p 
            LEFT JOIN  person person ON (p.person_id=person.person_id) ORDER BY p.tarikh DESC";
            // echo $linkedDomain;
    $sth = $this->db->prepare($sql);
    $sth->bindParam("person_id", $params[1]);
    $date_now = isset($params[2]) ? $params[2] : date('Y-m-d');
    $sth->bindParam("tarikh", $date_now);
    $sth->execute();
    $pointlog = $sth->fetchAll();
    return $this->response->withJson($pointlog);
});

$app->get('/person/point-log-card-no/[{params:.*}]', function($request, $response, $args) {
    $params = explode('/', $request->getAttribute('params'));
    $linkedDomain = getLinkedDomain($params[0]);
    
    $user_person_id = "SELECT GROUP_CONCAT(person_id) as list_person_id from person where card_no = '$params[1]' and domain_daftar IN ($linkedDomain)";
    $sth = $this->db->prepare($user_person_id);
    $sth->execute();
    $user_list = $sth->fetchAll();
    if (count($user_list) > 0) {
        $list_person_id = $user_list[0]['list_person_id'];    
        $sql = "SELECT p.type, p.tarikh, p.person_id, p.perkara, p.mata_ganjaran, p.resit, p.domain FROM (
            SELECT CONCAT('KUMPUL') as type, tarikh_kumpul AS tarikh, person_id, perkara, jumlah_mata AS mata_ganjaran, no_resit AS resit, domain
            FROM point_collection 
            WHERE person_id IN ($list_person_id) AND domain IN ($linkedDomain) AND DATE(tarikh_kumpul) <= :tarikh 
            UNION ALL SELECT CONCAT('GUNA') AS type, tarikh_guna AS tarikh, person_id, perkara, jumlah_guna AS mata_ganjaran, CONCAT('') AS resit, domain
            FROM point_consume WHERE person_id IN ($list_person_id) AND domain IN($linkedDomain) AND DATE(tarikh_guna) <= :tarikh ) AS p 
            LEFT JOIN  person person ON (p.person_id=person.person_id) 
            ORDER BY p.tarikh DESC";
            $sth = $this->db->prepare($sql);
            $sth->bindParam("person_id", $params[1]);
            $date_now = isset($params[2]) ? $params[2] : date('Y-m-d');
            $sth->bindParam("tarikh", $date_now);
            $sth->execute();
            $pointlog = $sth->fetchAll();
            return $this->response->withJson($pointlog);
    }
    
});

$app->get('/persons/search/[{query}]', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT * FROM person WHERE full_name LIKE :query ORDER BY person_id");
    $query = "%" . $args['query'] . "%";
    $sth->bindParam("query", $query);
    $sth->execute();
    $todos = $sth->fetchAll();
    return $this->response->withJson($todos);
});

$app->get('/person/domain/{params:.*}', function ($request, $response, $args) {
    $params = explode('/', $request->getAttribute('params'));
    $linkedDomain = getLinkedDomain($params['0']);
    $sth = $this->db->prepare("SELECT * FROM person WHERE domain_daftar IN ($linkedDomain) AND identification_no = :identification_no");
    $sth->bindParam(":identification_no", $params[1]);
    $sth->execute();
    $person = $sth->fetchObject();
    return $this->response->withJson($person);
});

$app->get('/person/check/[{card_no}]', function($request, $response, $args) {
    $query = "SELECT 
            p.*,
            (SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = p.person_id) AS collected,
            (SELECT sum(jumlah_guna) FROM point_consume WHERE person_id = p.person_id) as consume
            FROM person p where p.card_no = :card_no ";

    $sth = $this->db->prepare($query);
    $sth->bindParam(":card_no", $args['card_no']);
    $sth->execute();
    $person = $sth->fetchObject();
    
    if($person->domain_daftar != ''){
        $person->jenis_pelanggan = ($person->domain_daftar == 'sridelima' && $person->card_no == $person->identification_no) ? 'Pelanggan Biasa' : 'Pelanggan Membership';
    } else {
        
    }
    
    return $this->response->WithJson($person);
});

$app->get('/person/check-2/[{params:.*}]',function($request, $response, $args) {
     $params = explode('/', $request->getAttribute('params'));
     $linkedDomain = getLinkedDomain($params[0]);
     $query = "SELECT 
            p.*,
            (SELECT sum(jumlah_mata) FROM point_collection WHERE person_id = p.person_id) AS collected,
            (SELECT sum(jumlah_guna) FROM point_consume WHERE person_id = p.person_id) as consume
            FROM person p where p.card_no = :card_no AND domain_daftar IN ($linkedDomain)";

    $sth = $this->db->prepare($query);
    $sth->bindParam(":card_no", $params[1]);
    $sth->execute();
    $person = $sth->fetchObject();
    
    if($person):
    $person->jenis_pelanggan = 'Pelanggan Membership';
    
    if($person->domain_daftar == 'sridelima'){
        $person->jenis_pelanggan = ($person->domain_daftar == 'sridelima' && $person->card_no == $person->identification_no) ? 'Pelanggan Biasa' : 'Pelanggan Membership';
    }
    
    if(substr( $person->domain_daftar, 0, 8 ) === "ariffin-" || $person->domain_daftar == 'zak-chinatown'){
        $person->jenis_pelanggan = ($person->status == 1) ? 'Pelanggan Biasa' : 'Pelanggan Membership';
    }

    // if($person->domain_daftar )
    // if($person->domain_daftar != ''){
    //     $person->jenis_pelanggan = ($person->domain_daftar == 'sridelima' && $person->card_no == $person->identification_no) ? 'Pelanggan Biasa' : 'Pelanggan Membership';
    // } else {
        
    // }
    endif;
    
    return $this->response->WithJson($person);
});

$app->post('/person/new', function($request, $response) {
    $input = $request->getParsedBody();

    switch ($input['validiti']):
        case 'tahunan':
            $tarikhLuput = date('Y-m-d H:i:s', strtotime('+1 year'));
            break;
        case 'bulanan':
            $tarikhLuput = date('Y-m-d H:i:s', strtotime('+1 month'));
            break;
        default:
            $tarikhLuput = date('Y-m-d H:i:s', strtotime('+1 year'));
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
    $status = isset($input['status']) ? $input['status'] : 0;

    $sql = "INSERT INTO person "
            . "(person_id,card_no,full_name,identification_no,alamat,contact_no,tarikh_daftar,m_lastdate, point, diurus_oleh,status,domain_daftar,referal,email) "
            . " VALUES "
            . "(null,:card_no,:full_name,:identification_no,:alamat,:contact_no,:tarikh_daftar,:m_lastdate, :point, :diurus_oleh,:status,:domain_daftar,:referal,:email)";

    $sth = $this->db->prepare($sql);

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


//    return $this->response->withJson($input);
});


$app->put('/person/deactive', function($request, $response, $args) {
    $input = $request->getParsedBody();
    $sql = "UPDATE person SET status=1 WHERE person_id = :person_id AND domain_daftar = :domain";
    $sth = $this->db->prepare($sql);
    $sth->bindParam(':person_id', $input['person_id']);
    $sth->bindParam(':domain', $input['domain']);
    $sth->execute();
});
