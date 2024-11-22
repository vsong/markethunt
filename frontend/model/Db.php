<?php
require_once("$_SERVER[DOCUMENT_ROOT]/../config/config.php");

class Db
{
    private static $connection_singleton;
    private $connection;

    private function __construct()
    {
        global $DB_USER;
        global $DB_PASSWORD;
        global $DB_HOST;
        global $DB_DATABASE;
        $this->connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_DATABASE", $DB_USER, $DB_PASSWORD);
        // make mysqlnd return proper types
        $this->connection->setAttribute( PDO::ATTR_EMULATE_PREPARES, false);
        unset($DB_USER);
        unset($DB_PASSWORD);
        unset($DB_HOST);
        unset($DB_DATABASE);
    }

    public static function getConnection()
    {
        if (!isset(self::$connection_singleton)) {
            self::$connection_singleton = new self();
        }

        return self::$connection_singleton->connection;
    }
}