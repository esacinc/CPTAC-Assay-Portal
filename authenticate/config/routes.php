<?php
$container = $app->getContainer();

$container['ShowLoginForm'] = function($container) {
    return new authenticate\controllers\ShowLoginForm($container);
};

$container['OAuth'] = function($container) {
    return new authenticate\controllers\OAuth($container);
};

$container['ShowRegisterForm'] = function($container) {
    return new authenticate\controllers\ShowRegisterForm($container);
};

$container['PasswordRecoveryForm'] = function($container) {
    return new authenticate\controllers\PasswordRecoveryForm($container);
};

$container['PasswordRecoveryConfirmForm'] = function($container) {
    return new authenticate\controllers\PasswordRecoveryConfirmForm($container);
};

$container['RegistrationConfirm'] = function($container) {
    return new authenticate\controllers\RegistrationConfirm($container);
};

$container['Logout'] = function($container) {
    return new authenticate\controllers\Logout($container);
};

$container['ShowAccessDenied'] = function($container) {
    return new authenticate\controllers\ShowAccessDenied($container);
};


// Define authentication module routes
$app->get('/', \ShowLoginForm::class . ":show_login_form");
$app->post('/', \ShowLoginForm::class . ":show_login_form")
    ->add(new authenticate\controllers\AuthenticateUser($container));

$app->get('/oauth/[{register}]', \OAuth::class . ":oauth");

$app->get('/register[/]', \ShowRegisterForm::class . ":show_register_form");
$app->post('/register[/]', \ShowRegisterForm::class . ":show_register_form")
    ->add(new authenticate\controllers\RegisterUser($container));
$app->get('/register/confirm[/]', \RegistrationConfirm::class . ":confirm_registration");


$app->get('/password_recovery[/]', \PasswordRecoveryForm::class . ":show_password_recovery_form");
$app->post('/password_recovery[/]', PasswordRecoveryForm::class . ":submit_password_recovery");

$app->get('/password_recovery/confirm[/]', \PasswordRecoveryConfirmForm::class . ":show_password_recovery_confirm_form");
$app->post('/password_recovery/confirm[/]', \PasswordRecoveryConfirmForm::class . ":confirm_password_recovery");

$app->get('/logout[/]', \Logout::class . ":logout");

$app->get('/access_denied[/]', ShowAccessDenied::class . ":show_access_denied");