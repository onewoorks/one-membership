<?php

$app->get('/points/[{person_id}]', function($request, $response, $args) {
    $sql = "select pc.* from point_collection pc "
            . "WHERE pc.person_id = :person_id";
    $sth = executeQuery2($sql);
    $sth->bindParam(":person_id", $args['person_id']);
    $sth->execute();
    $points = $sth->fetchAll();
    return $this->response->withJson($points);
});

$app->get('/point/check-resit/[{params:.*}]', function($request, $response, $args){
   $params = explode('/', $request->getAttribute('params')); 
   $sql = "SELECT * FROM point_collection WHERE no_resit=:no_resit AND domain=:domain";
   $sth = executeQuery2($sql);
   $sth->bindParam(':domain',$params[0]);
   $sth->bindParam(':no_resit',$params[1]);
   $sth->execute();
   $result = $sth->fetchAll(PDO::FETCH_ASSOC);
   return $this->response->withJson((count($result)>0) ? $result : array());
});

$app->post('/point/add',function($request, $response, $args){
   $input = $request->getParsedBody();
   
   $sql = "INSERT INTO point_collection (person_id,jenis,perkara,no_resit,jumlah_mata,diurus_oleh,domain, transaksi) VALUES (:person_id,:jenis,:perkara,:no_resit,:jumlah_mata,:diurus_oleh,:domain, :transaksi)";
   
   $sth = executeQuery2($sql);
   $sth->bindParam(':person_id', $input['person_id']);
   $sth->bindParam(':jenis', $input['jenis']);
   $sth->bindParam(':perkara', $input['perkara']);
   $sth->bindParam(':no_resit', $input['no_resit']);
   $sth->bindParam(':jumlah_mata', $input['jumlah_mata']);
   $sth->bindParam(':diurus_oleh', $input['diurus_oleh']);
   $sth->bindParam(':domain',$input['domain']);
   $sth->bindParam(':transaksi', $input['transaksi']);
   $sth->execute();
   return $this->response->withJson($input);
});

$app->post('/point/use', function($request, $response, $args){
    $input = $request->getParsedBody();
    $sql = "INSERT INTO point_consume (tarikh_guna,person_id,jumlah_guna,perkara,domain,diurus_oleh,no_resit,ganjaran_id) "
            . "VALUES (:tarikh_guna,:person_id,:jumlah_guna,:perkara,:domain,:diurus_oleh,:no_resit,:ganjaran_id)";
    $sth = executeQuery2($sql);
    $sth->bindParam(':tarikh_guna', $input['tarikh_guna']);
    $sth->bindParam(':person_id', $input['person_id']);
    $sth->bindParam(':jumlah_guna', $input['jumlah_guna']);
    $sth->bindParam(':perkara',$input['perkara']);
    $sth->bindParam(':domain',$input['domain']);
    $sth->bindParam(':diurus_oleh',$input['diurus_oleh']);
    $sth->bindParam(':no_resit',$input['no_resit']);
    $sth->bindParam(':ganjaran_id', $input['ganjaran_id']);
    $sth->execute();
});
