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

/** HOME PAGE */
$app->get('/', 'App\Controllers\PagesController:main')->setName('main');
$app->post('/', 'App\Controllers\PagesController:postMain');

/** CHATS **/
$app->get('/chat', 'App\Controllers\PagesController:getChatlist')->setName('chatlist');
$app->post('/chat', 'App\Controllers\PagesController:postChatlist');

$app->get('/404', 'App\Controllers\PagesController:getNotFound')->setName('not.found');
$app->post('/404', 'App\Controllers\PagesController:postNotFound');

/** CHAT **/
$app->get('/chat/id={chat_id}', 'App\Controllers\PagesController:getChat');
$app->post('/chat/id={chat_id}', 'App\Controllers\PagesController:postChat');

/** REGISTRATION */
$app->get('/account/register', 'PagesController:getSignUp')->setName('auth.signup');
$app->post('/account/register', 'PagesController:postSignUp');

/** CONFIRMATION */
$app->get('/account/confirm/{hash}', 'PagesController:confirmEmail')->setName('auth.confirm');

/** FORGOT PASSWORD */
$app->get('/account/resetpass', 'App\Controllers\PagesController:getForgotPwd')->setName('forgotpwd');
$app->post('/account/resetpass', 'App\Controllers\PagesController:postForgotPwd');

/** NEW PASSWORD */
$app->get('/account/newpass/{hash}', 'App\Controllers\PagesController:getChangePassword')->setName('changepwd');
$app->post('/account/newpass/{hash}', 'App\Controllers\PagesController:postChangePassword');

/** LOGIN **/
$app->get('/account/login', 'App\Controllers\PagesController:getLogIn')->setName('auth.login');
$app->post('/account/login', 'App\Controllers\PagesController:postLogIn');

/** PROFILE **/
$app->get('/profile/id={id}', 'App\Controllers\PagesController:getProfile')->setName('profile');

/** SETTINGS **/
$app->get('/settings', 'App\Controllers\PagesController:getSettings')->setName('settings');
$app->post('/settings', 'App\Controllers\PagesController:postSettings');

/** ADDITIONAL SETTINGS **/
$app->get('/settings/additional', 'App\Controllers\PagesController:getSignUpInfos')->setName('addSettings');
$app->post('/settings/additional', 'App\Controllers\PagesController:postSignUpInfos');

/** LOGOUT **/
$app->get('/logout', 'App\Controllers\PagesController:getLogOut')->setName('logout');

/** LIKELIST **/
$app->get('/likelist', 'App\Controllers\PagesController:getLikelist')->setName('likelist');
$app->post('/likelist', 'App\Controllers\PagesController:postLikelist');

/** BLOCKLIST **/
$app->get('/blocklist', 'App\Controllers\PagesController:getBlocklist')->setName('blocklist');
$app->post('/blocklist', 'App\Controllers\PagesController:postBlocklist');

/** NOTIFICATIONLIST **/
$app->get('/notifications', 'App\Controllers\PagesController:getNotificationlist')->setName('notificationlist');
$app->post('/notifications', 'App\Controllers\PagesController:postNotificationlist');

 $app->run();