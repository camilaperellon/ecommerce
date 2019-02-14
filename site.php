<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;


$app->get('/', function() {

	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

$app->get('/categories/:idcategory', function($idcategory) {

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination["pages"]; $i++) { 
		array_push($pages, [
			'link'=>'/curso/Ecommerce/index.php/categories/' . $category->getidcategory() . '?page=' . $i,
			'page'=>$i
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);
});

$app->get('/products/:desurl', function($desurl) {

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});

$app->get('/cart', function() {

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

$app->get('/cart/:idproduct/add', function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 
		$cart->addProduct($product);
	}

	header("Location: /curso/Ecommerce/index.php/cart");
	exit;

});

$app->get('/cart/:idproduct/minus', function($idproduct) { //remover um

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /curso/Ecommerce/index.php/cart");
	exit;

});

$app->get('/cart/:idproduct/remove', function($idproduct) { //remover todos

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /curso/Ecommerce/index.php/cart");
	exit;

});

$app->post('/cart/freight', function() { //remover todos

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /curso/Ecommerce/index.php/cart");
	exit;

});

$app->get('/checkout', function() {

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout",[
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});

$app->get('/login', function() {

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] :['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});

$app->post('/login', function() {

	try{

		User::login($_POST['login'], $_POST['password']);

	}catch(Exception $e){

		User::setError($e->getMessage());
		
	}
	header("Location: /curso/Ecommerce/index.php/checkout");
	exit;
});

$app->get('/logout', function() {

	User::logout();

	header("Location: /curso/Ecommerce/index.php/login");
	exit;
});

$app->post('/register', function() {

	$_SESSION['registerValues'] = $_POST;

	if(!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("Preencha seu nome");

		header("Location: /curso/Ecommerce/index.php/login");
		exit;
	}

	if(!isset($_POST['email']) || $_POST['email'] == ''){

		User::setErrorRegister("Preencha seu email");

		header("Location: /curso/Ecommerce/index.php/login");
		exit;
	}

	if(!isset($_POST['password']) || $_POST['password'] == ''){

		User::setErrorRegister("Preencha sua senha");

		header("Location: /curso/Ecommerce/index.php/login");
		exit;
	}

	if(User::checkLoginExists($_POST['email']) === true){

		User::setErrorRegister("Este endereço de e-mail já está cadastrado.");

		header("Location: /curso/Ecommerce/index.php/login");
		exit;
	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'],$_POST['password']);

	header("Location: /curso/Ecommerce/index.php/checkout");
	exit;
});

$app->get('/forgot', function() {

	$page = new Page();

	$page->setTpl("forgot");


});

$app->post('/forgot', function() {

	$user = User::getForgot($_POST["email"], false);

	header("Location: /curso/Ecommerce/index.php/forgot/sent");
	exit;

});

$app->get('/forgot/sent', function() {

	$page = new Page();

	$page->setTpl("forgot-sent");

});

$app->get('/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post('/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgetUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

$app->get('/profile', function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);

});

$app->post('/profile', function() {

	User::verifyLogin(false);

	if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){

		User::setError("Preencha seu nome");
		header("Location: /curso/Ecommerce/index.php/profile");
		exit;
	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){

		User::setError("Preencha seu email");
		header("Location: /curso/Ecommerce/index.php/profile");
		exit;
	}
	
	$user = User::getFromSession();

	if($_POST['desemail'] !== $user->getdesemail()){

		if(User::checkLoginExists($_POST['desemail'])){

			User::setError("Este e-mail já está cadastrado");
		}
	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	User::setSuccess("Dados Alterados com Sucesso");

	header("Location: /curso/Ecommerce/index.php/profile");
	exit;

});

?>