<?php

namespace App\Database;

use PDO;

class DatabaseConnectionFactory
{
    public static function Create(string $dsn, string $username, string $password): PDO {
        $pdo = new PDO($dsn, $username, $password);

        // make PDO queries return the correct types https://stackoverflow.com/a/20123337/5717872
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // make PDO queries throw exception when fetching instead of returning false
        // https://stackoverflow.com/a/46352628/5717872
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}