<?php
// Configure aqui seu banco de dados MySQL
$host = 'localhost';
$db   = 'sistema_login';
$user = 'root';
$pass = '';  // coloque sua senha do MySQL

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco: " . $e->getMessage());
}
