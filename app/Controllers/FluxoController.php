<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Caminho corrigido e blindado para encontrar o database.php dentro de config
require_once __DIR__ . '/../../config/database.php';

try {
    $tenantDbName = $_SESSION['tenant_db'] ?? 'rustic_tenant_p';
    $pdoTenant = Database::getTenantConnection($tenantDbName);

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($action === 'dados_lotacao' || $action === 'fluxo_tempo_real') {
        $stmtSetores = $pdoTenant->query("SELECT id, nome, capacidade, COALESCE(atletas_presentes, 0) as atletas_presentes, COALESCE(monitoramento_ativo, 1) as monitoramento_ativo FROM setores");
        $setores = $stmtSetores->fetchAll(PDO::FETCH_ASSOC);

        $resultadoZonas = [];
        $totalGeralOcupacao = 0;
        $capacidadeTotalGeral = 0;

        foreach ($setores as $setor) {
            $setorId = (int)$setor['id'];
            $nomeZona = $setor['nome'];
            $capacidadeMax = (int)$setor['capacidade'];
            $ocupados = (int)$setor['atletas_presentes'];
            $monitorando = (int)$setor['monitoramento_ativo'];

            if ($monitorando === 1) {
                $capacidadeTotalGeral += $capacidadeMax;
                $totalGeralOcupacao += $ocupados;
            }

            $porcentagem = ($monitorando === 1 && $capacidadeMax > 0) ? round(($ocupados / $capacidadeMax) * 100) : 0;

            $resultadoZonas[] = [
                'id' => $setorId,
                'nome' => $nomeZona,
                'capacidade' => $capacidadeMax,
                'ocupados' => $ocupados,
                'monitoramento_ativo' => $monitorando,
                'porcentagem' => $porcentagem,
                'vagas_disponiveis' => max(0, $capacidadeMax - $ocupados)
            ];
        }

        echo json_encode([
            'sucesso' => true,
            'total_geral_ocupacao' => $totalGeralOcupacao,
            'capacidade_total_geral' => $capacidadeTotalGeral,
            'zonas' => $resultadoZonas
        ]);
        exit;
    }

    if ($action === 'toggle_monitoramento' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $setorId = $_POST['id'] ?? '';
        $statusAtual = $_POST['status_atual'] ?? 1;
        $novoStatus = $statusAtual == 1 ? 0 : 1;

        if (!empty($setorId)) {
            $stmt = $pdoTenant->prepare("UPDATE setores SET monitoramento_ativo = ? WHERE id = ?");
            $stmt->execute([$novoStatus, $setorId]);

            echo json_encode(['sucesso' => true, 'novo_status' => $novoStatus]);
            exit;
        }

        echo json_encode(['sucesso' => false, 'erro' => 'ID do setor inválido.']);
        exit;
    }

    echo json_encode(['sucesso' => false, 'erro' => 'Ação desconhecida.']);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}