<?php
// =============================================
//  contato.php — Recebe o formulário e salva
//  no banco de dados MySQL
// =============================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
    exit;
}

// Função para sanitizar entrada
function limpar(string $valor): string {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

// Coleta e valida os campos
$nome     = limpar($_POST['nome']     ?? '');
$email    = limpar($_POST['email']    ?? '');
$assunto  = limpar($_POST['assunto']  ?? '');
$mensagem = limpar($_POST['mensagem'] ?? '');

$erros = [];

if (strlen($nome) < 2)                        $erros[] = 'Nome inválido.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if (strlen($assunto) < 2)                      $erros[] = 'Assunto inválido.';
if (strlen($mensagem) < 5)                     $erros[] = 'Mensagem muito curta.';

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erros' => $erros]);
    exit;
}

// Salva no banco
try {
    $pdo = conectar();

    $sql = 'INSERT INTO contatos (nome, email, assunto, mensagem, ip)
            VALUES (:nome, :email, :assunto, :mensagem, :ip)';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':assunto'  => $assunto,
        ':mensagem' => $mensagem,
        ':ip'       => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    // Envia notificação por e-mail (opcional)
    $para    = 'proj.desenvolv.si.unip@gmail.com';
    $assunto_email = "Nova mensagem — MenosPlástico: $assunto";
    $corpo   = "Nome: $nome\nE-mail: $email\nAssunto: $assunto\n\nMensagem:\n$mensagem";
    $headers = "From: noreply@menosplastico.com.br\r\nReply-To: $email";
    @mail($para, $assunto_email, $corpo, $headers);

    echo json_encode(['ok' => true, 'mensagem' => 'Contato salvo com sucesso!']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro interno no servidor.']);
    // Para depurar, descomente a linha abaixo (remova em produção!):
    // echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
