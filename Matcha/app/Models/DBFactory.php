<?php 
namespace App\Models;
use \PDO;

class DBFactory
{
    public static function getMysqlConnectionWithPDO()
    {
        try 
        {
            $DB_PDO = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
            $DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            die ('SQL error : ' . $e->getMessage());
        }

        return $DB_PDO;
    }

    public static function getMysqlConnectionWithMySQLi()
    {
        return new MySQLi('localhost', 'rootpokrov', 'Gx3U.7dq9)_5', 'rootpokrov_matcha');
    }
}