<?php
namespace Controllers;
use \Models\User;
use \Models\UserManagePDO;
use \PDO;
use Controllers\Validator;
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
    const LOGIN_DOESNT_EXISTS = 0;

    public function home($request, $response)
    {
        $user = NULL;
        $data = '';
        $notifs = '';
        $nbUnread = '';
        $minPopularity = '';
        $minCommonTags = '';
        $sortby = '';
        $ageMin = '';
        $ageMax = '';
        $distance = '';

        if (Validator::isConnected())
        {
            $userManagerPDO = new UserManagerPDO($this->db);
            $user = $UserManagerPDO->getUnique(unserialize($_SESSION['id']));

            if (empty($user))
            {
                session_destroy();
            }

            if ($user->mainpicture())
            {
                if(!(isset($_GET['distance'])) || empty($_GET['distance']) || $_GET['distance'] < 0)
                {
                    $distance = 10000;
                }
                else 
                {
                    $distance = $_GET['distance'];
                }

                if(!(isset($_GET['ageMax'])) || empty($_GET['ageMax']) || $_GET['ageMax'] < 0)
                {
                    $ageMax = 100000;
                }
                else
                {
                    $ageMax = $_GET['ageMax'];
                }

                if (isset($_GET['min']) && $_GET['minPopularity'] >= 0)
                {
                    $minPopularity = $_GET['minPopularity'];
                }

                if (isset($_GET['minCommonTags']) && $_GET['minCommonTags'] >= 0)
                {
                    $minCommonTags = $_GET['minCommonTags'];
                }

                $data = $UserManagerPDO->getMatches($user, $distance);

                $i = 0;

                foreach ($data as $key => $value)
                {
                    $user_to_compare = $UserManagerPDO->getUnique($value['to_user_id']);
                    $data[$key]['to_user_age'] = Validator::getAge($data[$key]['to_user_age']);
                    if (($data[$key]['to_user_age'] >= $ageMin && $data[$key]['to_user_age'] <= $ageMax) && $data[$key]['popularity'] >= $minPopularity)
                    {
                        $tagsInCommon = $UserManagerPDO->countSimilarTags($user, $user_to_compare);
                        $data[$key]['tagsInCommon'] = $tagsInCommon;
                        $data[$key]['tags'] = $user_to_compare->tags();
                        if ($tagsInCommon < $minCommonTags)
                        {
                            unset($data[$key]);
                        }
                        $i++;
                    }

                    else
                    {
                        unset($data[$key]);
                    }
                }

                if (!empty($_GET['sortBy']) && isset($_GET['sortBy']))
                {
                    switch ($_GET['sortBy'])
                    {
                        case 'age':
                            $data = array_orderby($data, 'to_user_age', SORT_ASC, 'distance_in_km', SORT_ASC);
                        break;
                        case 'popularity':
                            $data = array_orderby($data, 'popularity', SORT_DESC, 'distance_in_km', SORT_ASC);
                        break;
                        case 'tagsInCommon':
                            $data = array_orderby($data, 'tagsInCommon', SORT_DESC, 'distance_in_km', SORT_ASC);
                        default:
                            break;
                    }
                }

                $notificationManager = new NotificationManager($this->db);
                $notifs = $notificationManager->get($user);
                $i = 0;
                foreach ($notifs as $notif)
                {
                    if ($notif->unread() == 1)
                    {
                        $i++;
                    }
                }
                $nbUnread = $i;
            }

            else 
            {
                $this->flash('You do not have a profile picture! Please, set one before getting your matches!', 'warning');
            }

            if ($user->isComplete())
            {
                $this->render($response, 'home.twig', [
                    'user' => $user,
                    'data' => $data,
                    'sortBy' => $sortBy,
                    'ageMin' => $ageMin,
                    'ageMax' => $ageMax,
                    'distance' => $distance,
                    'minPopularity' => $minPopularity,
                    'minCommonTags' => $minCommonTags,
                    'notifs' => $notifs,
                    'nbUnread' => $nbUnread
                    ]);
            }
            else 
            {
                return $this->redirect($response, 'auth.signupinfos', 200);
            }
        }
        else
        {
            return $this->redirect($response, 'auth.login', 200);
        }
    }

    public function getSignUp($request, $response)
    {
        if (!Validator::isConnected())
        {
        return $this->render($response, 'SIGN UP PAGE HERE');
        }
        else
        {
            return $this->redirect($response, 'home', 200);
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
            return $this->redirect($response, 'HOME PAGE HERE', 302);
        }

        $this->flash('A confirmation email was sent to you', 'info');
        return $this->redirect($response, 'LOGIN PAGE HERE', 200);
    }

    public function getSignUpInfos($request, $response)
    {
        if (Validator::isConnected())
        {
            $id = unserialize($_SESSION['id']);
            $UserManagerPDO = new UserManagerPDO($this->db);
            $user = $UserManagerPDO->getUnique($id);
            if (!($user->isComplete()))
            {
                return $this->render($response, 'SIGN UP INFO PAGE HERE', ['user' => $user]);
            }
            else
            {
                $this->flash('You can not access this page.', 'error');
                return $response->withRedirect($this->router->pathFor('user.profile', ['userprofile' => $user->login()]));
            }
        }

        else
        {
            $this->flash('You must be logged to access this page', 'error');
            return $this->redirect($response, 'auth.signup', 302);
        }
    }

    public function postSignUpInfos($request, $response)
    {
        $errors = [];

        if (!Validator::bioLengthCheck($request->getParam('bio')))
        {
            $errors['bio'] = 'Your bio must contain at least 20 chars.';
        }

        if (!Validator::radioCheck($request->getParam('gender')))
        {
            $errors['gender'] = 'You must pick a gender';
        }

        if (!Validator::tagsCheck($request->getParam('tags')))
        {
            $errors['tags'] = 'You must select at least 1 tag';
        }

        if (empty($errors))
        {
            $id = unserialize($_SESSION['id']);
            $tags = $request->getParam('tags');
            $UserManagerPDO = new UserManagerPDO($this->db);
            $user = $UserManagerPDO->getUnique((int) $id);

            $user->setBio($request->getParam('bio'));

            if (!Validator::radioCheck($request->getParam('sexuality')))
            {
                $user->setSexuality('bisexual');
            }
            else
            {
                $user->setSexuality($request->getParam('sexuality'));
            }

            $user->setGender($request->getParam('gender'));
            $user->setTags($tags);

            if ($request->getParam('latitude') && $requset->getParam('longitude'))
            {
                $latitude = floatval($request->getParam('latitude'));
                $longitude = floatval($request->getParam('longitude'));

                $user->setCoordinates($latitude, $longitude);
                $user->setMap($request->getParam('map'));
            }

            $UserManagerPDO->save($user);
            $UserManagerPDO->addExtras($user, $tags);
        }

        else 
        {
            $this->flash('Something was not filled correctly', 'error');
            $this->flash($errors, 'errors');
            return $this->redirect($response, 'auth.signupinfos', 302);
        }

        return $this->redirect($response, 'home', 200);
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
            return $this->render($response, 'FORGOT PASSWD PAGE HERE');
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
                    $user->setHash(md5(uniqid(rand(), true)));
                    $UserManagerPDO->save($user);
                    confirmResetPwd($email, $user->hash());
                }
            }
        }

        if (!empty($errors))
        {
            $this->flash('Something not filled correctly', 'error');
            return $this->redirect($response, 'forgotpwd', 302);
        }
        else 
        {
            $this->flash('Mail to reset passwd has been sent', 'info');
            return $this->redirect($response, 'auth.login', 200);
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

?>