<?php

$app->get('/hadiah/list', function($request, $response, $args) {
    $sql = "SELECT *, id as 'key' FROM ganjaran ";
    $sth = executeQuery2($sql);
    $sth->execute();
    $hadiah = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $this->response->withJson($hadiah);
});

$app->get('/hadiah-tunai/list/[{domain}]', function($request, $response, $args) {
    $sql = "SELECT * FROM ganjaran WHERE jenis_hadiah='kupon'";
    $sth = executeQuery2($sql);
    $sth->execute();
    $hadiah = $sth->fetchAll();
    return $this->response->withJson($hadiah);
});

$app->post('/hadiah/add', function($request, $response, $args) {
    $input = $request->getParsedBody();
    print_r($input);
    $sql = "INSERT INTO `ganjaran` (`nama_ganjaran`, `mata`, `kuantiti`, `user`, `tarikh_daftar`, `image`, `jenis_hadiah`, `domain`) VALUES (:nama_ganjaran,:mata,:kuantiti,:user,:tarikh_daftar,:image, :jenis_hadiah, :domain)";
    
    $sth = $this->db->prepare($sql);
    $sth->bindParam(':nama_ganjaran',$input['nama_ganjaran']);
    $sth->bindParam(':mata',$input['mata']);
    $sth->bindParam(':kuantiti',$input['kuantiti']);
    $sth->bindParam(':user',$input['user']);
    $sth->bindParam(':tarikh_daftar',$input['tarikh_daftar']);
    $sth->bindParam(':image',$input['image']);
    $sth->bindParam(':jenis_hadiah', $input['jenis_hadiah']);
    $sth->bindParam(':domain', $input['domain']);
    $sth->execute();
});

$app->post('/hadiah/ubah', function($request, $response, $args){
    $input = $request->getParsedBody();
    
    $linked_domain = getLinkedDomain($input['domain']);
    $sql = "UPDATE ganjaran SET nama_ganjaran=:nama_ganjaran, mata=:mata, kuantiti=:kuantiti, tarikh_daftar=:tarikh_daftar, image=:image, domain=:domain, user=:user "
            . " WHERE domain IN ($linked_domain) AND id=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam(':nama_ganjaran',$input['nama_ganjaran']);
    $sth->bindParam(':mata',$input['mata']);
    $sth->bindParam(':kuantiti',$input['kuantiti']);
    $sth->bindParam(':user',$input['user']);
    $sth->bindParam(':tarikh_daftar',$input['tarikh_daftar']);
    $sth->bindParam(':image',$input['image']);
    $sth->bindParam(':domain', $input['domain']);
    $sth->bindParam(':id',$input['id']);
    $sth->execute();
});