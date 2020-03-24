<?php

function GetMainCategory($category_id) {
    $categories = array(
        61 => array(97, 100, 101, 102, 103, 104, 110),
        59 => array(98, 105),
        65 => array(99),
        70 => array(93, 95, 96, 107)
    );
    $mainCategory = false;
    foreach ($categories as $key => $category):
        foreach ($category as $c):
            if ($c == $category_id):
                $mainCategory = $key;
            endif;
        endforeach;
    endforeach;
    return $mainCategory;
}

function InsertProductDescription($productId, array $productInfo) {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=tukangemas_sankyu', 'root', '');
    for ($i = 0; $i < 2; $i++):
        $productDescription = array(
            'product_id' => $productId,
            'languange_id' => ($i + 1),
            'name' => ($productInfo['remarks'] == '') ? $productInfo['kategori_Produk'] : $productInfo['remarks'],
            'description' => ($productInfo['remarks'] == '') ? $productInfo['kategori_Produk'] : $productInfo['remarks'],
            'meta_title' => ($productInfo['remarks'] == '') ? $productInfo['kategori_Produk'] : $productInfo['remarks'],
            'meta_description' => ($productInfo['remarks'] == '') ? $productInfo['kategori_Produk'] : $productInfo['remarks'],
        );

        $sql = "INSERT INTO oc_product_description"
                . " (product_id,language_id,name,description,tag,meta_title,meta_description,meta_keyword) VALUES "
                . " ("
                . "'" . (int) $productDescription['product_id'] . "',"
                . "'" . (int) $productDescription['languange_id'] . "', "
                . "'" . $productDescription['name'] . "', "
                . "'" . $productDescription['description'] . "', "
                . "'', "
                . "'" . $productDescription['meta_title'] . "', "
                . "'" . $productDescription['meta_description'] . "', "
                . "''"
                . ") ";
        $sth = $pdo->prepare($sql);
        $sth->execute();
    endfor;
}

function InsertProductToCategory($productId, $categoryId) {
    $product = array('product_id' => $productId, 'category_id' => $categoryId);
    $sql = "INSERT INTO oc_product_to_category (product_id,category_id) VALUES ('" . (int) $product['product_id'] . "','" . (int) $product['category_id'] . "')";
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=tukangemas_sankyu', 'root', '');
    $sth = $pdo->prepare($sql);
    $sth->execute();
}

function InsertProductToStore($productId,$storeId = 0){
    $sql = "INSERT INTO oc_product_to_store (product_id,store_id) VALUES ('" . (int) $productId . "', '" . (int) $storeId . "')";
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=tukangemas_sankyu', 'root', '');
    $sth = $pdo->prepare($sql);
    $sth->execute();
}

function InsertProductToLayout($productId, $storeId, $layoutId){
    $sql = "INSERT INTO oc_product_to_layout (product_id,store_id,layout_id) VALUES ('" . (int) $productId . "', '" . (int) $storeId . "', '" . (int) $layoutId . "')";
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=tukangemas_sankyu', 'root', '');
    $sth = $pdo->prepare($sql);
    $sth->execute();
}
