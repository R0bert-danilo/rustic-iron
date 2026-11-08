<?php
/**
 * RUSTIC IRON - CONTROLLER DE MODALIDADES E OBJETIVOS
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada.']);
    exit;
}

$tenantDb = $_SESSION['tenant_db'] ?? 'rustic_tenant_rustic_iron_matriz';

try {
    $pdo = Database::getTenantConnection($tenantDb);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de conexão com o banco.']);
    exit;
}

$action = $_REQUEST['action'] ?? 'listar';

if ($action === 'listar') {
    try {
        $stmt = $pdo->query("SELECT id, nome, tipo FROM modalidades_objetivos WHERE status = 'ATIVO' ORDER BY nome ASC");
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'sucesso' => true,
            'dados' => $dados
        ]);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}