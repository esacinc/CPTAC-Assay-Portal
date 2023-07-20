<?php 
function show_support_success(){
	$app = \Slim\Slim::getInstance();
	$app->render('support_success.php',array(
		"page_title" => "Support Request Submitted"
	)); 
}
?>