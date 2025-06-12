<?php
session_start();
require_once 'conexao.php';

if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("UPDATE usuarios_onlines SET last_activity = NOW() WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
}
?>
