<?php
function show_find_user_account_form(){
	$app = \Slim\Slim::getInstance();
	$app->render('find_user_account.php',array(
		"page_title" => "Find User Account"
	));  
}
?>