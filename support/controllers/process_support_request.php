<?php 
function process_support_request(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	//require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
	require_once $_SERVER["PATH_TO_CORE"] . "3rd_party/securimage/securimage.php";
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"],$final_global_template_vars["site_key"]);
	$gump = new GUMP();
	$errors = array();
	$rules = array(
		"support_category_id" => "required|numeric"
		,"title" => "required"
		,"body" => "required"
	);
	if(!isset($_SESSION[$final_global_template_vars["session_key"]])){
		$rules = array_merge($rules,array(
			"first_name" => "required"
			,"last_name" => "required"
			,"email" => "required|valid_email"
		));
		$securimage = new Securimage();
		if ($securimage->check($app->request()->post('captcha')) == false) {
			$errors["captcha"] = "Captcha is invalid.";
		}
	}
	$validated = $gump->validate($app->request()->post(), $rules);
	
	if($validated !== TRUE){
		$other_errors = \swpg\models\utility::gump_parse_errors($validated);
		$errors = array_merge($errors,$other_errors);
	}
	if(isset($_FILES['support_file'])){
		if(!in_array($_FILES['support_file']['type'],$final_global_template_vars["file_validation"]['allowed_mime_types'])){
			$errors["support_file"] = "File type is invalid: " . $_FILES['support_file']['type'];
		}
		if($_FILES['support_file']['size'] > $final_global_template_vars["file_validation"]['max_size']){
			$errors["support_file"] = "File is too large";
		}
	}
	
	if(!$errors){
		$support->insert_support($app->request()->post(),$_FILES);
		$app->redirect($final_global_template_vars["path_to_this_module"] . "/success");
	}else{
		$env = $app->environment();
		$env["swpg_validation_errors"] = $errors;
	}
}
?>