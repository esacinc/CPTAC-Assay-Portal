<?php
// Define routes

$container['BrowseUserAccounts'] = function($container) {
    return new users\controllers\BrowseUserAccounts($container);
};
$app->get("/", BrowseUserAccounts::class . ":browse_user_accounts")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['DatatablesBrowseUserAccounts'] = function($container) {
        return new users\controllers\DatatablesBrowseUserAccounts($container);
};
$app->post("/datatables_browse_user_accounts[/]", DatatablesBrowseUserAccounts::class . ":datatables_browse_user_accounts")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));
/*
$app->get('/', "check_authenticated", $apply_permissions("user_account", "browse_access"), "browse_user_accounts");
$app->post('/datatables_browse_user_accounts(/)', "check_authenticated", $apply_permissions("user_account", "browse_access"), "datatables_browse_user_accounts");
*/

//$app->get('/manage[/{account_id}/]', "check_authenticated", $apply_permissions("user_account", "manage_access"), $user_account_permissions, "show_user_account_form");

$container['ShowUserAccountForm'] = function($container) {
    return new users\controllers\ShowUserAccountForm($container);
};

$app->get('/manage[/{account_id}]',ShowUserAccountForm::class . ":show_user_account_form")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['InsertUpdateUserAccount'] = function($container) {
        return new users\controllers\InsertUpdateUserAccount($container);
};


$app->post('/manage[/{account_id}]',InsertUpdateUserAccount::class . ":insert_update_user_account")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

    $container['DeleteUserAccount'] = function($container) {
            return new users\controllers\DeleteUserAccount($container);
    };


    $app->post('/delete[/]',DeleteUserAccount::class . ":delete_user_account")
        ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

/*
$app->post('/manage(/:account_id)(/)', "check_authenticated", $apply_permissions("user_account", "manage_access"), $user_account_permissions,"show_user_account_form");

$app->get('/preferences(/)', "check_authenticated", "show_preferences_form");
$app->post('/preferences(/)', "check_authenticated", "update_preferences", "show_preferences_form");

$app->get('/find(/)', "check_authenticated", $apply_permissions("user_account", "manage_access"), "show_find_user_account_form");
$app->post('/find(/)', "check_authenticated", "find_user_account");

$app->post('/delete(/)', "check_authenticated", $apply_permissions("user_account", "manage_access"), $user_account_delete_permissions, "delete_user_account");
*/
?>
