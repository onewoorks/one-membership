<?php

$app->get('/setup/[{id}]',function($request,$response,$args){
   $sql = "SELECT * FROM setup WHERE id=:id"; 
   $sth = $this->db->prepare($sql);
   $sth->bindParam("id",$args['id']);
   $sth->execute();
   $setup = $sth->fetchObject();
   return $this->response->withJson($setup);
});