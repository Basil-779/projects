<?php
/* 
USE IN INDEX 
use \Middleware\FlashMiddleware 
*/

namespace Middlewares;

class FlashMiddleware
{
    private $twig;

    public function __construct(\Twig_Enviroment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke($request, $response, $next)
    {
        $this->twig->addGlobal('flash', isset($_SESSION['flash']) ? $_SESSION['flash'] : []);
        if (isset($_SESSION['flash']))
        {
            unset($_SESSION['flash']);
        }
        return $next($request, $response);
    }
}

?>