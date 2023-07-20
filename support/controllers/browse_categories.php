<?php
function browse_categories(){
	$app = \Slim\Slim::getInstance();
	$app->render('browse_categories.php',array(
		"page_title" => "Support Categories"
	));
}
?>