<?php
$container['Tutorials'] = function($container) {
    return new tutorials\controllers\Tutorials($container);
};
$app->get("/",Tutorials::class . ":tutorials")
    ->add( new \authenticate\controllers\ApplyPermissions($container, "assay_preview", "browse_access"));
?>
