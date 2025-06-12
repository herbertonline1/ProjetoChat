<?php

session_start();
require_once 'conexao.php';

$remetente_id = $_SESSION['usuario_id'];
$destinatario_id = $_POST['destinatario_id'];

if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
    $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid('img_') . '.' . $ext;
    $caminho = 'uploads/' . $nome_arquivo;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
        // Salva no banco como mensagem do tipo imagem
        $stmt = $pdo->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, texto, tipo, data_envio) VALUES (?, ?, ?, 'imagem', NOW())");
        $stmt->execute([$remetente_id, $destinatario_id, $nome_arquivo]);
        echo 'ok';
    } else {
        echo 'erro_upload';
    }
} else {
    echo 'erro_arquivo';
}