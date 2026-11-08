<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $tenantDbName = $_SESSION['tenant_db'] ?? 'rustic_tenant_p';
    $pdoTenant = Database::getTenantConnection($tenantDbName);

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // 1. LISTAR SETORES
    if ($action === 'listar' || empty($action)) {
        $stmt = $pdoTenant->query("SELECT id, nome, responsavel, capacidade, status FROM setores ORDER BY id DESC");
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'sucesso' => true,
            'dados' => $dados
        ]);
        exit;
    }

    // 2. SALVAR (CADASTRAR OU EDITAR) SETOR
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'salvar' || isset($_POST['nome']))) {
        $idEdit      = $_POST['id_edit'] ?? '';
        $nome        = trim($_POST['nome'] ?? '');
        $responsavel = trim($_POST['responsavel'] ?? '');
        $capacidade  = (int)($_POST['capacidade'] ?? 20);
        $status      = $_POST['status'] ?? 'ATIVO';

        if (empty($nome)) {
            echo json_encode(['sucesso' => false, 'erro' => 'O nome do setor não pode estar vazio.']);
            exit;
        }

        if (!empty($idEdit)) {
            $stmt = $pdoTenant->prepare("UPDATE setores SET nome = ?, responsavel = ?, capacidade = ?, status = ? WHERE id = ?");
            $stmt->execute([$nome, $responsavel, $capacidade, $status, $idEdit]);
        } else {
            $stmt = $pdoTenant->prepare("INSERT INTO setores (nome, responsavel, capacidade, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $responsavel, $capacidade, $status]);
        }

        echo json_encode(['sucesso' => true, 'mensagem' => 'Setor salvo com sucesso!']);
        exit;
    }

    // 3. EXCLUIR SETOR
    if ($action === 'excluir' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdoTenant->prepare("DELETE FROM setores WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['sucesso' => true, 'mensagem' => 'Setor removido com sucesso!']);
            exit;
        }
        echo json_encode(['sucesso' => false, 'erro' => 'ID de setor inválido.']);
        exit;
    }

    echo json_encode(['sucesso' => false, 'erro' => 'Ação desconhecida.']);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}