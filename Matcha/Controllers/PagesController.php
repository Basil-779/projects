<?php
namespace Controllers;
use \Models\User;
use \Models\UserManagePDO;
use \PDO;
use Controllers\Validator;


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
        $errors = [];

        if (!Validator::loginLengthCheck($request->getParam('login')))
        {
            $errors['login'] = 'Your username must contain between 2 and 32 chars.';
        }

        if (!Validator::loginCharsCheck($request->getParam('login')))
        {
            $errors['login'] = 'Your username must contain only letters, numbers, underscores and hyphens';
        }

        if (!Validator::nameCheck($request->getParam('firstName')))
        {
            $errors['firstName'] = 'The first name must contain only chars';
        }

        if (!Validator::nameCheck($request->getparam('lastName')))
        {
            $errors['lastName'] = 'The last name must contain only chars';
        }

        if (Validator::mailCheck($request->getParam('email'), $this->container->db) === INVALID_EMAIL)
        {
            $errors['email'] = 'E-mail is invalid';
        }

        elseif (Validator::mailCheck($request->getParam('email'), $this->container->db) === EMAIL_ALREADY_EXISTS)
        {
            $errors['email'] = 'E-mail already used';
        }

        if (Validator::passwordCheck($request->getParam('password')))
        {
            switch (Validator::passwordCheck($request->getParam('password')))
            {
                case 1:
                    $errors['password'] = 'Password too short';
                    break;
                case 2:
                    $errors['password'] = 'Password must contain at least 1 number';
                    break;
                case 3:
                    $errors['password'] = 'Password must contain at least 1 letter';
                break;
            }
        }

        if (Validator::ageCheck($request->getParam('age')))
        {
            $errors['age'] = 'You are too young';
        }

        if (empty($errors))
        {
            $user = new User([
                'login' => $request->getParam('login'),
                'email' => $request->getParam('email'),
                'firstName' => $request->getParam('firstName'),
                'lastName' => $request->getParam('lastName'),
                'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
                'age' => $request->getParam('age'),
            ]);

            $UserManagerPDO = new UserManagerPDO($this->db);
            $userManagerPDO->save($user);
            $last_id = $this->db->lastInsertId();
        }

        else
        {
            $this->flash('Something wend wrong', 'error');
            $this->flash($errors, 'errors');
            return $this->redirect($response, /**HOME PAGE */, 302);
        }

        $this->flash('A confirmation email was sent to you', 'info');
        return $this->redirect($response, /**LOGIN PAGE */, 200);
    }
}

?>