<?php
/**
 * RUSTIC MASTER - CONTROLLER DE FATURAMENTO & PIX
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Caminho absoluto seguro baseado na pasta atual para evitar conflitos
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Garante que apenas o Super Admin global pode aceder a este endpoint
if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'acesso_negado']);
    exit;
}

try {
    $pdoMaster = Database::getMasterConnection();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // ==========================================
    // LISTAGEM DE FATURAS (GET)
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'listar') {
        $stmt = $pdoMaster->query("SELECT * FROM faturas ORDER BY vencimento DESC");
        $faturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['sucesso' => true, 'dados' => $faturas]);
        exit;
    }

    // ==========================================
    // AÇÕES DE POST (GERAR LOTE & DAR BAIXA)
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 1. Gerar Lote de Cobranças do Mês
        if ($action === 'gerar_lote') {
            $stmtTenants = $pdoMaster->query("SELECT id, nome_academia, COALESCE(valor_plano, 249.90) as valor_plano FROM tenants WHERE status = 'ativo' OR status = 'ATIVO'");
            $tenants = $stmtTenants->fetchAll(PDO::FETCH_ASSOC);

            $geradas = 0;
            $mesAtual = date('Y-m'); // Ex: '2026-11'

            foreach ($tenants as $t) {
                // VERIFICAÇÃO BLINDADA: Procura se já existe QUALQUER fatura gerada neste mesmo mês para esta academia
                $stmtCheck = $pdoMaster->prepare("
                    SELECT id FROM faturas 
                    WHERE tenant_id = ? 
                    AND (DATE_FORMAT(criado_em, '%Y-%m') = ? OR DATE_FORMAT(vencimento, '%Y-%m') = ?)
                    LIMIT 1
                ");
                $stmtCheck->execute([$t['id'], $mesAtual, $mesAtual]);
                $faturaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                // Se já existir fatura para este mês (independente de estar paga ou pendente), bloqueia a criação da duplicada!
                if ($faturaExistente) {
                    continue; 
                }

                $valor = $t['valor_plano'] ?: 249.90;
                $vencimento = date('Y-m-d', strtotime('+30 days'));
                
                $pixCopiaECola = "00020126580014br.gov.bcb.pix0136suporte@rusticmaster.com.br5204000053039865802BR5925Rustic Master SaaS6009Olinda/PE62070503***6304";

                $stmtInsert = $pdoMaster->prepare("
                    INSERT INTO faturas (tenant_id, academia, valor, vencimento, status, pix_copia_cola) 
                    VALUES (?, ?, ?, ?, 'pendente', ?)
                ");
                $stmtInsert->execute([$t['id'], $t['nome_academia'], $valor, $vencimento, $pixCopiaECola]);
                $geradas++;
            }

            echo json_encode(['sucesso' => true, 'mensagem' => "Lote rodado com sucesso! $geradas novas faturas geradas (duplicadas do mês bloqueadas)."]);
            exit;
        }

        // 2. Dar Baixa na Fatura (Marcar como Pago)
        if ($action === 'dar_baixa') {
            $faturaId = $_POST['fatura_id'] ?? '';
            if (empty($faturaId)) {
                echo json_encode(['sucesso' => false, 'erro' => 'id_vazio']);
                exit;
            }

            $stmt = $pdoMaster->prepare("UPDATE faturas SET status = 'pago', pago_em = CURRENT_DATE WHERE id = ?");
            $stmt->execute([$faturaId]);

            echo json_encode(['sucesso' => true]);
            exit;
        }
    }

    echo json_encode(['sucesso' => false, 'erro' => 'acao_invalida']);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    exit;
}