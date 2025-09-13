<?php
$conf = parse_ini_file("Config.ini");


// Preencha com os dados do seu banco (ByetHost)
define('DB_HOST', $conf["server"]);
define('DB_NAME', $conf["database"]);
define('DB_USER', $conf["user"]);
define('DB_PASS', $conf["password"]); // substitua pela senha







function db() {
    static $pdo;
    if (!$pdo) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    return $pdo;
}



