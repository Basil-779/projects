<?php
namespace App\Controllers;
use App\Models\User;
use App\Models\UserManagerPDO;
use \PDO;
use App\Controllers\Validator;
use \App\Middlewares\FlashMiddleware;
use \App\Middlewares\OldMiddleware;
include "mail.php";
/**
 * NAMING FAQ
 * 
 * 'SOMETHING' HERE - REQUIRES TWIG PAGE
 * 
 * auth.login/forget pwd/etc - setName params DO NOT TOUCH
 *                                            ^^^ O'RLY
 */

class PagesController extends Controller 
{
	public function confirmEmail($request, $response, $args)
    {
        $UserManagerPDO = new UserManagerPDO($this->db);
		return $UserManagerPDO->activateUser($args['hash']);
    }
    
    public function changePassword($request, $response, $args)
    {
        $UserManagerPDO = new UserManagerPDO($this->db);
        if (Validator::passwordCheck($request->getParam('newPassword')))
        {
            switch (Validator::passwordCheck($request->getParam('newPassword')))
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
        $newpass = password_hash($request->getParam('newPassword'), PASSWORD_DEFAULT);
		return $UserManagerPDO->setNewPassword($args['hash'], $newpass);
    }
	
	public function getSignUp($request, $response)
    {
		if (!Validator::isConnected())
        {
			return $this->render($response, '/accountRegister.twig');
        }
        else
        {
            return $this->redirect($response, 'home', 200);
        }
    }
    public function postSignUp($request, $response)
    {
        $errors = [];

        if (!Validator::loginAvailability($request->getParam('login'), $this->container->db))
        {
            $errors['login'] = 'This login is already uses.';
        }
		
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

        /*if (Validator::mailCheck($request->getParam('email'), $this->container->db) === INVALID_EMAIL)
        {
            $errors['email'] = 'E-mail is invalid';
        }

        else if (Validator::mailCheck($request->getParam('email'), $this->container->db) === EMAIL_ALREADY_EXISTS)
        {
            $errors['email'] = 'E-mail already used';
        }*/

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
           $hash = hash(md5, $request->getParam('login'));
		   $email = $request->getParam('email');
		   
		   $user = new User([
                'login' => $request->getParam('login'),
                'email' => $email,
                'firstName' => $request->getParam('firstName'),
                'lastName' => $request->getParam('lastName'),
                'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
                'age' => $request->getParam('age'),
				'hash' => $hash,
            ]);
			
            $userManagerPDO = new UserManagerPDO($this->db);
            $userManagerPDO->save($user);
            $last_id = $this->db->lastInsertId();
			confirmSignUp($email, $hash);
			return "OK";
        }
        else
        {
			return var_dump($errors);
        }
    }
	
    public function getLogIn($request, $response)
    {
        $user = '';

        if (!Validator::isConnected())
        {
            $UserManagerPDO = new UserManagerPDO($this->db);
            return $this->render($response, 'LOGIN PAGE HERE');
        }
        else 
        {
            return $this->redirect($response, 'home', 200);
        }
    }
    
    public function postLogIn($request, $response)
    {
        $errors = [];
        $login = $request->getParam('login');
        $password = $request->getParam('password');

        if (Validator::loginCheck($login, $this->db))
        {
            if (Validator::isActive($login, $this->db))
            {
                if (Validator::passwordLogin($login, $password, $this->db))
                {
                    $UserManagerPDO = new UserManagerPDO($this->db);
                    $id = $UserManagerPDO->getIdFromLogin($login);
                    $user = $UserManagerPDO->getUnique($id);

                    $_SESSION['id'] = serialize($id);
                    setcookie("matcha_cookie", $_SESSION['id'], time() + 36000, "/");
                }
                else 
                {
                    $errors['passwod'] = 'Wrong password';
                }
            }
            else 
            {
                $errors['login'] = 'Account is not activated';
            } 
        }
        else 
        {
            $errors['login'] = 'This username doesnt exists';
        }

        if (!empty($errors))
        {
            $this->flash('Fields are not fullfilled correctly', 'error');
            $this->flash($errors, 'errors');
            return $this->redirect($response, 'auth.login', 302); /**AUTH.LOGIN - NAMED BY TWIG BEFORE */
        }

        return $this->redirect($response, 'home', 200);
    }

    public function getForgotPwd($request, $response)
    {
        $user ='';

        if (!Validator::isConnected())
        {
            $userManagerPDO = new UserManagerPDO($this->db);
            return $this->render($response, '/accountResetpass.twig');
        }
        else 
        {
            return $this->redirect($response, 'home', 200);
        }
    }

    public function postForgotPwd($request, $response)
    {
        if (!Validator::isConnected())
        {
            $errors = [];
            $email = $request->getParam('email');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $errors['email'] = 'Email is invalid';
            }
            else 
            {
                $UserManagerPDO = new UserManagerPDO($this->db);
                $user = $UserManagerPDO->getUserFromEmail($email);
				
                if (!empty($user))
                {
					$hash = $UserManagerPDO->newHash($user['email']);
                    confirmResetPwd($email, $hash);
                }
            }
        }

        if (!empty($errors))
        {
            return $this->render($response, '/accountResetpass.twig');
        }
        else 
        {
            return $this->redirect($response, '/account/register', 200);
        }
    }

    public function getNewPwd($request, $response)
    {
        $user = '';

        if (!Validator::isConnected() && $_GET['email'] && !empty($_GET['email']) && isset($_GET['hash']) && !empty($_GET['hash']))
        {
            $UserManagerPDO = new UserManagerPDO($this->db);

            $email = $_GET['email'];
            $_SESSION['email'] = $email;
            $hash = $_GET['hash'];
            $_SESSION['hash'] = $hash;
            $user = $userManagerPDO->getUserFromEmail($email);

            if ($user && $user->hash() === $_GET['hash'])
            {
                return $this->render($response, 'NEWPASS PAGE HERE');
            }
            else
            {
                $this->flash('Invalid link', 'error');
                return $this->redirect($response, 'auth.login', 302);
            }
        }
        else
        {
            return $this->redirect($response, 'home', 200);
        }
    }

    public function postNewPwd($request, $response)
    {
        if (!Validator::isConnected())
        {
            $UserManagerPDO = new UserManagerPDO($this->db);
            $email = $_SESSION['email'];
            $hash = $_SESSION['hash'];
            $user = $UserManagerPDO->getUserFromEmail($email);
            $newPassword = $request->getParam('newPassword');
            $newPasswordConfirm = $request->getParam('newPasswordConfirm');
            $errors = [];

            if ($newPassword != $newPasswordConfirm)
            {
                $errors['newPasswordConfirm'] = 'Passwords do not match';
            }

            switch (Validator::passwordCheck($newPassword))
            {
                case 1:
                    $errors['newPassword'] = 'Password too short';
                break;
                case 2:
                    $errors['newPassword'] = 'Must be at least 1 number';
                break;
                case 3:
                    $errors['newPassword'] = 'Must be at least 1 letter';
                break;
            }

            if (!empty($errors))
            {
                $this->flash('something isnt filled correctly', 'error');
                $this->flash($errors, 'errors');
                return $response->withRedirect('newpwd' . '?' . 'email=' . $email . '&hash=' . $hash);
            }

            else
            {
                $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
                $userManagerPDO->save($user);

                $this->flash('Your password has been changed', 'success');
                return $this->redirect($response, 'auth.login', 200);
            }
        }
    }
    public function getLogOut($request, $response)
    {
        unset($_SESSION['id']);
        setcookie("matcha_cookie", null, -1, "/");
        session_destroy();
        return $this->redirect($response, 'home', 200);
    }

}