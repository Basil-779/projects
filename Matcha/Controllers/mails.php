<?php
use Models\User;

function mailResetPwd($email, $hash)
{
    $host = "matcha.aretino.ru/matcha/public/settings/changeMail";
    $subject = "Your new Matcha E-mail";
    $message = "Hi, \n
    It seems, that you have changed your e-mail address. Please confirm it by clicking on the following link:\n
    http://".$host."?email=".$email."&hash=".$hash;
    mail($email, $subject, $message);
}

function confirmResetPwd($email, $hash)
{
    $host = "matcha.aretino.ru/matcha/public/auth/newpwd";
    $subject = "Reset password confirmation";
    $message = "Hi, \n
    Please, confirm the resetting of your password by clicking on the following link:\n
    http://".$host."?email=".$email."&hash=".$hash;
    mail($email, $subject, $message);
}

function reportMail(User $user, User $reportedProfile, $reportReason)
{
    $subject = "Matcha - " . $user->login() . " has reported " . $reportedProfile->login();
    $email = "admin@email.com";
    $message = "Hi, \n
    The User " . $user->login() . " (ID: " . $user->id() .") has reported the user" . $reportedProfile->login() ."(ID: " . $reportedProfile->id() .") for the following reason:\n". $reportReason . "";
    mail($email, $subject, $message);
}

function contactMail(User $user, $contact)
{
    $subject = "Matcha - " . $user->login() . "has sent you a message";
    $email = "admin@email.com";
    $message = "Hi, \n
    The user " . $user->login() . " (ID: " . $user->id() .") has sent you a message:\n". $contact ."";
    mail($email, $subject, $message);
}

?>

