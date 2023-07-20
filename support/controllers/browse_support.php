<?php
function browse_support(){
	$app = \Slim\Slim::getInstance();
	$app->render('browse_support.php',array(
		"page_title" => "Support Requests"
	));
}
?>