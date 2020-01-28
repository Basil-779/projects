<?php

use \Middlewares\FlashMiddleware;
use \Middlewares\OldMiddleware;

require '/vendor/autoload.php';

session_start();

$app = new \Slim\App((['settings' => ['displayErrorDetails' => true]]));
require ('container.php');

$container = $app->getContainer();
$app->add(new FlashMiddleware($container->view->getEnviroment()));
$app->add(new OldMiddleware($container->view->getEnviroment()));

/**HOME PAGE */
$app->get('/', 'Controllers\PagesController:home')->setName('home');

/**REGISTRATION */
$app->get('/auth/signup', 'Controllers\PagesController:getSignUp')->setName('auth.signup');
$app->post('/auth/signup', 'Controllers\PagesController:postSignUp');

$app->get('/auth/signupinfos', 'Controllers\PagesController:getSignUpInfos')->setName('auth.signupinfos');
$app->post('/auth/signupinfos', 'Controllers\PasgesController:postSignUpInfos');

/**LOGIN */
$app->get('/auth/login', 'Controllers\PagesController:getLogIn')->setName('auth.login');
$app->post('/auth/login', 'Controllers\PagesController:postLogIn');

/**LOGOUT */
$app->get('/logout', 'Controllers\PageController:getLogOut')->setName('logout');

/**FORGOT PASSWORD */
$app->get('/auth/forgotpwd', 'Controllers\PagesController:getForgotPwd')->setName('forgotpwd');
$app->post('/auth/forgotpwd', 'Controllers\PagesController:postForgotPwd');

/**NEW PASSWORD */
$app->get('/auth/newpwd', 'Controllers\PagesController:getNewPwd')->setName('newpwd');
$app->post('/auth/newpwd', 'Controllers\PagesController:postNewPwd');

/**USER PROFILE */
$app->get('/profile/{userprofile}', 'Controllers\PagesController:getProfile')->setName('user.profile');
$app->post('/profile/{userprofile}', 'Controllers\PagesController:postProfile');

#PLACEHOLDER FOR USER ALIVE
$app->post('/like', 'Controllers\PagesController:postLike')->setName('like');
$app->post('/unlike', 'Controllers\PagesController:postUnLike')->setName('unlike');
$app->post('/block', 'Controllers\PagesController:postBlock')->setName('block');
$app->post('/unblock', 'Controllers\PagesController:postUnBlock')->setName('unblock');

/**SETTINGS */
$app->get('/settings', 'Controllers\PagesController:getSettings')->setName('user.settings');
$app->post('/settings', 'Controllers\PagesController:postSettings');

$app->get('/settings/changeMail', 'Controllers\PagesController:getChangeMail');
#PLACEHOLDER FOR UNBLOCKING SETTINGS

/**EDIT USERPROFILE */
$app->get('/edit', 'Controllers\PagesController:getEdit')->setName('user.edit');
$app->post('/edit', 'Controllers\PagesController:postEdit');

$app->post('/uploadpic', 'Controllers\PageController:postUploadPicture')->setName('upload.picture');
$app->post('/change_avatar', 'Controllers\PagesController:postChangeAvatar')->setName('change.picture');
$app->post('/deletepic', 'Controllers\PagesController:postDeletePicture')->setName('delete.picture');

/**
 * CHAT 
 * PLACEHOLDER
 */

 $app->run();
?>