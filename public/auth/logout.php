<?php
/**
 * RUSTIC MASTER - ENCERRAMENTO DE SESSÃO (LOGOUT)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],$params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Como estás dentro da pasta auth, redireciona direto para o ficheiro de login (ex: index.php ou login.php)
header("Location: auth.php"); // ou o nome da página de login que tens dentro de auth
exit;
