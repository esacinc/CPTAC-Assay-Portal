<?php
/**
 * @desc Import Assays: controller for browsing assay import sets
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function browse_assay_imports(){
	$app = \Slim\Slim::getInstance();
	$app->render('browse_assay_imports.php',array(
		  "page_title" => "Browse Imports"
		, "uniquehash" => uniqid()
	));
}
?>