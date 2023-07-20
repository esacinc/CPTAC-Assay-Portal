<?php

$container = $app->getContainer();

set_current_module($app, "assays", "/assays");

$container['BrowseAssays'] = function($container) {
    return new assays\controllers\BrowseAssays($container);
};

$container['AssayDetails'] = function($container) {
    return new assays\controllers\AssayDetails($container);
};

$container['Home'] = function($container) {
    return new assays\controllers\Home($container);
};
$app->get('/', Home::class . ':home');


//$app->get('/', BrowseAssays::class . ':browse_assays');

$app->get('/available_assays[/]', BrowseAssays::class . ':browse_assays');

$app->get('/{assay_type}-{assay_id:[0-9]+}', AssayDetails::class . ':show_assay_details');
