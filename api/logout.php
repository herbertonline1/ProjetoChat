<?php
session_start();
require_once 'conexao.php';

if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $pdo->prepare("DELETE FROM usuarios_online WHERE usuario_id = ?")->execute([$usuario_id]);
}

session_destroy();
header("Location: login.php");
exit;
