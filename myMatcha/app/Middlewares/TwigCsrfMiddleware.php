<?php
namespace App\Middlewares;
use Slim\Csrf\Guard;

class TwigCsrfMiddleware
{
    private $twig;

    public function __construct(\Twig_Environment $twig, Guard $csrf)
    {
        $this->twig = $twig;
        $this->csrf = $csrf;
    }

    public function __invoke($request, $response, $next)
    {
        $csrf = $this->csrf;
        $this->twig->addFunction(new \Twig_SimpleFunction('csrf', function() use ($csrf, $request)
        {
            $nameKey = $csrf->getTokenNameKey();
            $valueKey = $csrf->getTokenValueKey();
            $name = $request->getAttribute($namekey);
            $value = $request->getAttribute($valuekey);
            return "
            <input type=\"hidden\" name=\"$nameKey\" value=\"$name\">
            <input type=\"hidden\" name=\"$valueKey\" value=\"$value\">
            ";
        }, ['is_safe' => ['html']]
    ));
    return $next($request, $response);
    }
}

?>