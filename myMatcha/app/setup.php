<?php
	$DB_PDO = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents('bd.sql');
    $query = $DB_PDO->exec($sql);
    echo ("OK");