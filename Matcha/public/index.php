<?php

use \App\Middlewares\FlashMiddleware;
use \App\Middlewares\OldMiddleware;

require '../vendor/autoload.php';

session_start();

$app = new \Slim\App((['settings' => ['displayErrorDetails' => true]]));

require ('../app/container.php');

$container = $app->getContainer();
$app->add(new FlashMiddleware($container->view->getEnviroment()));
$app->add(new OldMiddleware($container->view->getEnviroment()));

/**HOME PAGE */
$app->get('/', 'App\Controllers\PagesController:home')->setName('home');

/**REGISTRATION */
$app->get('/auth/signup', 'App\Controllers\PagesController:getSignUp')->setName('auth.signup');
$app->post('/auth/signup', 'App\Controllers\PagesController:postSignUp');

$app->get('/auth/signupinfos', 'App\Controllers\PagesController:getSignUpInfos')->setName('auth.signupinfos');
$app->post('/auth/signupinfos', 'App\Controllers\PasgesController:postSignUpInfos');

/**LOGIN */
$app->get('/auth/login', 'App\Controllers\PagesController:getLogIn')->setName('auth.login');
$app->post('/auth/login', 'App\Controllers\PagesController:postLogIn');

/**LOGOUT */
$app->get('/logout', 'App\Controllers\PageController:getLogOut')->setName('logout');

/**FORGOT PASSWORD */
$app->get('/auth/forgotpwd', 'App\Controllers\PagesController:getForgotPwd')->setName('forgotpwd');
$app->post('/auth/forgotpwd', 'App\Controllers\PagesController:postForgotPwd');

/**NEW PASSWORD */
$app->get('/auth/newpwd', 'App\Controllers\PagesController:getNewPwd')->setName('newpwd');
$app->post('/auth/newpwd', 'App\Controllers\PagesController:postNewPwd');

/**USER PROFILE */
$app->get('/profile/{userprofile}', 'App\Controllers\PagesController:getProfile')->setName('user.profile');
$app->post('/profile/{userprofile}', 'App\Controllers\PagesController:postProfile');

#PLACEHOLDER FOR USER ALIVE
$app->post('/like', 'App\Controllers\PagesController:postLike')->setName('like');
$app->post('/unlike', 'App\Controllers\PagesController:postUnLike')->setName('unlike');
$app->post('/block', 'App\Controllers\PagesController:postBlock')->setName('block');
$app->post('/unblock', 'App\Controllers\PagesController:postUnBlock')->setName('unblock');

/**SETTINGS */
$app->get('/settings', 'App\Controllers\PagesController:getSettings')->setName('user.settings');
$app->post('/settings', 'App\Controllers\PagesController:postSettings');

$app->get('/settings/changeMail', 'App\Controllers\PagesController:getChangeMail');
#PLACEHOLDER FOR UNBLOCKING SETTINGS

/**EDIT USERPROFILE */
$app->get('/edit', 'App\Controllers\PagesController:getEdit')->setName('user.edit');
$app->post('/edit', 'App\Controllers\PagesController:postEdit');

$app->post('/uploadpic', 'App\Controllers\PageController:postUploadPicture')->setName('upload.picture');
$app->post('/change_avatar', 'App\Controllers\PagesController:postChangeAvatar')->setName('change.picture');
$app->post('/deletepic', 'App\Controllers\PagesController:postDeletePicture')->setName('delete.picture');

/**
 * CHAT 
 * PLACEHOLDER
 */

 $app->run();