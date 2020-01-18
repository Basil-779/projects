<?php
try {
    $DB_PDO = new PDO('mysql:host=localhost;charset=utf8', 'root', 'root');
    $DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $DB_REQ = $DB_PDO->prepare('CREATE DATABASE IF NOT EXISTS matcha DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;');
    $DB_REQ->execute();
    $DB_REQ->closeCursor ();
}
catch (PDOException $e) {
    die("Database creation failed : " . $e->getMessage());
}

try {
    $DB_REQ = $DB_PDO->query("USE matcha;");
    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS users (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            login varchar(32) NOT NULL,
            firstName varchar(32) NOT NULL,
            lastName varchar(32) NOT NULL,
            age int(2) Not NULL,
            password char(128) NOT NULL,
            gender char(1) DEFAULT NULL,
            email varchar(255) NOT NULL,
            hash varchar(128) DEFAULT NULL,
            bio text,
            sexuality char(12) DEFAULT 'bisexual',
            rating int NOT NULL DEFAULT '0',
            map tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If GEO is allowed')
        ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE users AUTO_INCREMENT=30;");
    $DB_REQ->execute();


    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS tags (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            algorythm tinyint(1) UNSIGNED DEFAULT NULL COMMENT 'Love difficault algs',
            graphics tinyint(1) UNSIGNED DEFAULT NULL COMMENT 'Fancy for drawing',
            unix tinyint(1) UNSIGNED DEFAULT NULL COMMENT 'I WILL MAKE MY OWN LINUX',
            sysadmin tinyint(1) UNSIGNED DEFAULT NULL COMMENT 'Will u marry me, Docker?',
            web tinyint(1) UNSIGNED DEFAULT NULL COMMENT 'Im in love with PHP')
            ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE tags ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();


    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS pictures (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            source varchar(255) DEFAULT NULL,
            mainpic tinyint(1) UNSIGNED DEFAULT '0' COMMENT 'Main pic value')
        ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE pictures ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();


    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS visitors (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            id_visitor int(11) NOT NULL)
        ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE visitors ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();


    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS scores (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            score int(11) NOT NULL DEFAULT 0)
        ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE scores ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();


    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS likes (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            id_liked int(11) NOT NULL)
        ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE likes ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();
    

    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS blocks (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            id_blocked int(11) NOT NULL)
    ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE blocks ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();


    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS notifications (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            id_owner int(11) NOT NULL,
            id_sender int(11) NOT NULL,
            unread tinyint(1) NOT NULL,
            type varchar (255) NOT NULL,
            id_reference int(11) NOT NULL)
    ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE notifications ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE notifications ADD FOREIGN KEY (id_sender) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();


    $DB_REQ = $DB_PDO->prepare("CREATE TABLE IF NOT EXISTS chats (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_belong int(11) NOT NULL,
            id_sent int(11) NOT NULL,
            message text)
    ");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE chats ADD FOREIGN KEY (id_belong) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();
    $DB_REQ = $DB_PDO->prepare("ALTER TABLE chats ADD FOREIGN KEY (id_sent) REFERENCES users(id) ON DELETE CASCADE;");
    $DB_REQ->execute();

    echo "database created WOOHOO";
    $DB_REQ->closecursor();
}
catch (Exception $e) {
    die('LOL: ' . $e->getMessage());
}

?>