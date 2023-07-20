<?php

$container['BrowseGroups'] = function($container) {
    return new group\controllers\BrowseGroups($container);
};
$app->get("/", BrowseGroups::class . ":browse_groups")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "group", "manage_access"));

$container['DatatablesBrowseGroups'] = function($container) {
            return new group\controllers\DatatablesBrowseGroups($container);
};
$app->post("/datatables_browse_groups[/]", DatatablesBrowseGroups::class . ":datatables_browse_groups")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "group", "manage_access"));

$container['ShowGroupForm'] = function($container) {
        return new group\controllers\ShowGroupForm($container);
};
$app->get("/manage[/{group_id}]", ShowGroupForm::class . ":show_group_form")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "group", "manage_access"));

/*
$container['InsertUpdateGroup'] = function($container) {
        return new group\controllers\InsertUpdateGroup($container);
};


$app->post('/manage[/{group_id}]',InsertUpdateGroup::class . ":insert_update_group")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));
*/

$container['DeleteGroup'] = function($container) {
       return new group\controllers\DeleteGroup($container);
};

$app->post('/delete[/]',DeleteGroup::class . ":delete_group")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));
