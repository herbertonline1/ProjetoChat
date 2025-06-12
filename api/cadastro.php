<?php
session_start();
require_once "conexao.php"; // arquivo de conexão com PDO

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $foto_nome = null;

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } else {
        // Verifica se email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erro = "Este email já está cadastrado.";
        } else {
            // Processa a foto
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $foto_tmp = $_FILES['foto']['tmp_name'];
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $foto_nome = uniqid() . '.' . $ext;
                $destino = 'uploads/' . $foto_nome;

                if (!move_uploaded_file($foto_tmp, $destino)) {
                    $erro = "Erro ao salvar a foto de perfil.";
                }
            } else {
                $erro = "Foto de perfil é obrigatória.";
            }

            // Se não houve erro até aqui, salva no banco
            if (empty($erro)) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, foto) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$nome, $email, $senha_hash, $foto_nome])) {
                    // login automático após cadastro
$_SESSION['usuario_id'] = $pdo->lastInsertId();
$_SESSION['usuario_nome'] = $nome;
header("Location: index.php");

                    exit;
                } else {
                    $erro = "Erro ao cadastrar. Tente novamente.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Cadastro</h2>
    <?php if (!empty($erro)) echo "<p class='error-msg'>$erro</p>"; ?>
    <form method="post" action="" enctype="multipart/form-data">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="file" name="foto" accept="image/*" required>
        <button type="submit">Cadastrar</button>
    </form>
    <p>Já tem conta? <a href="login.php">Entrar</a></p>
</div>
</body>
</html>
