<?php
// =============================================
//  config.php — Configurações do banco
//  EDITE os valores abaixo com os dados
//  que a sua hospedagem forneceu
// =============================================

define('DB_HOST', 'sql109.infinityfree.com');         // quase sempre é localhost
define('DB_NAME', 'if0_41790332_mundoplastificado');     // nome do banco 
define('DB_USER', 'if0_41790332');              // usuário do banco 
define('DB_PASS','w8VJtviTp8wK' );    // senha do banco 
define('DB_CHARSET', 'utf8mb4');

define('ADMIN_USUARIO', 'tucas');       // login do painel admin
define('ADMIN_SENHA',   'tucas@908020');   // senha do painel admin 

function conectar(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $opcoes);
}
