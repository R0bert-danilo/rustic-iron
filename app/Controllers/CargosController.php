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

    // Salvar ou Atualizar Cargo
    if ($action === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $idEdit = $_POST['id_edit'] ?? '';
        $nomeCargo = trim($_POST['nome_cargo'] ?? '');

        if (!empty($nomeCargo)) {
            if (!empty($idEdit)) {
                $stmt = $pdoTenant->prepare("UPDATE cargos SET nome = ? WHERE id = ?");
                $stmt->execute([$nomeCargo, $idEdit]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Cargo atualizado com sucesso!']);
            } else {
                $stmt = $pdoTenant->prepare("INSERT IGNORE INTO cargos (nome) VALUES (?)");
                $stmt->execute([$nomeCargo]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Cargo criado com sucesso!']);
            }
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'O nome do cargo não pode estar vazio.']);
        }
        exit;
    }

    // Listar Cargos
    if ($action === 'listar') {
        $stmt = $pdoTenant->query("SELECT * FROM cargos ORDER BY nome ASC");
        echo json_encode(['sucesso' => true, 'dados' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // Excluir Cargo
    if ($action === 'excluir') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdoTenant->prepare("DELETE FROM cargos WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Cargo removido com sucesso!']);
        }
        exit;
    }

    echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida.']);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}