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

    // Trata o salvamento tanto se a action estiver vazia quanto se vier como 'salvar'
    if (($action === '' || $action === 'salvar') && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eqpNome'])) {
        $idEdit           = $_POST['equipamento_id_edit'] ?? '';
        $nome             = trim($_POST['eqpNome'] ?? '');
        $codigo           = trim($_POST['eqpCodigo'] ?? '');
        $setor            = trim($_POST['eqpSetor'] ?? '');
        $quantidade       = intval($_POST['eqpQuantidade'] ?? 1);
        $estado           = $_POST['eqpEstado'] ?? 'Excelente';
        $ultimaRevisao    = !empty($_POST['eqpUltimaRevisao']) ? $_POST['eqpUltimaRevisao'] : null;
        $proximaRevisao   = !empty($_POST['eqpProximaRevisao']) ? $_POST['eqpProximaRevisao'] : null;
        $observacoes      = $_POST['eqpObservacoes'] ?? '';

        if (!empty($idEdit)) {
            $stmt = $pdoTenant->prepare("UPDATE equipamentos SET nome = ?, codigo = ?, setor = ?, quantidade = ?, estado = ?, ultima_revisao = ?, proxima_revisao = ?, observacoes = ? WHERE id = ?");
            $stmt->execute([$nome, $codigo, $setor, $quantidade, $estado, $ultimaRevisao, $proximaRevisao, $observacoes, $idEdit]);
        } else {
            $stmt = $pdoTenant->prepare("INSERT INTO equipamentos (nome, codigo, setor, quantidade, estado, ultima_revisao, proxima_revisao, observacoes, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nome, $codigo, $setor, $quantidade, $estado, $ultimaRevisao, $proximaRevisao, $observacoes]);
        }

        echo json_encode(['sucesso' => true, 'mensagem' => 'Equipamento salvo com sucesso!']);
        exit;
    }

    if ($action === 'listar') {
        $setorFiltro = trim($_GET['setor'] ?? 'todos');
        
        if ($setorFiltro !== 'todos' && $setorFiltro !== '') {
            // Busca flexível por igualdade exata ou contendo o termo para evitar quebras por & ou acentos
            $stmt = $pdoTenant->prepare("SELECT * FROM equipamentos WHERE setor = ? OR setor LIKE ? ORDER BY id DESC");
            $stmt->execute([$setorFiltro, "%{$setorFiltro}%"]);
        } else {
            $stmt = $pdoTenant->query("SELECT * FROM equipamentos ORDER BY id DESC");
        }
        
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    if ($action === 'excluir') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdoTenant->prepare("DELETE FROM equipamentos WHERE id = ?");
            $stmt->execute([$id]);
        }
        echo json_encode(['sucesso' => true, 'mensagem' => 'Equipamento removido com sucesso!']);
        exit;
    }

    echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida.']);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}