<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container dashboard-container">
    <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
    <a href="logout.php">Sair</a>

    <h3>Usuários Online</h3>
    <ul id="lista-usuarios"></ul>
</div>

<script>
function atualizarUsuarios() {
    fetch('usuarios_online.php')
        .then(res => res.json())
        .then(data => {
            const lista = document.getElementById('lista-usuarios');
            lista.innerHTML = '';
            data.forEach(usuario => {
                const li = document.createElement('li');
                li.textContent = `${usuario.nome} - ${usuario.status}`;
                lista.appendChild(li);
            });
        });
}

setInterval(atualizarUsuarios, 10000); // Atualiza a cada 10s
atualizarUsuarios();

setInterval(() => {
    fetch('ping.php'); // Mantém o usuário online
}, 30000); // a cada 30s

</script>
</body>

</html>
