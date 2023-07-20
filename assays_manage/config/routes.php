<?php
$container['BrowseAssaysManage'] = function($container) {
    return new assays_manage\controllers\BrowseAssaysManage($container);
};
$app->get("/", BrowseAssaysManage::class . ":browse_assays_manage")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['DatatablesBrowseAssaysManage'] = function($container) {
    return new assays_manage\controllers\DatatablesBrowseAssaysManage($container);
};
$app->post('/datatables_browse_assays_manage[/]', DatatablesBrowseAssaysManage::class . ":datatables_browse_assays_manage")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$app->get('/{laboratory_id}/{import_log_id}[/]', BrowseAssaysManage::class . ":browse_assays_manage")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['ApprovalProcess'] = function($container) {
    return new assays_manage\controllers\ApprovalProcess($container);
};
$app->post('/approval_process[/]', ApprovalProcess::class . ":approval_process")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['AddApprovalProcessNote'] = function($container) {
    return new assays_manage\controllers\AddApprovalProcessNote($container);
};
$app->post('/add_approval_process_note[/]', AddApprovalProcessNote::class . ":add_approval_process_note")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['GetNotes'] = function($container) {
    return new assays_manage\controllers\GetNotes($container);
};
$app->post('/get_notes[/]', GetNotes::class . ":get_notes")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['GetNotesTotals'] = function($container) {
    return new assays_manage\controllers\GetNotesTotals($container);
};
$app->post('/get_notes_totals[/]',  GetNotesTotals::class . ":get_notes_totals")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['GetImportLogsByLabId'] = function($container) {
    return new assays_manage\controllers\GetImportLogsByLabId($container);
};
$app->post('/get_import_logs_by_lab_id[/]', GetImportLogsByLabId::class . ":get_import_logs_by_lab_id")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['GetLaboratoryById'] = function($container) {
    return new assays_manage\controllers\GetLaboratoryById($container);
};
$app->post('/get_laboratory_by_id[/]', GetLaboratoryById::class . ":get_laboratory_by_id")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['SendEmail'] = function($container) {
    return new assays_manage\controllers\SendEmail($container);
};
$app->post('/send_email[/]', SendEmail::class . ":send_email")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['EmailCsv'] = function($container) {
    return new assays_manage\controllers\EmailCsv($container);
};
$app->post('/email_csv[/]', EmailCsv::class . ":email_csv")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

$container['DeleteNote'] = function($container) {
    return new assays_manage\controllers\DeleteNote($container);
};
$app->post('/delete_note[/]', DeleteNote::class . ":delete_note")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_approval", "manage_access"));

/*$app->post('/datatables_browse_assays_manage(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "datatables_browse_assays_manage");


$app->get('/(:laboratory_id)(/:import_log_id)(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "browse_assays_manage");

$app->post('/approval_process(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "approval_process");
$app->post('/add_approval_process_note(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "add_approval_process_note");

$app->post('/get_notes(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "get_notes");
$app->post('/get_notes_totals(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "get_notes_totals");
$app->post('/get_import_logs_by_lab_id(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "get_import_logs_by_lab_id");
$app->post('/get_laboratory_by_id(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "get_laboratory_by_id");
$app->post('/send_email(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "send_email");
$app->post('/email_csv(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "email_csv");
$app->post('/delete_note(/)', "check_authenticated", $apply_permissions("assay_approval", "manage_access"), "delete_note");*/