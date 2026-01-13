<?php
// config/db.php

class Database
{
    private static ?\PDO $connection = null;

    public static function getConnection(): \PDO
    {
        if (self::$connection === null) {
            $host = 'localhost';
            $db   = 'kanban_db';
            $user = 'root';      // cámbialo según tu entorno
            $pass = '';          // cámbialo según tu entorno
            $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // ✅ FIJAR TIMEZONE MYSQL PARA ESTA CONEXIÓN
            self::$connection->exec("SET time_zone = '-05:00'");

        }

        return self::$connection;
    }
}
