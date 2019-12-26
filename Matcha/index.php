<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ .  '/vendor/autoload.php';

$app = new \Slim\App([
   'settings' =>  ['displayErrorDetails' => true]
]);

/*$container = $app->getContainer();

$container['greeting'] = function() {
    return 'Hello from a container';
};


$app->get('/article', function () {
    echo $this->greeting;}
);
*/
$container = $app->getContainer();

$container['nothing'] = function() {
    return 'This is actually nothing';
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/resources/views', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$app->get('/', function($request, $response) {
    return $this->view->render($response, 'home.twig');
});

$app->get('/account', function($request, $response) {
    return $this->view->render($response, 'account.twig');
});

$app->get('/chats', function($request, $response) {
    return $this->view->render($response, 'chats.twig');
});

$app->get('/match', function($request, $response) {
    return $this->view->render($response, 'match.twig');
});

$app->get('/users', function($request, $response) {

    $users = [
        ['username' => 'mbennis'],
        ['username' => 'pkerstin'],
    ];

    return $this->view->render($response, 'users.twig', ['users' => $users,]);
});


$app->run();