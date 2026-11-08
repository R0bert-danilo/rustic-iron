<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$configFile = __DIR__ . '/../../config/database.php';
if (!file_exists($configFile)) {
    $configFile = __DIR__ . '/../../../config/database.php';
}
require_once $configFile;

try {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $tenantDbName = $_SESSION['tenant_db'] ?? 'rustic_tenant_p';
    $pdoTenant = Database::getTenantConnection($tenantDbName);

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($action === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $idEdit = $_POST['id_edit'] ?? '';
        $nomeTurno = trim($_POST['nome_turno'] ?? '');

        if (!empty($nomeTurno)) {
            if (!empty($idEdit)) {
                $stmt = $pdoTenant->prepare("UPDATE turnos SET nome = ? WHERE id = ?");
                $stmt->execute([$nomeTurno, $idEdit]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Turno atualizado com sucesso!']);
            } else {
                $stmt = $pdoTenant->prepare("INSERT IGNORE INTO turnos (nome) VALUES (?)");
                $stmt->execute([$nomeTurno]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Turno criado com sucesso!']);
            }
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'O nome do turno não pode estar vazio.']);
        }
        exit;
    }

    if ($action === 'listar') {
        $stmt = $pdoTenant->query("SELECT * FROM turnos ORDER BY id ASC");
        echo json_encode(['sucesso' => true, 'dados' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'excluir') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdoTenant->prepare("DELETE FROM turnos WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Turno removido com sucesso!']);
        }
        exit;
    }

    echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida.']);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}