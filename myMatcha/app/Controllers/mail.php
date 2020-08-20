<?php
	use App\Models\User;

	function mailResetPwd($email, $hash) {
		$host = "localhost:8080/matcha/public/settings/changeMail";
		$subject = "Matcha - You have changed your e-mail!";
		$message = "
		Hello,\n
		You have changed your e-mail, please confirm this adress by clicking on the following link:\n
		http://".$host."?email=".$email."&hash=".$hash;
		mail($email, $subject, $message);
	}
		
	function confirmResetPwd($email, $hash) {
		$host = "https://matcha.aretino.ru/account/newpass/";
		$subject = "Matcha - You have requested a reset of your password";
		$message = "
		Hello,\n
		You asked to reset your password, please confirm this adress by clicking on the following link:\n
		".$host.$hash;
		mail($email, $subject, $message);
	}
	
	function reportMail($userID, $reportedProfileID) {
        $subject = "Matcha - " . $userID . " has reported " . $reportedProfileID;
        $email = "root.ia.pokrov@gmail.com";
        $message = "
        Hello\n
        The user (ID: " . $userID .") has reported the user (ID: " . $reportedProfileID .") for making a fake account\n
        ";
        mail($email, $subject, $message);
    }
	
	function contactMail(User $user, $contact) {
		$subject = "Matcha - " . $user->login() . " has sent you a message";
		$email = "admin@admin.com";
		$message = "
		Hello\n
		The user " . $user->login() . " (ID: " . $user->id() .") has sent you a message:\n
		". $contact . "
		";
		mail($email, $subject, $message);
	}
	
	function confirmSignUp($email, $hash) {
		$host = "https://matcha.aretino.ru/account/confirm/";
		$subject = "Matcha - activating your account";
		$message = "
		Hello,\n
		Activate your account by clicking on the following link:\n
		".$host.$hash;
		mail($email, $subject, $message);
	}