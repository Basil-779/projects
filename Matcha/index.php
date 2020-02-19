<?php

use \App\Middlewares\FlashMiddleware;
use \App\Middlewares\OldMiddleware;

require 'vendor/autoload.php';

session_start();

$app = new \Slim\App((['settings' => ['displayErrorDetails' => true]]));

require ('app/container.php');

$container = $app->getContainer();
$app->add(new FlashMiddleware($container->view->getEnvironment()));
$app->add(new OldMiddleware($container->view->getEnvironment()));

/**HOME PAGE */
$app->get('/', 'App\Controllers\PagesController:home')->setName('home');

/**REGISTRATION */
$app->get('/account/register', 'PagesController:getSignUp')->setName('auth.signup');
$app->post('/account/register', 'PagesController:postSignUp');

/**CONFIRMATION */
$app->get('/account/confirm/{hash}', 'PagesController:confirmEmail')->setName('auth.confirm');

/**FORGOT PASSWORD */
$app->get('/account/resetpass', 'App\Controllers\PagesController:getForgotPwd')->setName('forgotpwd');
$app->post('/account/resetpass', 'App\Controllers\PagesController:postForgotPwd');

/**NEW PASSWORD */
$app->get('/account/newpass/{hash}', 'App\Controllers\PagesController:getChangePassword')->setName('changepwd');
$app->post('/account/newpass/{hash}', 'App\Controllers\PagesController:postChangePassword');

/** LOGIN **/
$app->get('/account/login', 'App\Controllers\PagesController:getLogIn')->setName('auth.login');
$app->post('/account/login', 'App\Controllers\PagesController:postLogIn');

/** PROFILE **/
$app->get('/profile', 'App\Controllers\PagesController:getProfile')->setName('profile');

/** SETTINGS **/
$app->get('/settings', 'App\Controllers\PagesController:getSettings')->setName('settings');

/** ADDITIONAL SETTINGS **/
$app->get('/settings/additional', 'App\Controllers\PagesController:getSignUpInfos')->setName('addSettings');
$app->post('/settings/additional', 'App\Controllers\PagesController:postSignUpInfos');

/** LOGOUT **/
$app->get('/logout', 'App\Controllers\PagesController:getLogOut')->setName('logout');

 $app->run();