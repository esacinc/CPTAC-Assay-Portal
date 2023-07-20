<?php
function browse_user_accounts(){
	$app = \Slim\Slim::getInstance();
	$app->render('browse_user_accounts.php',array(
		"page_title" => "Browse User Accounts"
	));
}
?>