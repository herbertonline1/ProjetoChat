<?php

session_start();
require_once 'conexao.php';

$remetente_id = $_SESSION['usuario_id'];
$destinatario_id = $_POST['destinatario_id'];
$texto = trim($_POST['texto']);

if ($texto !== '') {
    $stmt = $pdo->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, texto, data_envio) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$remetente_id, $destinatario_id, $texto]);
    echo 'ok';
} else {
    echo 'erro';
}