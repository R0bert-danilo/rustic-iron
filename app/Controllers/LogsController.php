<?php
/**
 * RUSTIC MASTER - CONTROLLER DE LOGS E AUDITORIA
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Correção do caminho relativo para a raiz (2 níveis acima: app/controllers/ -> rustic_iron/)
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Garante que apenas o Super Admin global aceda
if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'acesso_negado']);
    exit;
}

try {
    $pdoMaster = Database::getMasterConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_REQUEST['action'] ?? '';

    // 1. REGISTAR UM NOVO LOG (VIA POST OU ACTION REGISTRAR)
    if ($method === 'POST' || $action === 'registrar') {
        $acao = $_POST['evento_acao'] ?? 'Ação do Sistema';
        $tenant = $_POST['tenant_alvo'] ?? 'Geral';
        $operador = $_POST['operador'] ?? 'Super Admin';
        $resultado = $_POST['resultado'] ?? 'Sucesso';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $pdoMaster->prepare("
            INSERT INTO master_logs (ip_de_origem, evento_acao, tenant_alvo, operador, resultado) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$ip, $acao, $tenant, $operador, $resultado]);

        echo json_encode(['sucesso' => true]);
        exit;
    }

    // 2. LISTAR OS LOGS (VIA GET COM ACTION LISTAR)
    if ($action === 'listar' || empty($action)) {
        $stmt = $pdoMaster->query("SELECT * FROM master_logs ORDER BY id DESC LIMIT 100");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['sucesso' => true, 'dados' => $logs]);
        exit;
    }

    echo json_encode(['sucesso' => false, 'erro' => 'acao_invalida']);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    exit;
}