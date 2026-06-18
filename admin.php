 <?php
// =============================================
//  admin.php — Painel para ver as mensagens
//  Acesse: seusite.com/admin.php
// =============================================

session_start();
require_once 'config.php';

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['usuario'] === ADMIN_USUARIO && $_POST['senha'] === ADMIN_SENHA) {
        $_SESSION['admin'] = true;
    } else {
        $erro_login = 'Usuário ou senha incorretos.';
    }
}

// Logout
if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Busca mensagens se logado
$contatos = [];
$total = 0;
if (!empty($_SESSION['admin'])) {
    try {
        $pdo = conectar();
        $total = $pdo->query('SELECT COUNT(*) FROM contatos')->fetchColumn();
        $pagina  = max(1, intval($_GET['p'] ?? 1));
        $limite  = 15;
        $offset  = ($pagina - 1) * $limite;
        $stmt = $pdo->prepare('SELECT * FROM contatos ORDER BY criado_em DESC LIMIT :limite OFFSET :offset');
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $contatos = $stmt->fetchAll();
        $total_paginas = ceil($total / $limite);
    } catch (PDOException $e) {
        $erro_db = 'Não foi possível conectar ao banco de dados.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel Admin — MundoPlastificado</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --verde: #085041;
    --verde-medio: #1D9E75;
    --verde-palido: #E1F5EE;
    --cinza: #2C2C2A;
    --muted: #888780;
    --fundo: #F5F5F2;
    --branco: #fff;
    --marrom: #4b2309;
    --preto: #000000;
}
  
  body { font-family: 'DM Sans', sans-serif; background: var(--preto); color: var(--cinza); min-height: 100vh; }

  /* ── Login ── */
  .login-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .login-card {
    background: var(--verde);
    border: 1px solid rgba(8,80,65,0.12);
    border-radius: 16px;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 380px;
    text-align: center;
  }
  .login-card h1 { font-size: 1.4rem; font-weight: 500; margin-bottom: 0.3rem; color: var(--branco); }
  .login-card p { font-size: 0.85rem; color: var(--muted); margin-bottom: 1.75rem; }
  .login-card input {
    background: #000000;
    border: 1.5px solid var(--verde-medio);
    border-radius: 8px;
    padding: 0.7rem 1rem;
    width: 100%;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    outline: none;
    margin-bottom: 0.75rem;
    color: #ffffff;
  }
  .login-card input:focus { border-color: var(--verde-medio); }
  .login-card button {
    width: 100%;
    background: var(--marrom);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.75rem;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 0.25rem;
  }
  .login-card button:hover { background: var(--verde-medio); }
  .erro-msg { font-size: 0.85rem; color: #a32d2d; margin-top: 0.75rem; }

  /* ── Painel ── */
  .topbar {
    background: var(--marrom);
    color: #ffffff;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .topbar strong { font-size: 1rem; font-weight: 500; }
  .topbar a { color: rgba(255,255,255,0.7); font-size: 0.85rem; text-decoration: none; transition: color 0.2s; }
  .topbar a:hover { color: #ffffff; }

  .painel { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem 4rem; }

  .stat-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
  }
  .stat-box {
    background: var(--verde-medio);
    border: 1px solid rgba(8,80,65,0.1);
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    flex: 1;
  }
  .stat-box span { font-size: 0.8rem; color: var(--fundo); display: block; margin-bottom: 0.2rem; }
  .stat-box strong { font-size: 1.75rem; font-weight: 500; color: var(--fundo); }

  table {
    table {
    width: 100%;
    border-collapse: collapse;
    background: #000000;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(8,80,65,0.1);
    font-size: 0.88rem;
  }
  thead { background: #000000 }
  thead th {
    text-align: left;
    padding: 0.9rem 1.1rem;
    font-weight: 500;
    font-size: 0.8rem;
    color: var(--branco);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  tbody tr { border-top: 1px solid rgba(8,80,65,0.07); transition: background 0.15s; }
  tbody tr:hover { background: #111111; }
  tbody td { padding: 0.85rem 1.1rem; vertical-align: top; color: #ffffff; }
  td.msg-cell p { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px; color: var(--branco); }

  .badge {
    display: inline-block;
    font-size: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 100px;
    background: var(--verde-palido);
    color: var(--verde);
    white-space: nowrap;
  }

  .paginacao { display: flex; gap: 0.5rem; margin-top: 1.5rem; justify-content: center; }
  .paginacao a {
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    border: 1px solid rgba(8,80,65,0.2);
    font-size: 0.85rem;
    color: var(--verde);
    text-decoration: none;
    transition: background 0.15s;
  }
  .paginacao a:hover, .paginacao a.ativo { background: var(--verde); color: #fff; border-color: var(--verde); }

  .vazio { text-align: center; padding: 3rem; color: var(--muted); font-size: 0.95rem; }
  .erro-db { background: #fff0f0; border: 1px solid #f09595; color: #a32d2d; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
</style>
</head>
<body>

<?php if (empty($_SESSION['admin'])): ?>
<!-- LOGIN -->
<div class="login-wrap">
  <div class="login-card">
    <h1>MenosPlástico</h1>
    <p>Painel de mensagens — acesso restrito</p>
    <form method="POST">
      <input type="text"     name="usuario" placeholder="Usuário" required autocomplete="username">
      <input type="password" name="senha"   placeholder="Senha"   required autocomplete="current-password">
      <button type="submit" name="login">Entrar</button>
    </form>
    <?php if (!empty($erro_login)): ?>
      <p class="erro-msg"><?= htmlspecialchars($erro_login) ?></p>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<!-- PAINEL -->
<div class="topbar">
  <strong>🌿 MundoPlastificado — Painel Admin</strong>
  <a href="?sair=1">Sair</a>
</div>

<div class="painel">

  <?php if (!empty($erro_db)): ?>
    <div class="erro-db"><?= htmlspecialchars($erro_db) ?></div>
  <?php endif; ?>

  <div class="stat-bar">
    <div class="stat-box">
      <span>Total de mensagens</span>
      <strong><?= $total ?></strong>
    </div>
    <div class="stat-box">
      <span>Página atual</span>
      <strong><?= $pagina ?? 1 ?> / <?= $total_paginas ?? 1 ?></strong>
    </div>
  </div>

  <?php if (empty($contatos)): ?>
    <p class="vazio">Nenhuma mensagem recebida ainda.</p>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Assunto</th>
        <th>Mensagem</th>
        <th>Data</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($contatos as $c): ?>
      <tr>
        <td><?= $c['id'] ?></td>
        <td><?= htmlspecialchars($c['nome']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><span class="badge"><?= htmlspecialchars($c['assunto']) ?></span></td>
        <td class="msg-cell"><p title="<?= htmlspecialchars($c['mensagem']) ?>"><?= htmlspecialchars($c['mensagem']) ?></p></td>
        <td><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if (($total_paginas ?? 1) > 1): ?>
  <div class="paginacao">
    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
      <a href="?p=<?= $i ?>" class="<?= $i === $pagina ? 'ativo' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>
<?php endif; ?>

</body>
</html>
