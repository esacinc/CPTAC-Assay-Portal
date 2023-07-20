<?php
//Define Public Routes
$container['ShowSupportForm'] = function ($container) {
    return new support\controllers\ShowSupportForm($container);
};
$container['ProcessSupportRequest'] = function ($container) {
    return new support\controllers\ProcessSupportRequest($container);
};


$app->get('/', ShowSupportForm::class . ':show_support_form');



/*
$app->get('/', "show_support_form"); 
$app->post('/', "process_support_request", "show_support_form");
$app->get('/success(/)', "show_support_success"); 

// Define Admin Routes
$app->get('/browse(/)', "check_authenticated", $apply_permissions("support", "browse_access"), "browse_support"); 
$app->post('/datatables_browse_support(/)', "check_authenticated", $apply_permissions("support", "browse_access"), "datatables_browse_support"); 
$app->post('/delete(/)', "check_authenticated", $apply_permissions("support", "manage_access"), "delete_support");

$app->get('/details/:support_id(/)', "check_authenticated", $apply_permissions("support", "manage_access"), "show_details"); 
$app->get('/download/:file_id(/)', "check_authenticated", $apply_permissions("support", "manage_access"), "download_file"); 

$app->get('/settings(/)', "check_authenticated", $apply_permissions("support", "settings_access"), "show_settings_form"); 
$app->post('/settings(/)', "check_authenticated", $apply_permissions("support", "settings_access"), "insert_update_settings", "show_settings_form"); 

$app->get('/categories(/)', "check_authenticated", $apply_permissions("support", "manage_access"), "browse_categories"); 
$app->post('/datatables_browse_categories(/)', "check_authenticated", $apply_permissions("support", "manage_access"), "datatables_browse_categories");
$app->post('/categories/delete(/)', "check_authenticated", $apply_permissions("support", "settings_access"), "delete_category");

$app->get('/categories/manage(/:category_id)(/)', "check_authenticated", $apply_permissions("support", "settings_access"), "show_category_form"); 
$app->post('/categories/manage(/:category_id)(/)', "check_authenticated", $apply_permissions("support", "settings_access"), "insert_update_category", "show_category_form");
*/
?>