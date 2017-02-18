<?php

$app->get('/setup/[{domain}]',function($request,$response,$args){
   $sql = "SELECT config FROM setup WHERE domain=:domain"; 
   $sth = $this->db->prepare($sql);
   $sth->bindParam(":domain",$args['domain']);
   $sth->execute();
   $setup = $sth->fetchObject();
   return $this->response->withJson($setup);
});