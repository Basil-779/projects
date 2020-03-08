<?php
namespace App\Controllers;
use App\Models\User;
use App\Models\UserManagerPDO;
use App\Models\Notification;
use App\Models\NotificationManager;
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
	/**************** TEST  ************************************/ 
	
	public function debug($value) {
		echo "<pre>";
		var_dump($value);
		echo "</pre>";
	}
	
	function hasLiked($id1, $id2)
    {
        $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
		$DB_REQ = $DB_REQ->prepare('
        SELECT * FROM likes WHERE id_belong = :id2 AND id_liked = :id1
        ');
        $DB_REQ->bindValue(':id2', $id1, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id1', $id2, PDO::PARAM_INT);
        $DB_REQ->execute();
        if ($DB_REQ->fetchColumn() > 0)
        {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
	
	function countNotif() {
		$notificationManager = new NotificationManager($this->db);
		return $notificationManager->countUnread($_SESSION['id']);
	}
	
	/*********************************** END TEST  *********************************/
	
	public function getLikelist($request, $response) {
		if (Validator::isConnected()) {
			if (Validator::isComplete($_SESSION['login'], $this->container->db)) {
				$users = [];
				$UserManagerPDO = new UserManagerPDO($this->db);
				$logged_id = $UserManagerPDO->getIdFromLogin($_SESSION['login']);
				$id_liked = $UserManagerPDO->listLikes($logged_id);
				foreach ($id_liked as $key => $value)
						{
							$tmp = $UserManagerPDO->getUnique($key);
							$tmp['likeback'] = $value;		
							$users[] =  $tmp;
						}
				$this->render($response, '/likelist.twig', ['users' => json_encode($users), 'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
			}
			else {
				return $this->redirect($response, 'addSettings', 200);
			}
		}
		else {
			return $this->redirect($response, 'auth.login', 200);
		}
	}
	
	public function PostLikelist($request, $response) {
		
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/
		
		$UserManagerPDO = new UserManagerPDO($this->db);
		$logged_id = $UserManagerPDO->getIdFromLogin($_SESSION['login']);
		switch ($request->getParam('action')) {
			case 'unlike':
				$UserManagerPDO->dislike((int)$request->getParam('id'), $logged_id);
				
				$notification = new Notification([
					'belong' => (int)$request->getParam('id'),
					'sender' => $_SESSION['id'],
					'unread' => TRUE,
					'type' => "unlike",
					]);
				$notificationManager = new NotificationManager($this->db);
				$notificationManager->add($notification);
				
				return "OK";
				break;
		}
	}
	
	public function getBlocklist($request, $response) {
		if (Validator::isConnected()) {
			if (Validator::isComplete($_SESSION['login'], $this->container->db)) {
				$users = [];
				$UserManagerPDO = new UserManagerPDO($this->db);
				$logged_id = $UserManagerPDO->getIdFromLogin($_SESSION['login']);
				$id_liked = $UserManagerPDO->listBlocks($logged_id);
				foreach ($id_liked as $value){
							$users[] = $UserManagerPDO->getUnique($value);	
				}
				$this->render($response, '/blocklist.twig', ['users' => json_encode($users), 'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
			}
			else {
				return $this->redirect($response, 'addSettings', 200);
			}
		}
		else {
			return $this->redirect($response, 'auth.login', 200);
		}
	}
	
	public function PostBlocklist($request, $response) {
		
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/
		
		$UserManagerPDO = new UserManagerPDO($this->db);
		$logged_id = $UserManagerPDO->getIdFromLogin($_SESSION['login']);
		if ($request->getParam('action') === 'unblock') {
			$UserManagerPDO->unblock($logged_id, (int)$request->getParam('id'));
			return "OK";
		}
	}
	
	public function main($request, $response)
    {
		if (Validator::isConnected()) {
			if (Validator::isComplete($_SESSION['login'], $this->container->db)) {
				$users = [];
				$UserManagerPDO = new UserManagerPDO($this->db);
				$sort = 1;
				$ageMin = 0;
				$ageMax = 100;
				$scoreMin = -1;
				$scoreMax = 100;
				$locationMax = 100000;
				$tagsInCommon = 1;
				$id = (int)$UserManagerPDO->getIdFromLogin($_SESSION['login']);
				$gender = $UserManagerPDO->getGenderFromLogin($_SESSION['login']);
				$sexuality = $UserManagerPDO->getSexualityFromLogin($_SESSION['login']);
				$matches = $UserManagerPDO->getMatches($id, $gender, $sexuality, $sort, $ageMin, $ageMax, $scoreMin, $scoreMax, $locationMax, $tagsInCommon);
				foreach ($matches as $key => $value)
				{
					$tmp = $UserManagerPDO->getUnique($key);
					$tmp['distance'] = $value;		
					$users[] =  $tmp;
				}
				$this->render($response, '/main.twig', ['users' => json_encode($users), 'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
			}
			else {
				return $this->redirect($response, 'addSettings', 200);
			}
		}
		else {
			return $this->redirect($response, 'auth.login', 200);
		}
    }
	
	public function postMain($request, $response)
    {
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/
			
		$UserManagerPDO = new UserManagerPDO($this->db);
		$id = (int)$UserManagerPDO->getIdFromLogin($_SESSION['login']);
		$gender = $UserManagerPDO->getGenderFromLogin($_SESSION['login']);
		$sexuality = $UserManagerPDO->getSexualityFromLogin($_SESSION['login']);
		$action = $request->getParam('action');
		switch ($action) {
			case "filter":
				$users = [];
				$sort = (int)$request->getParam('sort');
				$ageMin = (int)$request->getParam('a_min');
				$ageMax = (int)$request->getParam('a_max');
				$scoreMin = (int)$request->getParam('r_min');
				$scoreMax = (int)$request->getParam('r_max');
				$locationMax = (int)$request->getParam('d_max');
				$tagsInCommon = 1;
				$matches = $UserManagerPDO->getMatches($id, $gender, $sexuality, $sort, $ageMin, $ageMax, $scoreMin, $scoreMax, $locationMax, $tagsInCommon);
				foreach ($matches as $key => $value)
				{
					$tmp = $UserManagerPDO->getUnique($key);
					$tmp['distance'] = $value;		
					$users[] =  $tmp;
				}
				echo json_encode($users);
				break;
			case "like":
				$UserManagerPDO->like($UserManagerPDO->getIdFromLogin($_SESSION['login']), (int)$request->getParam('id'));
				$notification = new Notification([
					'belong' => (int)$request->getParam('id'),
					'sender' => $UserManagerPDO->getIdFromLogin($_SESSION['login']),
					'unread' => TRUE,
					'type' => hasLiked((int)$request->getParam('id'), $UserManagerPDO->getIdFromLogin($_SESSION['login'])) ? "likeback" : "like",
					]);
				$notificationManager = new NotificationManager($this->db);
				$notificationManager->add($notification);
				return "OK";
				break;
			case "block":
				$UserManagerPDO->block($UserManagerPDO->getIdFromLogin($_SESSION['login']), (int)$request->getParam('id'));
				return "OK";
				break;
			case "report":
				reportMail($_SESSION['id'], (int)$request->getParam('id'));
				$notification = new Notification([
					'belong' => (int)$request->getParam('id'),
					'sender' => $UserManagerPDO->getIdFromLogin($_SESSION['login']),
					'unread' => TRUE,
					'type' => "report",
					]);
				$notificationManager = new NotificationManager($this->db);
				$notificationManager->add($notification);
			/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
			/* ОБРАБОТЧИК РЕПОРТА, ПРИЧИНА ВСЕГДА ОДНА - ПОДОЗРЕНИЕ НА ФЕЙК АККАУНТ  */
			/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
			/** ЗАДАНИЕ: WHEN A USER CONSULTS A PROFILE, IT MUST APPEAR IN HIS/HER VISIT HISTORY **/
			/** СДЕЛАТЬ VISIT HISTORY ДЛЯ КАЖДОГО ПОЛЬЗОВАТЕЛЯ. КОГДА ЕГО ЛАЙКАЮТ, БЛОЧАТ ИЛИ РЕПОРТЯТ - ДЕЛАТЬ ЗАПИСЬ, КТО "ПОСМОТРЕЛ" ЕГО ПРОФИЛЬ, ТАК КАК У НАС НЕТ ИМЕННО "ПРОСМОТРА ПРОФИЛЯ" **/
			/** БУДЕТ СТРАНИЦА VISIT HISTORY, НА КОТОРУЮ БУДЕТ ВЫДАВАТЬСЯ ИНФА: КТО (ЛОГИН), КОГДА (ВРЕМЯ) И ЧТО СДЕЛАЛ (ЛАЙКНУЛ, БЛОКНУЛ, РЕПОРТНУЛ) С ПРОФИЛЕМ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ **/
				return "OK";
				break;
		}
    }
	
	public function getChatlist($request, $response)
    {
		$errors= [];
		$UserManagerPDO = new UserManagerPDO($this->db);
		if (Validator::isConnected())
		{
			if (Validator::isComplete($_SESSION['login'], $this->container->db))
			{
				$chatsIDs = [];
				$tmp = $UserManagerPDO->listLikes($_SESSION['id']);
				foreach($tmp as $key => $value)
				{
					if ($value == 1) {
						$chatsIDs[] = $key;
					}
				}
			}
			else {
				$errors['complete'] = "Your profile is not completed.";
			}
		}
		else {
			$errors['auth'] = "You must log in first.";
		}

		if (empty($errors))
		{
			$user = $UserManagerPDO->getUnique($_SESSION['id']);
			$chatUsers = [];
			foreach ($chatsIDs as $idofchat)
			{
				$chatUsers[] = $UserManagerPDO->getUnique($idofchat);
			}
			/**$chatUsers */
			$this->render($response, '/chatlist.twig', ['user' => json_encode($user), 'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
		}
		/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
		/* ПРОВЕРКА НА ЗАЛОГИНЕНОСТЬ, ПРОВЕРКА НА ЗАПОЛНЕННОСТЬ ДОП. ИНФЫ
		/* ВЫДАЧА ИНФЫ ОБО ВСЕХ ЧАТАХ ДЛЯ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ:
		/* ID (СЛУЖИТ ССЫЛКОЙ НА ЧАТ), ФОТО, ЛОГИН, ПОСЛЕДНЕЕ СООБЩЕНИЕ, ДАТА ПОСЛЕДНЕГО СООБЩЕНИЯ, ФОТО ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ */
		/* ЕСЛИ БУДЕМ ТАК ДЕЛАТЬ, ТО ИНФА, ЧТО КОНКРЕТНЫЫЙ ЧАТ ЗАБЛОЧЕН, ПОТОМУ ЧТО СОБЕСЕДНИК АНЛАЙКНУЛ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ */
		/* ЕСЛИ БУДЕТ ПАРАМЕТР НЕПРОЧИТАННЫХ СООБЩЕНИЙ, ТО КОЛ-ВО НЕПРОЧИТАННЫХ СООБЩЕНИЙ */
		/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    }
	
	public function postChatlist($request, $response)
    {
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/	
    }
	
	public function getChat($request, $response, $args)
    {
		$errors = [];
		if (Validator::isConnected())
		{
			if (Validator::isComplete($_SESSION['login'], $this->container->db))
			{
				if (Validator::isUser($args['chat_id'], $this->container->db))
				{
					if (hasLiked((int)$_SESSION['id'], (int)$args['chat_id']) && hasLiked((int)$args['chat_id'], (int)$_SESSION['id']))
					{
						$UserManagerPDO = new UserManagerPDO($this->db);
						$messages = $UserManagerPDO->getChatMsg($_SESSION['id'], $args['chat_id']);
						$this->render($response, '/chat.twig', ['messages' => json_encode($messages), 'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
					}
					else {
						$errors['unliked'] = "Sorry, this user unliked you.";
					}
				}
				else {
					$errors['dialogue'] = "No such user.";
				}
			}
			else {
				$errors['complete'] = "You must fullfill your account before chatting";
			}
		}
		else {
			$errors['login'] = "You must log in first";
		}
		if (!empty($errors))
		{
			return var_dump($errors);
		}
		/** ПРОВЕРКА НА ЗАЛОГИНЕНОСТЬ, ПРОВЕРКА НА ЗАПОЛНЕННОСТЬ ДОП. ИНФЫ
		/** ПРОВЕРКА, ЧТО ТАКОЙ ПОЛЬЗОВАТЕЛЬ ($args['chat_id']) ВООБЩЕ СУЩЕСТВУЕТ  **/
		/** ВЫДАЧА ИНФЫ: ФОТО И ЛОГИНЫ ОБОИХ БЕСЕДУЮЩИХ                            **/
		/** ВСЕ СООБЩЕНИЯ В БЕСЕДЕ, ИХ ДАТЫ И ТОЧНЫЙ ОПРЕДЕЛИТЕЛЬ, КТО ОТПРАВИЛ КОНКРЕТНОЕ СООБЩЕНИЕ **/
		/* ЕСЛИ БУДЕМ ТАК ДЕЛАТЬ, ТО ИНФА, ЧТО КОНКРЕТНЫЫЙ ЧАТ ЗАБЛОЧЕН, ПОТОМУ ЧТО СОБЕСЕДНИК АНЛАЙКНУЛ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ */
    }
	
	public function postChat($request, $response, $args)
    {
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/	
		if ($args['chat_id']) {
			$UserManagerPDO = new UserManagerPDO($this->db);
			/***                              **/
			/*** ПРОВЕРКА НА ПУСТОЕ СООБЩЕНИЕ **/
			
			if (!empty($request->getParam('message')))
			{
				$UserManagerPDO->saveMsg($_SESSION['id'], $args['chat_id'], $request->getParam('message'));
				
				$notification = new Notification([
						'belong' => (int)$args['chat_id'],
						'sender' => $_SESSION['id'],
						'unread' => TRUE,
						'type' => "message",
						]);
				$notificationManager = new NotificationManager($this->db);
				$notificationManager->add($notification);
				
				return "OK";
			}
			else {
				return "Type a message";
			}
		}
    }
	
	public function getNotificationlist($request, $response)
    {
		$errors = [];
		/***                                                               **/
		/** ПРОВЕРКА НА ЗАЛОГИНЕНОСТЬ, ПРОВЕРКА НА ЗАПОЛНЕННОСТЬ ДОП. ИНФЫ **/
		/***                                                               **/
		if (Validator::isConnected())
		{
			if (Validator::isComplete($_SESSION['login'], $this->container->db))
			{
				$notificationManager = new NotificationManager($this->db);
				$notifs = $notificationManager->get($_SESSION['id']);
				$this->render($response, '/notificationlist.twig', ['notifs' => json_encode($notifs), 'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
			}
			else {
				$errors['complete'] = "You must fullfill your account first";
			}
		}
		else {
			$errors['login'] = "You must log in first";
		}
		if (!empty($errors))
		{
			return var_dump($errors);
		}
	}
	
	
	public function postNotificationlist($request, $response)
    {
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/
		
		if ($request->getParam('readall')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setAllNotifsAsRead($_SESSION['id']);
			return "OK";
		}
    }
	
	public function getSettings($request, $response)
    {
		if (Validator::isConnected())
        {
            if (Validator::isComplete($_SESSION['login'], $this->container->db)) {
				$userManagerPDO = new UserManagerPDO($this->db);
				$settings = $userManagerPDO->getUnique($userManagerPDO->getIdFromLogin($_SESSION['login']));
				return $this->render($response, '/userSettings.twig', ['settings' => json_encode($settings),'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
			}
			else {
				return $this->redirect($response, 'addSettings', 200);
			}
        }
        else
        {
            return $this->redirect($response, 'auth.login', 200);
        }
    }
	
	public function postSettings($request, $response)
    {
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/
		
        $errors = [];
		$userManagerPDO = new UserManagerPDO($this->db);
        if ($request->getParam('firstName')) {
            if (Validator::nameCheck($request->getParam('firstName'))) {
				if ($userManagerPDO->changeFirstName($userManagerPDO->getIdFromLogin($_SESSION['login']), $request->getParam('firstName'))){
					return 'OK';
				}
			}
			else {
				$errors['firstName'] = 'The first name must contain only chars';
			}
		}
        if ($request->getParam('lastName'))
        {
            if (Validator::nameCheck($request->getParam('lastName'))) {
				if ($userManagerPDO->changeLastName($userManagerPDO->getIdFromLogin($_SESSION['login']), $request->getParam('lastName')))
				{
					return 'OK';
				}
			}
			else {
				$errors['lastName'] = 'The last name must contain only chars';
			}
        }	
        if ($request->getParam('email')){
            if (Validator::mailCheck($request->getParam('email'), $this->container->db) === INVALID_EMAIL){
				$errors['email'] = 'E-mail is invalid';
			}
			else if (Validator::mailCheck($request->getParam('email'), $this->container->db) === EMAIL_ALREADY_EXISTS)
			{
				$errors['email'] = 'E-mail already used';
			}
			else if ($userManagerPDO->changeMail($userManagerPDO->getIdFromLogin($_SESSION['login']), $request->getParam('email'))){
				return 'OK';
			}
		}
        if ($request->getParam('password'))
        {
            $pass = password_hash($request->getParam('password'), PASSWORD_DEFAULT);	
			switch (Validator::passwordCheck($request->getParam('password')))
			{
				case 1:
					$errors['pswd'] = 'Password too short';
					break;
				case 2:
					$errors['pswd'] = 'Password must contain at least 1 number';
					break;
				case 3:
					$errors['pswd'] = 'Password must contain at least 1 letter';
					break;
			}
			if (empty($errors['pswd'])) {
				if ($userManagerPDO->changePass($userManagerPDO->getIdFromLogin($_SESSION['login']), $pass)){
					return 'OK';
				}
			}
        }
		return json_encode($errors);
    }
	
	public function getProfile($request, $response)
    {
		$UserManagerPDO = new UserManagerPDO($this->db);
		$user = $UserManagerPDO->getUnique($UserManagerPDO->getIdFromLogin($_SESSION['login']));
		$this->render($response, '/userProfile.twig', ['user' => json_encode($user), 'login' => $_SESSION['login']]);
    }
	
	public function confirmEmail($request, $response, $args)
    {
        $UserManagerPDO = new UserManagerPDO($this->db);
		$this->render($response, '/accountConfirm.twig', ['message' => $UserManagerPDO->activateUser($args['hash'])]);
    }
	
	public function getChangePassword($request, $response, $args)
    {
        $_SESSION['hash'] = $args['hash'];
		return $this->render($response, '/accountNewpass.twig');
    }
	
	public function postChangePassword($request, $response, $args)
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
            $errors['login'] = 'Your username must contain between 3 and 32 chars.';
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

        else if (Validator::mailCheck($request->getParam('email'), $this->container->db) === EMAIL_ALREADY_EXISTS)
        {
            $errors['email'] = 'E-mail already used';
        }

        if (Validator::passwordCheck($request->getParam('password')))
        {
            switch (Validator::passwordCheck($request->getParam('password')))
            {
                case 1:
                    $errors['pswd'] = 'Password too short';
                    break;
                case 2:
                    $errors['pswd'] = 'Password must contain at least 1 number';
                    break;
                case 3:
                    $errors['pswd'] = 'Password must contain at least 1 letter';
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
			return json_encode($errors);
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
					$_SESSION['id'] = $id;
					$UserManagerPDO->updateLastSeen($id);
                    setcookie("matcha_cookie", $_SESSION['id'], time() + 36000, "/");
                }
                else 
                {
                    $errors['pswd'] = 'Wrong password';
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
			return json_encode($errors);
		}
		else {
			$_SESSION['login'] = $login;
			return "OK";
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
            $userManagerPDO = new UserManagerPDO($this->db);
			$settings = $userManagerPDO->getUnique($userManagerPDO->getIdFromLogin($_SESSION['login']));
			return $this->render($response, '/userAdditionalSettings.twig', ['settings' => json_encode($settings),'login' => $_SESSION['login'], 'count' => $this->countNotif($id)]);
        }
        else
        {
            return $this->redirect($response, 'auth.login', 200);
        }
    }
    public function postSignUpInfos($request, $response)
    {
		/***** <notification listener> *****/
		if ($request->getParam('listen')) {
			$notificationManager = new NotificationManager($this->db);
			return json_encode($notificationManager->getNotifs($_SESSION['id']));
		}
		if ($request->getParam('read')) {
			$notificationManager = new NotificationManager($this->db);
			$notificationManager->setNotifAsRead($_SESSION['id'], $request->getParam('read'));
			return "OK";
		}
		/***** </notification listener> *****/
		
		$errors = [];
        $tags = [];
		$userManagerPDO = new UserManagerPDO($this->db);
        if (!Validator::bioLengthCheck($request->getParam('biography')))
        {
            $errors['bio'] = 'You have to tell about yourself more';
        }
		if (empty($request->getParam('tags')))
        {
            $errors['tags'] = 'You must choose at least 1 tag';
        }
        else
        {
            $strTags = json_decode($request->getParam('tags'), true);
            if ($strTags['algorythm'] == "1")
            {
                array_push($tags, 'algorythm');
            }
            if ($strTags['web'] == "1")
            {
                array_push($tags, 'web');
            }
            if ($strTags['graphics'] == "1")
            {
                array_push($tags, 'graphics');
            }
            if ($strTags['unix'] == "1")
            {
                array_push($tags, 'unix');
            }
            if ($strTags['sysadmin'] == "1")
            {
                array_push($tags, 'sysadmin');
            }
        }
        if (empty($request->getParam('gender')))
        {
            $errors['gender'] = 'You must choose a gender';
        }
        if (empty($_FILES['photo']) && $userManagerPDO->countPictures($userManagerPDO->getIdFromLogin($_SESSION['login'])) == 0)
        {
            $errors['photo'] = 'You must upload a photo';
        }
        if (empty($errors))
        {
            $user = new User([
                'bio' => $request->getParam('biography'),
                'gender' => $request->getParam('gender'),
                'sexuality' => lcfirst($request->getParam('sexuality')),
                'tags' => $tags,
                'id' => $userManagerPDO->getUserFromLogin($_SESSION['login'])
            ]);
            $userManagerPDO->addTotal($user);
            $userManagerPDO->addExtras($user, $tags);
			$coords = explode(',', $request->getParam('coords'));
			$latitude = (float)$coords[0];
			$longitude = (float)$coords[1];
			$userManagerPDO->setCoords($longitude, $latitude, $userManagerPDO->getIdFromLogin($_SESSION['login']));
            if (($userManagerPDO->countPictures($userManagerPDO->getIdFromLogin($_SESSION['login'])) < 5) && !empty($_FILES['photo']))
            {
                $this->postUploadPicture($request, $response);
            }
			return "OK";
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