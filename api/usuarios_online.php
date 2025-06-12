<?php

require_once 'conexao.php';

// Remove usuários inativos há mais de 60 segundos
// $pdo->exec("DELETE FROM usuarios_online WHERE last_activity < (NOW() - INTERVAL 60 SECOND)");

// Pega todos os usuários online com nome e status
$sql = "SELECT u.nome, uo.status, uo.last_activity 
        FROM usuarios_online uo
        JOIN usuarios u ON u.id = uo.usuario_id
        ORDER BY u.nome";

$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($usuarios);
?>