<?php
namespace \Controller;
use \Models\User;
use \Models\UserManagePDO;
use \PDO;
use \Controller\Validator;


class PagesController extends Controller 
{
    public function getSignUp($request, $response)
    {
        if (!Validator::isConnected())
        {
        return $this->render($response, /**SIGN UP PAGE */);
        }
        else
        {
            return $this->redirect($response, /**HOME PAGE */, 200);
        }
    }
    public function postSignUp($request, $response)
    {
        $json_str = file_get_contents('php://input');

        $json_obj = json_decode($json_str);
        echo $json_obj;
    }
}

?>