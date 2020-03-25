<?php


$app->get('/pemasaran/sms/template-list/[{type}]', function($request, $response, $args){
    $query = "SELECT * FROM sms_template WHERE code=:type";
    $sth = executeQuery2($query);
    $sth->bindParam(":type", $args['type']);
    $sth->execute();
    $templates = $sth->fetchAll(\PDO::FETCH_ASSOC);
    return $this->response->withJson($templates);
});

$app->post('/pemasaran/jualan', function($request, $response, $args){
    $payloads = $request->getParsedBody();
    $query = "SELECT * FROM sms_template WHERE type='jualan' AND code='automate' AND status=1";
    $sth = executeQuery2($query);
    $sth->execute();
    $automate_jualan = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach($automate_jualan as $a):
        SmsGateway::SendSms($a, $payloads);
    endforeach;
    $message = "Sms integration has been executed!";
    return $this->response->withJson($message);

});