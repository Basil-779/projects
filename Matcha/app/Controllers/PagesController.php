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
	
	public function getSettings($request, $response)
    {
		return $this->render($response, '/userSettings.twig', ['name' => 'Tomas']);
    }
	
	public function getProfile($request, $response)
    {
		$UserManagerPDO = new UserManagerPDO($this->db);
		$user = $UserManagerPDO->getUnique($UserManagerPDO->getIdFromLogin($_SESSION['login']));
		function debug($str) {
			echo '<pre>';
			var_dump($str);
			echo '</pre>';
			exit;
		}
		return $this->render($response, '/userProfile.twig', ['user' => $user, 'login' => $_SESSION['login']]);
    }
	
	public function confirmEmail($request, $response, $args)
    {
        $UserManagerPDO = new UserManagerPDO($this->db);
		return $UserManagerPDO->activateUser($args['hash']);
    }
	
	public function getChangePassword($request, $response, $args)
    {
        $_SESSION['hash'] = $args['hash'];
		return $this->render($response, '/accountNewpass.twig');
    }
	
	public function postChangePassword($request, $response, $args)
    {
		$UserManagerPDO = new UserManagerPDO($this->db);
        //if (Validator::passwordCheck($request->getParam('newPassword')))
        //{
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
        //}
		if (!empty($errors)) {
			return $errors['password'];
		}
		else 
		{
			$newpass = password_hash($request->getParam('newPassword'), PASSWORD_DEFAULT);
			$UserManagerPDO->setNewPassword($args['hash'], $newpass);
			return "Ok";
		}
    }
	
	public function getSignUp($request, $response)
    {
		return $this->render($response, '/accountRegister.twig');
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
        return $this->render($response, '/accountLogin.twig');
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
                    $_SESSION['id'] = serialize($id);
                    setcookie("matcha_cookie", $_SESSION['id'], time() + 36000, "/");
                }
                else 
                {
                    $errors['password'] = 'Wrong password';
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
		if (!empty($errors)) {
			return var_dump($errors);
		}
		else {
			$_SESSION['login'] = $login;
		}
    }

    public function getForgotPwd($request, $response)
    {
        $user ='';
        $userManagerPDO = new UserManagerPDO($this->db);
        return $this->render($response, '/accountResetpass.twig');
    }

    public function postForgotPwd($request, $response)
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

        if (empty($errors))
        {
			$this->render($response, '/accountResetpass.twig');
        }
        /*else 
        {
            return $this->render($response, '/accountRegister.twig', 200);
        }*/
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
	
	public function getSignUpInfos($request, $response)
    {
        if (Validator::isConnected())
        {
            return $this->render($response, '/userAdditionalSettings.twig');
        }
        else
        {
            return $this->redirect($response, 'auth.login', 200);
        }
    }
    public function postSignUpInfos($request, $response)
    {
		$errors = [];
        $tags = [];
		$userManagerPDO = new UserManagerPDO($this->db);
        if (!Validator::bioLengthCheck($request->getParam('biography')))
        {
            $errors['bio'] = 'You have to tell about yourself more';
        }
        if (!is_string($request->getParam('tags')) && empty($request->getParam('tags')))
        {
            $errors['tags'] = 'You must choose at least 1 tag';
        }
        else
        {
            $strTags = $request->getParam('tags');
            if (stristr($strTags, 'Algorithms'))
            {
                array_push($tags, 'algorythm');
            }
            if (stristr($strTags, 'Web') !== 0)
            {
				array_push($tags, 'web');
            }
            if (stristr($strTags, 'Graphics'))
            {
                array_push($tags, 'graphics');
            }
            if (stristr($strTags, 'Unix'))
            {
                array_push($tags, 'unix');
            }
            if (stristr($strTags, 'Sysadmin'))
            {
                array_push($tags, 'sysadmin');
            }
        }
        if (empty($request->getParam('gender')))
        {
            $errors['gender'] = 'You must choose a gender';
        }
        if (empty($_FILES['photo']))
        {
            $errors['photo'] = 'You must upload a photo';
        }
        if (empty($errors))
        {
			
            $user = new User([
                'bio' => $request->getParam('biography'),
                'gender' => $request->getParam('gender'),
                'sexuality' => $request->getParam('sexuality'),
                'tags' => $tags,
                'id' => $userManagerPDO->getUserFromLogin($_SESSION['login'])
            ]);
            $userManagerPDO->addTotal($user);
            $userManagerPDO->addExtras($user, $tags);
            $this->postUploadPicture($request, $response);
        }
        else
        {
			return var_dump($errors);
        }
    }
	
    public function getLogOut($request, $response)
    {
        session_start();
		unset($_SESSION['id']);
		unset($_SESSION['login']);
        setcookie("matcha_cookie", null, -1, "/");
		return $this->redirect($response, 'auth.login', 200);
    }
	
	public function postUploadPicture($request, $response) {
        define('MB', 1048576);
        $UserManagerPDO = new UserManagerPDO($this->db);
        $id = $UserManagerPDO->getUserFromLogin($_SESSION['login']);
        $errors = [];
        $target_dir = 'uploads/' . $id . '/';
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        $uploadOk = 1;
        $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
        // Check if image file is a actual image or fake image
        if(!empty($request->getParam('photo'))) {
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if($check !== false) {
                $errors['imageupload'] = "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            }
            else {
                $errors['imageupload'] = "File is not an image.";
                $uploadOk = 0;
            }
        }
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $errors['imageupload'] =  $target_dir;
                $uploadOk = 0;
            }
        }
        if (file_exists($target_file)) {
            $errors['imageupload'] =  "Sorry, file already exists.";
            $uploadOk = 0;
        }
        if ($_FILES["photo"]["size"] > 5 * MB) {
            $errors['imageupload'] =  "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $errors['imageupload'] =  "Only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        $count = $UserManagerPDO->countPictures($id);
        if ($count >= 5) {
            $errors['imageupload'] =  "Sorry, maximum 5 pictures allowed";
            $uploadOk = 0;
        }
        if ($uploadOk == 0) {
            var_dump($errors);
            //return $this->redirect($response, 'user.edit', 302);
        }
        else {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                /*File has been uploaded*/
                $UserManagerPDO->addPicture($target_file, $id);
            } else {
                $errors['imageupload'] =  "Sorry, there was an error uploading your file.";
                var_dump($errors);
            }
        }
    }

}