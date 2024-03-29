<?php
namespace App\Controllers;
use App\Models\UserManagerPDO;
use App\Models\User;
use App\Controllers\Validator;
use \PDO;

class Controller
{
	protected $container;

	function __construct($container) {
		$this->container = $container;
	}

	public function render($response, $file, $params = [])
	{
		$this->container->view->render($response, $file, $params);
	}

	public function redirect($response, $name, $code){
		return ($response->withStatus($code)->withHeader('Location', $this->router->pathFor($name)));
	}

	public function __get($name) {
		return $this->container->get($name);
	}
	
	public function flash($message, $type = 'success'){
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        return $_SESSION['flash'][$type] = $message;
    }
}