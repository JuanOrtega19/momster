<?php
$dsn = "mysql:host=localhost;dbname=momster;charset=utf8mb4";


$host = "localhost";
$dbname = "momster";
$user = "root";
$pass = "";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
