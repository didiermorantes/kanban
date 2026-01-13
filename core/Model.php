<?php
// core/Model.php
require_once __DIR__ . '/../config/db.php';

abstract class Model
{
    protected static function db(): \PDO
    {
        return Database::getConnection();
    }
}
