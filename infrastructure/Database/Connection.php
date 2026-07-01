<?php

namespace Infrastructure\Database;

use PDO;
use PDOException;

function getConnection(): PDO {
    $host = $_ENV['DB_HOST'];
    $db = $_ENV['DB_DB'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
    $dsn = "pgsql:host=$host;dbname=$db";

    try {
        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        exit( "Connection failed: " . $e->getMessage());
    }
}