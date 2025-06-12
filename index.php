<?php
session_start(); // Sempre inicie a sessão primeiro

require_once 'conexao.php'; // Inclua a conexão logo após a sessão

// Verifique se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id']; // Pegue o ID do usuário logado

// Atualiza ou insere presença do usuário logado na tabela de online
$pdo->prepare("
    INSERT INTO usuarios_online (usuario_id, status, last_activity)
    VALUES (?, 'online', NOW())
    ON DUPLICATE KEY UPDATE status='online', last_activity=NOW()
")->execute([$usuario_id]);

// Agora você pode buscar os dados do usuário normalmente
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Definir variáveis
$fotoUsuario = $usuario['foto'] ?? 'default.jpg'; // fallback
$_SESSION['usuario_nome'] = $usuario['nome'];
?>




</body>
</html>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>MSN Chat </title>
  <style>
    /* Reset básico */
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, #d0e6f6, #ffffff);
      transition: background-color 0.4s ease, color 0.4s ease;
      color: #333;
    }

    body.dark {
      background: linear-gradient(to right, #1a1a2e, #16213e);
      color: #ccc;
    }

    .msn-container {
      display: flex;
      width: 100%;
      height: 100vh;
    }

    .sidebar {
      width: 280px;
      background-color: #e0f0ff;
      border-right: 2px solid #b0c4de;
      padding: 20px;
      box-sizing: border-box;
      transition: background-color 0.4s ease, border-color 0.4s ease;
      position: relative;
    }

    body.dark .sidebar {
      background-color: #222b45;
      border-color: #444c6e;
    }

    .sidebar h2 {
      font-size: 20px;
      color: #336699;
      margin-bottom: 15px;
      transition: color 0.4s ease;
    }

    body.dark .sidebar h2 {
      color: #a2c4ff;
    }

    .contact-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .contact {
      display: flex;
      align-items: center;
      padding: 10px;
      margin-bottom: 10px;
      background-color: #f8fbff;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease, color 0.3s ease;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      color: #222;
    }

    body.dark .contact {
      background-color: #2a3558;
      color: #cbd5ff;
      box-shadow: none;
    }

    .contact:hover, .contact.active {
      background-color: #cce5ff;
      color: #004080;
      box-shadow: 0 0 10px #3399ff;
    }

    body.dark .contact:hover, body.dark .contact.active {
      background-color: #3a4b8c;
      color: #a2c4ff;
      box-shadow: 0 0 8px #a2c4ff;
    }

    .contact img {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      margin-right: 10px;
      object-fit: cover;
      border: 1.5px solid #4a90e2;
      transition: border-color 0.4s ease;
    }

    body.dark .contact img {
      border-color: #7faaff;
    }

    .contact-info {
      flex: 1;
    }

    .contact-name {
      font-weight: 600;
      font-size: 15px;
    }

    .status {
      font-size: 12px;
      color: gray;
      display: block;
      transition: color 0.3s ease;
    }

    body.dark .status {
      color: #888;
    }

    .chat-window {
      flex: 1;
      display: flex;
      flex-direction: column;
      background-color: #ffffff;
      transition: background-color 0.4s ease, color 0.4s ease;
      box-shadow: inset 0 0 10px #a0c4ff33;
      position: relative;
    }

    body.dark .chat-window {
      background-color: #1f263d;
      color: #ddd;
      box-shadow: inset 0 0 12px #4a90e233;
    }

    .chat-header {
      background-color: #4a90e2;
      color: white;
      padding: 15px;
      font-weight: bold;
      border-bottom: 2px solid #336699;
      user-select: none;
      transition: background-color 0.4s ease, border-color 0.4s ease;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    body.dark .chat-header {
      background-color: #357ab8;
      border-color: #224d88;
    }

    /* Foto perfil no header */
    .header-profile {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .header-profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 2px solid white;
      object-fit: cover;
    }

    /* Botão tema */
    #themeToggle {
      background: transparent;
      border: 1.5px solid white;
      border-radius: 4px;
      color: white;
      font-size: 12px;
      padding: 4px 10px;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.3s ease, color 0.3s ease;
      margin-left: 10px;
    }

    #themeToggle:hover {
      background-color: white;
      color: #4a90e2;
    }

    .chat-messages {
      flex: 1;
      padding: 15px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 10px;
      scroll-behavior: smooth;
      background: transparent;
    }

    .message {
      max-width: 60%;
      padding: 10px 14px;
      margin-bottom: 10px;
      border-radius: 15px;
      line-height: 1.4;
      display: inline-block;
      font-size: 14px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      opacity: 0;
      animation: fadeInUp 0.3s forwards;
      user-select: text;
      word-wrap: break-word;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .received {
      background-color: #d0eaff;
      border: 1px solid #3399ff;
      box-shadow: 0 2px 4px rgba(0, 102, 204, 0.2);
      align-self: flex-start;
      color: #003366;
    }

    body.dark .received {
      background-color: #304a80;
      border-color: #7faaff;
      color: #cce5ff;
      box-shadow: 0 0 10px #7faaff88;
    }

    .sent {
      background-color: #b6f7c1;
      border: 1px solid #33cc66;
      box-shadow: 0 2px 4px rgba(0, 153, 76, 0.2);
      align-self: flex-end;
      text-align: right;
      color: #1a5d22;
    }

    body.dark .sent {
      background-color: #2e6c3b;
      border-color: #77d88b;
      color: #d2ffd8;
      box-shadow: 0 0 10px #77d88b88;
    }

    /* Imagem na mensagem */
    .message img {
      max-width: 200px;
      border-radius: 10px;
      display: block;
      margin-top: 5px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .message img:hover {
      transform: scale(1.05);
    }

    .chat-input {
      display: flex;
      padding: 10px;
      border-top: 2px solid #b0c4de;
      background-color: #f1f1f1;
      transition: background-color 0.4s ease, border-color 0.4s ease;
      align-items: center;
      gap: 6px;
    }

    body.dark .chat-input {
      background-color: #2b3455;
      border-color: #4a5695;
    }

    .chat-input input[type="text"] {
      flex: 1;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-right: 0;
      transition: box-shadow 0.3s ease, border-color 0.3s ease;
      outline-offset: 0;
    }

    .chat-input input[type="text"]:focus {
      outline: none;
      box-shadow: 0 0 10px #4a90e2;
      border-color: #4a90e2;
      background-color: #fff;
    }

    body.dark .chat-input input[type="text"] {
      background-color: #20294b;
      border-color: #444c6e;
      color: #eee;
    }

    body.dark .chat-input input[type="text"]:focus {
      background-color: #2a3558;
    }

    /* Botão enviar e emojis */
    .chat-input button, 
    .chat-input #emojiBtn {
      padding: 10px 16px;
      background: linear-gradient(135deg, #4a90e2, #00c6ff);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .chat-input button:hover, 
    .chat-input #emojiBtn:hover {
      background: linear-gradient(135deg, #0073e6, #00aaff);
      box-shadow: 0 0 10px #00c6ff;
    }

    /* Input file escondido */
    #imageInput {
      display: none;
    }

    /* Emoji picker */
    #emojiPicker {
      position: absolute;
      bottom: 70px;
      right: 30px;
      background: white;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      padding: 8px;
      display: none;
      max-width: 300px;
      max-height: 200px;
      overflow-y: auto;
      z-index: 1000;
      user-select: none;
    }

    body.dark #emojiPicker {
      background: #2a2a40;
      border-color: #555577;
      color: #ddd;
    }

    #emojiPicker span {
      font-size: 22px;
      cursor: pointer;
      padding: 6px 8px;
      display: inline-block;
      transition: background-color 0.2s ease;
      border-radius: 6px;
    }

    #emojiPicker span:hover {
      background-color: #4a90e2;
      color: white;
    }



.piscar {
  animation: piscarNome 0.7s alternate infinite;
}
@keyframes piscarNome {
  from { color: #222; background: #ffe066; }
  to   { color: #fff; background: #4a90e2; }
}



.badge-nao-lidas {
  display: inline-block;
  min-width: 20px;
  padding: 2px 7px;
  font-size: 12px;
  background: #e74c3c;
  color: #fff;
  border-radius: 12px;
  margin-left: 8px;
  font-weight: bold;
  text-align: center;
  vertical-align: middle;
  box-shadow: 0 0 4px #e74c3c88;
  animation: badgePop 0.3s;
}
@keyframes badgePop {
  0% { transform: scale(0.7);}
  80% { transform: scale(1.2);}
  100% { transform: scale(1);}
}

  </style>
</head>
<body>
  <div class="msn-container">
    <div class="sidebar">
      <h2>Contatos</h2>
      <ul class="contact-list">
        <?php
        // Busca todos os usuários exceto o próprio usuário logado
        $stmt = $pdo->prepare("
          SELECT u.id, u.nome, u.foto, u.status
          FROM usuarios u
          WHERE u.id != ?
          ORDER BY u.nome
        ");
        $stmt->execute([$usuario_id]);
        $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($contatos as $index => $contato):
          // Ajusta a foto (fallback para default)
          $fotoContato = !empty($contato['foto']) ? 'uploads/' . htmlspecialchars($contato['foto']) : 'uploads/default.jpg';
          // Status amigável
          $status = strtolower($contato['status']);
          if ($status === 'online') {
            $statusLabel = 'Online';
          } elseif ($status === 'ausente' || $status === 'away') {
            $statusLabel = 'Ausente';
          } else {
            $statusLabel = 'Offline';
          }
        ?>
         <li 
  class="contact <?php echo ($index === 0) ? 'active' : ''; ?>" 
  data-id="<?php echo $contato['id']; ?>" 
  data-nome="<?php echo htmlspecialchars($contato['nome']); ?>" 
  data-foto="<?php echo $fotoContato; ?>" 
  onclick="trocarContato('<?php echo addslashes($contato['nome']); ?>', this)"
>
  <img src="<?php echo $fotoContato; ?>" alt="Foto <?php echo htmlspecialchars($contato['nome']); ?>" />
<div class="contact-info">
  <div class="contact-name"><?php echo htmlspecialchars($contato['nome']); ?></div>
  <span class="status"><?php echo $statusLabel; ?></span>
  <span class="badge-nao-lidas" style="display:none;"></span>
</div>
</li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="chat-window">
      <div class="chat-header">
        <div class="header-profile">
          <img src="<?php echo isset($contatos[0]) ? (!empty($contatos[0]['foto']) ? 'uploads/' . htmlspecialchars($contatos[0]['foto']) : 'uploads/default.jpg') : 'uploads/default.jpg'; ?>" alt="Foto perfil ativo" id="fotoPerfilAtivo" />
          <span id="nomeContatoAtivo"><?php echo isset($contatos[0]) ? htmlspecialchars($contatos[0]['nome']) : ''; ?></span>
        </div>
        <button id="themeToggle" title="Alternar tema">🌙</button>
        <div class="container dashboard-container" style="margin-left: auto; color: white; font-weight: bold;">
          

<div onclick="abrirModal()" style="cursor:pointer; display:flex; align-items:center;">
  <img src="uploads/<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Sua foto" style="width:32px; height:32px; border-radius:50%; vertical-align:middle; margin-right:8px; border:2px solid #fff; object-fit:cover;" />
  <span><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
</div>


           <!-- <img src="uploads/<?php echo htmlspecialchars($usuario['foto']); ?>"alt="Sua foto" style="width:32px; height:32px; border-radius:50%; vertical-align:middle; margin-right:8px; border:2px solid #fff; object-fit:cover;"/>
      

            <span> <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span> -->
         
        </div>
         &nbsp;|&nbsp; <a href="logout.php" style="color: #cce5ff; text-decoration: underline;">Sair</a>
      </div>
      
      <div class="chat-messages" id="chatMessages">
        <!-- mensagens aqui -->
      </div>

      <div class="chat-input">
        <button id="emojiBtn" title="Abrir emojis">😊</button>
        <input type="text" id="inputMensagem" placeholder="Digite sua mensagem..." autocomplete="off" />
        <label for="imageInput" title="Enviar imagem" style="cursor:pointer; font-size: 20px;">📷</label>
        <input type="file" id="imageInput" accept="image/*" />
        <button id="btnEnviar">Enviar</button>
      </div>

      <div id="emojiPicker"></div>
    </div>
  </div>
<script>
  const contatos = document.querySelectorAll('.contact');
  const nomeContatoAtivoEl = document.getElementById('nomeContatoAtivo');
  const fotoPerfilAtivoEl = document.getElementById('fotoPerfilAtivo');
  const chatMessages = document.getElementById('chatMessages');
  const inputMensagem = document.getElementById('inputMensagem');
  const btnEnviar = document.getElementById('btnEnviar');
  const emojiBtn = document.getElementById('emojiBtn');
  const emojiPicker = document.getElementById('emojiPicker');
  const imageInput = document.getElementById('imageInput');
  const themeToggle = document.getElementById('themeToggle');

  let contatoAtivo = contatos[0];
  let contatoAtivoId = contatoAtivo.getAttribute('data-id');
  let ultimasMensagens = {};
carregarMensagens();
  let temaEscuro = false;

  // Lista de emojis básicos para o seletor
  const emojis = [
    '😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇',
    '🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚',
    '😋','😜','😝','😛','🤑','🤗','🤩','🤔','🤨','😐',
    '😑','😶','🙄','😏','😣','😥','😮','🤐','😯','😪',
    '😫','🥱','😴','😌','😛','😜','😝','🤤','😒','😓'
  ];

  

  function trocarContato(nome, elContato) {
    // Remove active do antigo
    contatoAtivo.classList.remove('active');
    contatoAtivo = elContato;
    contatoAtivo.classList.add('active');
    nomeContatoAtivoEl.textContent = nome;
    fotoPerfilAtivoEl.src = elContato.getAttribute('data-foto');

    chatMessages.innerHTML = ''; // Limpa mensagens ao trocar contato

    // Demo: mensagem de boas vindas automática
    // addMensagemRecebida(`Olá, esta é a conversa com ${nome}.`, nome);
  }

  // Envia mensagem digitada
  // function enviarMensagem() {
  //   const texto = inputMensagem.value.trim();
  //   if (texto === '') return;

  //   // addMensagemEnviada(texto);
  //   inputMensagem.value = '';
  // }

  function enviarMensagem() {
  const texto = inputMensagem.value.trim();
  if (texto === '') return;

  fetch('enviar_mensagem.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'destinatario_id=' + encodeURIComponent(contatoAtivoId) + '&texto=' + encodeURIComponent(texto)
  })
  .then(res => res.text())
  .then(resp => {
    if (resp === 'ok') {
      inputMensagem.value = '';
      carregarMensagens();
    } else {
      alert('Erro ao enviar mensagem!');
    }
  });
}

  // Adiciona mensagem enviada no chat
  function addMensagemEnviada(texto) {
    const msgEl = document.createElement('div');
    msgEl.classList.add('message', 'sent');
    msgEl.textContent = texto;
    chatMessages.appendChild(msgEl);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Adiciona mensagem recebida no chat
  function addMensagemRecebida(texto, nome) {
    const msgEl = document.createElement('div');
    msgEl.classList.add('message', 'received');
    msgEl.textContent = texto;
    chatMessages.appendChild(msgEl);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Cria o picker de emojis
  function criarEmojiPicker() {
    emojiPicker.innerHTML = '';
    emojis.forEach(emoji => {
      const span = document.createElement('span');
      span.textContent = emoji;
      span.title = `Emoji ${emoji}`;
      span.onclick = () => {
        inputMensagem.value += emoji;
        inputMensagem.focus();
      };
      emojiPicker.appendChild(span);
    });
  }

  // Alterna a exibição do picker
  emojiBtn.addEventListener('click', () => {
    if (emojiPicker.style.display === 'block') {
      emojiPicker.style.display = 'none';
    } else {
      emojiPicker.style.display = 'block';
      inputMensagem.focus();
    }
  });

  // Fecha emoji picker se clicar fora
  document.addEventListener('click', (e) => {
    if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
      emojiPicker.style.display = 'none';
    }
  });

  // Envio de foto
imageInput.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    alert('Por favor, envie um arquivo de imagem válido.');
    imageInput.value = '';
    return;
  }

  const formData = new FormData();
  formData.append('imagem', file);
  formData.append('destinatario_id', contatoAtivoId);

  fetch('enviar_imagem.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(resp => {
    if (resp === 'ok') {
      carregarMensagens();
      imageInput.value = '';
    } else {
      alert('Erro ao enviar imagem!');
    }
  });
});

  // Adiciona mensagem de imagem enviada
  function addMensagemEnviadaImagem(src) {
    const msgEl = document.createElement('div');
    msgEl.classList.add('message', 'sent');
    const img = document.createElement('img');
    img.src = src;
    img.alt = 'Imagem enviada';
    msgEl.appendChild(img);
    chatMessages.appendChild(msgEl);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Botão enviar texto
  btnEnviar.addEventListener('click', enviarMensagem);
  inputMensagem.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      enviarMensagem();
    }
  });

  // Tema claro/escuro
  themeToggle.addEventListener('click', () => {
    temaEscuro = !temaEscuro;
    if (temaEscuro) {
      document.body.classList.add('dark');
      themeToggle.textContent = '☀️';
    } else {
      document.body.classList.remove('dark');
      themeToggle.textContent = '🌙';
    }
  });

  criarEmojiPicker();

  // Inicia conversa padrão com João
  trocarContato('João', contatos[0]);







    function abrirModal() {
    document.getElementById('modalPerfil').style.display = 'flex';
  }

  function fecharModal() {
    document.getElementById('modalPerfil').style.display = 'none';
  }



  





  // ...existing code...

function atualizarStatusContatos() {
  fetch('usuarios_online.php')
    .then(res => res.json())
    .then(usuariosOnline => {
      // Cria um Set com nomes dos usuários online
      const onlineSet = new Set(usuariosOnline.map(u => u.nome));
      document.querySelectorAll('.contact').forEach(li => {
        const nome = li.getAttribute('data-nome');
        const statusSpan = li.querySelector('.status');
        if (onlineSet.has(nome)) {
          statusSpan.innerHTML = 'Online <span style="display:inline-block;width:10px;height:10px;background:#28c940;border-radius:50%;margin-left:5px;vertical-align:middle;"></span>';
        } else {
            statusSpan.innerHTML = 'Offline <span style="display:inline-block;width:10px;height:10px;background:#e74c3c;border-radius:50%;margin-left:5px;vertical-align:middle;"></span>';
        }
      });
    });
}
setInterval(atualizarStatusContatos, 10000);
atualizarStatusContatos();





// ...definições de variáveis...



// Troca de contato
function trocarContato(nome, elContato) {
  contatoAtivo.classList.remove('active');
  contatoAtivo = elContato;
  contatoAtivo.classList.add('active');
  nomeContatoAtivoEl.textContent = nome;
  fotoPerfilAtivoEl.src = elContato.getAttribute('data-foto');
  contatoAtivoId = elContato.getAttribute('data-id');
  carregarMensagens();
}

// Carrega mensagens via AJAX
function carregarMensagens() {
  fetch('buscar_mensagens.php?contato_id=' + contatoAtivoId)
    .then(res => res.json())
    .then(mensagens => {
      chatMessages.innerHTML = '';
      mensagens.forEach(msg => {
        const msgEl = document.createElement('div');
        msgEl.classList.add('message', msg.remetente_id == <?php echo $usuario_id; ?> ? 'sent' : 'received');
        msgEl.textContent = msg.texto;
        chatMessages.appendChild(msgEl);
      });
      chatMessages.scrollTop = chatMessages.scrollHeight;
    });
}

// Envia mensagem para o backend
function enviarMensagem() {
  const texto = inputMensagem.value.trim();
  if (texto === '') return;

  fetch('enviar_mensagem.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'destinatario_id=' + encodeURIComponent(contatoAtivoId) + '&texto=' + encodeURIComponent(texto)
  })
  .then(res => res.text())
  .then(resp => {
    if (resp === 'ok') {
      inputMensagem.value = '';
      carregarMensagens();
    } else {
      alert('Erro ao enviar mensagem!');
    }
  });
}

// Apenas UM event listener para enviar
// btnEnviar.addEventListener('click', enviarMensagem);
// inputMensagem.addEventListener('keydown', (e) => {
//   if (e.key === 'Enter' && !e.shiftKey) {
//     e.preventDefault();
//     enviarMensagem();
//   }
// });

// Apenas UMA chamada inicial
carregarMensagens();

// Apenas UM setInterval
setInterval(carregarMensagens, 2000);

// Botão enviar texto
// btnEnviar.addEventListener('click', enviarMensagem);
// inputMensagem.addEventListener('keydown', (e) => {
//   if (e.key === 'Enter' && !e.shiftKey) {
//     e.preventDefault();
//     enviarMensagem();
//   }
// });

let ultimoConteudoChat = '';

function carregarMensagens() {
  fetch('buscar_mensagens.php?contato_id=' + contatoAtivoId)
    .then(res => res.json())
    .then(mensagens => {
      // Monta o HTML das mensagens em uma string
   let novoConteudo = '';
mensagens.forEach(msg => {
  if (msg.tipo === 'imagem') {
    novoConteudo += `<div class="message ${msg.remetente_id == <?php echo $usuario_id; ?> ? 'sent' : 'received'}">
      <img src="uploads/${msg.texto}" alt="Imagem enviada" style="max-width:200px; border-radius:10px;">
    </div>`;
  } else {
    novoConteudo += `<div class="message ${msg.remetente_id == <?php echo $usuario_id; ?> ? 'sent' : 'received'}">${msg.texto}</div>`;
  }
});

      // Só atualiza se mudou
      if (novoConteudo !== ultimoConteudoChat) {
        chatMessages.innerHTML = novoConteudo;
        chatMessages.scrollTop = chatMessages.scrollHeight;
        ultimoConteudoChat = novoConteudo;
      }
    });
}





function checarNovasMensagens() {
  fetch('buscar_mensagens.php?contato_id=all')
    .then(res => res.json())
    .then(todasMensagens => {
   document.querySelectorAll('.contact').forEach(li => {
  const contatoId = li.getAttribute('data-id');
  // Mensagens não lidas desse contato
  const msgsNaoLidas = todasMensagens.filter(msg =>
    msg.remetente_id == contatoId &&
    msg.destinatario_id == <?php echo $usuario_id; ?> &&
    msg.lida == 0
  );
  const nomeDiv = li.querySelector('.contact-name');
  const badge = li.querySelector('.badge-nao-lidas');
  if (msgsNaoLidas.length > 0 && contatoAtivoId != contatoId) {
    nomeDiv.classList.add('piscar');
    badge.textContent = msgsNaoLidas.length;
    badge.style.display = 'inline-block';
  } else {
    nomeDiv.classList.remove('piscar');
    badge.textContent = '';
    badge.style.display = 'none';
  }
      });
    });
}
setInterval(checarNovasMensagens, 1000);

// Função para piscar o nome do contato
function piscarContato(li) {
  const nomeDiv = li.querySelector('.contact-name');
  if (!nomeDiv.classList.contains('piscar')) {
    nomeDiv.classList.add('piscar');
    setTimeout(() => nomeDiv.classList.remove('piscar'), 9000); // pisca por 2 segundos
  }
}

// // Chame a checagem a cada 2 segundos
// setInterval(checarNovasMensagens, 1000);

</script>


<!-- Foto do usuário clicável -->
<!-- <div onclick="abrirModal()" style="cursor:pointer; display:flex; align-items:center;">
  <img id="fotoUsuario" src="uploads/<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Sua foto" style="width:32px; height:32px; border-radius:50%; vertical-align:middle; margin-right:8px; border:2px solid #fff; object-fit:cover;" />
  <span><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
</div> -->

<!-- Modal de Edição de Perfil -->
<div id="modalPerfil" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#00000080; z-index:10000; justify-content:center; align-items:center;">
  <div style="background:#fff; padding:20px; border-radius:10px; width:300px; text-align:center; position:relative;">
    <h3>Editar Perfil</h3>
    <form id="formPerfil" method="POST" action="atualizar_perfil.php" enctype="multipart/form-data">
      <input type="text" name="novo_nome" placeholder="Novo nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" style="width:100%; padding:8px; margin-bottom:10px;" />
      <input type="file" name="nova_foto" accept="image/*" style="margin-bottom:10px;" />
      <br/>
      <button type="submit" style="background:#4a90e2; color:white; padding:8px 16px; border:none; border-radius:6px;">Salvar</button>
      <button type="button" onclick="fecharModal()" style="margin-left:10px; background:#ccc; padding:8px 16px; border:none; border-radius:6px;">Cancelar</button>
    </form>
  </div>
</div>

</body>
</html>
