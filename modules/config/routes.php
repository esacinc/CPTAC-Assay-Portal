<?php
$container = $app->getContainer();

$container['ShowDashboard'] = function($container) {
    return new modules\controllers\ShowDashboard($container);
};

// Define routes
$app->get('/', ShowDashboard::class .":show_dashboard")
    ->add(new authenticate\controllers\CheckAuthenticated($container));
