<?php
session_start();
require 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome = $_POST['novo_nome'];
    $foto = $_FILES['nova_foto'];

    if (!empty($foto['name'])) {
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $novo_nome_arquivo = uniqid() . '.' . $ext;
        move_uploaded_file($foto['tmp_name'], "uploads/" . $novo_nome_arquivo);

        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, foto = ? WHERE id = ?");
        $stmt->execute([$novo_nome, $novo_nome_arquivo, $usuario_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
        $stmt->execute([$novo_nome, $usuario_id]);
    }

    $_SESSION['usuario_nome'] = $novo_nome;
    header("Location: index.php"); // ou a página principal do chat
    exit;
}
?>
