<?php
function browse_groups(){
	$app = \Slim\Slim::getInstance();
	$app->render('browse_groups.php',array(
		"page_title" => "Browse Groups"
	));
}
?>