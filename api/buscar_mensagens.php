<?php

session_start();
require_once 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$contato_id = $_GET['contato_id'] ?? 0;

if ($contato_id === 'all') {
    $stmt = $pdo->prepare("
        SELECT * FROM mensagens
        WHERE destinatario_id = ?
        ORDER BY data_envio ASC
    ");
    $stmt->execute([$usuario_id]);
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($mensagens);
    exit;
}

// Marcar como lidas as mensagens recebidas do contato
$pdo->prepare("
    UPDATE mensagens SET lida = 1
    WHERE remetente_id = :contato_id AND destinatario_id = :usuario_id AND lida = 0
")->execute([
    'contato_id' => $contato_id,
    'usuario_id' => $usuario_id
]);

$stmt = $pdo->prepare("
    SELECT m.*, u.nome AS remetente_nome
    FROM mensagens m
    JOIN usuarios u ON u.id = m.remetente_id
    WHERE (m.remetente_id = :usuario_id AND m.destinatario_id = :contato_id)
       OR (m.remetente_id = :contato_id AND m.destinatario_id = :usuario_id)
    ORDER BY m.data_envio ASC
");
$stmt->execute([
    'usuario_id' => $usuario_id,
    'contato_id' => $contato_id
]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($mensagens);