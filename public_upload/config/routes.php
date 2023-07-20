<?php
$container['InsertMetadata'] = function ($container) {
    return new public_upload\controllers\InsertMetadata($container);
};

$container['ShowDashboard'] = function ($container) {
    return new public_upload\controllers\ShowDashboard($container);
};

$container['InsertSopFile'] = function ($container) {
    return new public_upload\controllers\InsertSopFile($container);
};

$container['UpdateSopFileTypeId'] = function ($container) {
    return new public_upload\controllers\UpdateSopFileTypeId($container);
};

$container['ProcessFileUpload'] = function ($container) {
    return new public_upload\controllers\ProcessFileUpload($container);
};

$container['UploadSkylineFile'] = function ($container) {
    return new public_upload\controllers\UploadSkylineFile($container);
};

$container['SubmitSkylineFile'] = function ($container) {
    return new public_upload\controllers\SubmitSkylineFile($container);
};

$container['InsertUpdateAssaysImport'] = function ($container) {
    return new public_upload\controllers\InsertUpdateAssaysImport($container);
};

$container['DownloadFile'] = function ($container) {
    return new public_upload\controllers\DownloadFile($container);
};

$container['DeleteFile'] = function ($container) {
    return new public_upload\controllers\DeleteFile($container);
};

$container['DeleteFilePrePost'] = function ($container) {
    return new public_upload\controllers\DeleteFilePrePost($container);
};

$container['DownloadSopFile'] = function ($container) {
    return new public_upload\controllers\DownloadSopFile($container);
};

$container['DeleteInvestigator'] = function ($container) {
    return new public_upload\controllers\DeleteInvestigator($container);

};

$container['UploadFiles'] = function ($container) {
    return new public_upload\controllers\UploadFiles($container);
};

$container['AddInvestigator'] = function ($container) {
    return new public_upload\controllers\AddInvestigator($container);
};

$container['DeletePublication'] = function ($container) {
    return new public_upload\controllers\DeletePublication($container);
};

$container['BrowseAssaysPreview'] = function($container) {
    return new public_upload\controllers\BrowseAssaysPreview($container);
};

$container['DatatablesBrowseAssaysPreview'] = function($container) {
    return new public_upload\controllers\DatatablesBrowseAssaysPreview($container);
};

$container['SubmitProcess'] = function($container) {
    return new public_upload\controllers\SubmitProcess($container);
};

$container['SubmitAssays'] = function ($container) {
    return new public_upload\controllers\SubmitAssays($container);
};

$app->get('/', ShowDashboard::class . ':show_dashboard')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->get('/insert_metadata[/{assay_import_id}]', InsertMetadata::class . ':insert_metadata')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/insert_metadata[/{assay_import_id}]', InsertUpdateAssaysImport::class . ':insert_update_assays_import', InsertMetadata::class . ':insert_metadata')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->get('/upload_skyline_file', UploadSkylineFile::class . ':upload_skyline_file')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"))
    ->add( new \public_upload\controllers\CheckImportLogId($container));

$app->get('/submit_skyline_file', SubmitSkylineFile::class . ':submit_skyline_file')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/submit_skyline_file', SubmitSkylineFile::class . ':submit_skyline_file')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/insert_sop_file', InsertSopFile::class . ':insert_sop_file')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/update_sop_file_type_id', UpdateSopFileTypeId::class . ':update_sop_file_type_id')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/process_file_upload', ProcessFileUpload::class . ':process_file_upload')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/delete_publication/', DeletePublication::class . ':delete_publication')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/delete_file', DeleteFile::class. ":delete_file")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->get('/download_file[/{file_type}]', DownloadFile::class . ':download_file')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->get('/download_sop_file[/{file_id}]', DownloadSopFile::class . ':download_sop_file')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/delete_file_pre_post', DeleteFilePrePost::class. ":delete_file_pre_post")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/delete_investigator', DeleteInvestigator::class. ":delete_investigator")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/add_investigator', AddInvestigator::class. ":add_investigator")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/upload_files', UploadFiles::class . ':upload_files')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->post('/check_upload_files', UploadFiles::class . ':check_public_upload_files')
    ->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->get('/get_upload_files', UploadFiles::class . ':get_public_upload_files');


$container['BrowseAssayImports'] = function($container) {
    return new public_upload\controllers\BrowseAssayImports($container);
};

$container['DatatablesBrowseAssayImports'] = function($container) {
    return new public_upload\controllers\DatatablesBrowseAssayImports($container);
};

//$app->get("/preview_assays[/]", BrowseAssaysPreview::class . ":browse_assays_manage");

//$app->post('/datatables_browse_assays_manage[/]', DatatablesBrowseAssaysPreview::class . ":datatables_browse_assays_manage");

$app->post('/submit_process[/]', SubmitProcess::class . ":submit_process");

//$app->get("/browse_imports", BrowseAssayImports::class . ":browse_assay_imports");

$app->post("/datatables_browse_assay_imports[/]", DatatablesBrowseAssayImports::class . ":datatables_browse_assay_imports");

$app->get('/submit_assays[/]', SubmitAssays::class . ':submit_assays')
->add( new \authenticate\controllers\ApplyPermissions($container, "public_upload", "upload"));

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$container['GetImportLogsByLabId'] = function($container) {
    return new assays_manage\controllers\GetImportLogsByLabId($container);
};
$app->post('/get_import_logs_by_lab_id[/]', GetImportLogsByLabId::class . ":get_import_logs_by_lab_id");


$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});
