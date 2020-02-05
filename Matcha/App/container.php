<?php
$container = $app->getContainer();

$container['debug'] = function()
{
    return false;
};

$container['db'] = function($container)
{
    $db = \App\Models\DBFactory::getMysqlConnectionWithPDO();
    return $db;
};

$container['view'] = function($container)
{
    $dir = dirname(__DIR__);
    $view = new \Slim\Views\Twig($dir . '/app/views', ['cache' => false, 'debug' => $container->debug]);

    if ($container->debug)
    {
        $view->addExtension(new Twig_Extension_Debug());
    }

    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
}
?>