<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Products;

$app->get("/admin/products", function(){

	User::verifyLogin();

	$products = Products::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
	]);

});

?>