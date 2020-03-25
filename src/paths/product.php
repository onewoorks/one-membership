<?php

include_once 'functions/product_functions.php';

$app->get('/products', function ($request, $response, $args) {
    $sth = executeQuery2("SELECT * FROM person ORDER BY person_id");
    $sth->execute();
    $persons = $sth->fetchAll();

    return $this->response->withJson($persons);
});

$app->post('/product/add', function($request, $response, $args) {
    $products = $request->getParsedBody();

    foreach ($products as $productInfo):
        $mainCategory = GetMainCategory($productInfo['mpn']);
        $sql = "INSERT INTO oc_product "
                . " (model,sku,upc,ean,jan,isbn,mpn,location,quantity,stock_status_id,image,manufacturer_id,shipping,price,points,tax_class_id,date_available,weight,weight_class_id,length,width,height,length_class_id,subtract,minimum,sort_order,status,viewed,date_added,date_modified,user_id,ring_size)"
                . " VALUES "
                . " ("
                . "'" . str_replace($productInfo['isbn'], '', $productInfo['model']) . "',"
                . "'" . $productInfo['sku'] . "',"
                . "'',"
                . "'" . $productInfo['ean'] . "',"
                . "'" . (int) $productInfo['jan'] . "',"
                . "'" . $productInfo['isbn'] . "',"
                . "'" . $mainCategory . "',"
                . "'" . $productInfo['location'] . "',"
                . "'" . (int) $productInfo['quantity'] . "',"
                . "'" . (int) $productInfo['stock_status_id'] . "',"
                . "'" . $productInfo['image'] . "',"
                . "'" . (int) $productInfo['manufacturer_id'] . "',"
                . "'" . (int) $productInfo['shipping'] . "',"
                . "'" . (double) $productInfo['price'] . "',"
                . "'" . $productInfo['points'] . "',"
                . "'" . (int) $productInfo['tax_class_id'] . "',"
                . "'" . $productInfo['date_available'] . "',"
                . "'" . (float) $productInfo['weight'] . "',"
                . "'" . (int) $productInfo['weight_class_id'] . "',"
                . "'" . (float) $productInfo['length'] . "',"
                . "'" . (float) $productInfo['width'] . "',"
                . "'" . (float) $productInfo['height'] . "',"
                . "'" . (int) $productInfo['length_class_id'] . "',"
                . "'" . (int) $productInfo['substract'] . "',"
                . "'" . (int) $productInfo['minimum'] . "',"
                . "'" . (int) $productInfo['sort_order'] . "',"
                . "'" . (int) $productInfo['status'] . "',"
                . "'" . (int) $productInfo['viewed'] . "',"
                . "'" . $productInfo['date_added'] . "',"
                . "'" . $productInfo['date_modified'] . "',"
                . "'" . (int) $productInfo['user_id'] . "',"
                . "'" . (float) $productInfo['ring_size'] . "'"
                . ")";
        $sth = executeQuery2($sql);
        $sth->execute();
        $productId = $this->db->lastInsertId();
        InsertProductDescription($productId, $productInfo);
        InsertProductToCategory($productId, $productInfo['mpn']);
        InsertProductToStore($productId,0);
        InsertProductToLayout($productId, 0, 0);
    endforeach;
//    return $this->response->withJson($products);
});
