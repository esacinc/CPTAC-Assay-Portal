<?php
/**
 * @desc Import module routes
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

$container['BrowseAssayImports'] = function($container) {
    return new assays_import\controllers\BrowseAssayImports($container);
};
$app->get("/", BrowseAssayImports::class . ":browse_assay_imports")
->add( new \authenticate\controllers\ApplyPermissions($container, "import", "submit_import"));


$container['DatatablesBrowseAssayImports'] = function($container) {
    return new assays_import\controllers\DatatablesBrowseAssayImports($container);
};
$app->post("/datatables_browse_assay_imports[/]", DatatablesBrowseAssayImports::class . ":datatables_browse_assay_imports");

$container['InsertUpdate'] = function($container) {
    return new assays_import\controllers\InsertUpdate($container);
};
$app->get("/insert_update[/{import_log_id}]", InsertUpdate::class . ":insert_update");

$container['InsertUpdateAssaysImport'] = function($container) {
    return new assays_import\controllers\InsertUpdateAssaysImport($container);
};
$app->post("/insert_update[/{import_log_id}]",  InsertUpdateAssaysImport::class . ":insert_update_assays_import", InsertUpdate::class . ":insert_update");

$container['ProcessFileUpload'] = function($container) {
    return new assays_import\controllers\ProcessFileUpload($container);
};
$app->get("/process_file_upload[/]",  ProcessFileUpload::class . ":process_file_upload");
$app->post("/process_file_upload[/]",  ProcessFileUpload::class . ":process_file_upload");

$container['InsertSopFile'] = function($container) {
    return new assays_import\controllers\InsertSopFile($container);
};
$app->post("/insert_sop_file[/]",  InsertSopFile::class . ":insert_sop_file");

$container['DeleteFile'] = function($container) {
    return new assays_import\controllers\DeleteFile($container);
};
$app->post("/delete_file/{file_id}[/]", DeleteFile::class . ":delete_file");

$container['DeleteFilePrePost'] = function($container) {
    return new assays_import\controllers\DeleteFile($container);
};
$app->post("/delete_file_pre_post/{file_id}[/]", DeleteFilePrePost::class . ":delete_file_pre_post");

$container['DownloadFile'] = function($container) {
    return new assays_import\controllers\DownloadFile($container);
};
$app->get("/download_file/{file_id}[/]", DownloadFile::class . ":download_file");

$container['UpdateSopFileTypeId'] = function($container) {
    return new assays_import\controllers\UpdateSopFileTypeId($container);
};
$app->post("/update_sop_file_type_id[/]", UpdateSopFileTypeId::class . ":update_sop_file_type_id");

$container['DeletePublication'] = function($container) {
    return new assays_import\controllers\DeletePublication($container);
};
$app->post("/delete_publication/{publication_id}[/]", DeletePublication::class . ":delete_publication");

$container['ImportPanoramaProteinPeptide'] = function($container) {
    return new assays_import\controllers\ImportPanoramaProteinPeptide($container);
};
$app->get("/import_panorama_protein_peptide[/]", ImportPanoramaProteinPeptide::class . ":import_panorama_protein_peptide");

$container['ImportPanoramaDataController'] = function($container) {
    return new assays_import\controllers\ImportPanoramaDataController($container);
};
$app->get("/import_panorama_data[/]", ImportPanoramaDataController::class . ":import_panorama_data");

$container['ExecuteImport'] = function($container) {
    return new assays_import\controllers\ExecuteImport($container);
};
$app->get("/execute[/]", ExecuteImport::class . ":execute_import");
$app->post("/execute[/]", ExecuteImport::class . ":execute_import");

$container['ShowImportLog'] = function($container) {
    return new assays_import\controllers\ShowImportLog($container);
};
$app->get("/show_import_log[/]", ShowImportLog::class . ":show_import_log");

$container['DeleteImport'] = function($container) {
    return new assays_import\controllers\DeleteImport($container);
};
$app->post("/delete_import[/]", DeleteImport::class . ":delete_import");

$container['ResetImport'] = function($container) {
    return new assays_import\controllers\ResetImport($container);
};
$app->post("/reset_import[/]", ResetImport::class . ":reset_import");

$container['SendErrorReportEmail'] = function($container) {
    return new assays_import\controllers\SendErrorReportEmail($container);
};
$app->post("/send_error_report_email[/]", SendErrorReportEmail::class . ":send_error_report_email");

$container['FixUniprotImport'] = function($container) {
    return new assays_import\controllers\FixUniprotImport($container);
};
$app->get("/fix_uniprot_import/{import_log_id}[/]", FixUniprotImport::class . ":fix_uniprot_import");

$container['ReadImportLog'] = function($container) {
    return new \assays_import\controllers\ReadImportLog($container);
};
$app->get("/read_import_log", ReadImportLog::class . ":read_import_log");
$app->get("/download_import_log", ReadImportLog::class . ":download_import_log");

/*$app->get('/', "check_authenticated", $apply_permissions("import", "submit_access"), "browse_assay_imports");

$app->post('/datatables_browse_assay_imports(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "datatables_browse_assay_imports");
$app->get('/insert_update(/:assay_import_id)(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "insert_update");
$app->post('/insert_update(/:assay_import_id)(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "insert_update_assays_import", "insert_update");
$app->get('/process_file_upload(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "process_file_upload");
$app->post('/process_file_upload(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "process_file_upload");
$app->post('/insert_sop_file(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "insert_sop_file");
$app->post('/delete_file(/:file_id)(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "delete_file");
$app->post('/delete_file_pre_post(/:file_id)(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "delete_file_pre_post");
$app->get('/download_file(/:file_id)(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "download_file");
$app->post('/update_sop_file_type_id(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "update_sop_file_type_id");
$app->post('/delete_publication(/:publication_id)(/)', "check_authenticated", $apply_permissions("import", "submit_access"), "delete_publication");

$app->get('/import_panorama_protein_peptide(/:import_log_id)(/:uniquehash)(/)', "import_panorama_protein_peptide");
$app->get('/import_panorama_data(/:import_log_id)(/:imports_executed_log_id)(/:uniquehash)(/)', "import_panorama_data");
$app->get('/execute(/:import_log_id)(/)', "check_authenticated", $apply_permissions("import", "submit_import"), "execute_import");
$app->post('/execute(/)', "check_authenticated", $apply_permissions("import", "submit_import"), "execute_import");
$app->post('/delete_import(/)', "check_authenticated", $apply_permissions("import", "submit_import"), "delete_import");
$app->post('/reset_import(/)', "check_authenticated", $apply_permissions("import", "submit_import"), "reset_import");
$app->post('/send_error_report_email(/)', "check_authenticated", $apply_permissions("import", "submit_import"), "send_error_report_email");
$app->get('/fix_uniprot_import(/:import_log_id)(/)', "check_authenticated", $apply_permissions("import", "submit_import"), "fix_uniprot_import");*/
