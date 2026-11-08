<?php
/**
 * RUSTIC IRON - CONTROLLER DO PAINEL DA RECEPÇÃO & CATRACA
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
$usuarioId = $_SESSION['usuario_id'];

try {
    $pdo = Database::getTenantConnection($tenantDb);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de conexão com a base de dados da unidade.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'carregar_painel_recepcao':
        try {
            $stmtAlunos = $pdo->query("SELECT id, matricula, nome, cpf, telefone, plano, catraca_liberada FROM alunos ORDER BY nome ASC");
            $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($alunos as &$a) {
                $a['id'] = (int)$a['id'];
                $a['catraca_liberada'] = (int)$a['catraca_liberada'];
            }
            unset($a);

            $stmtFaturas = $pdo->query("SELECT id, aluno, cpf, plano, valor, vencimento, status FROM faturas ORDER BY vencimento DESC");
            $faturas = $stmtFaturas->fetchAll(PDO::FETCH_ASSOC);
            foreach ($faturas as &$f) {
                $f['valor'] = (float)$f['valor'];
            }
            unset($f);

            $stmtLogs = $pdo->query("SELECT horario, aluno, cpf, sentido, autorizado, motivo FROM logs_catraca ORDER BY id DESC LIMIT 20");
            $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($logs as &$l) {
                $l['autorizado'] = (int)$l['autorizado'];
            }
            unset($l);

            echo json_encode([
                'sucesso' => true,
                'alunos' => $alunos,
                'faturas' => $faturas,
                'logs' => $logs
            ]);
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    case 'validar_catraca':
        $termo = trim($_POST['termo'] ?? '');
        $sentido = trim($_POST['sentido'] ?? 'Entrada');

        if (empty($termo)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Identificador vazio.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM alunos WHERE cpf = ? OR matricula = ? OR nome LIKE ? LIMIT 1");
            $stmt->execute([$termo, $termo, "%$termo%"]);
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

            $horaAtual = date('H:i');
            $autorizado = 0;
            $motivo = '';
            $nomeAluno = 'Não Cadastrado';
            $planoAluno = '';

            if ($sentido === 'Saída') {
                $autorizado = 1;
                $motivo = 'Saída Regular';
                $nomeAluno = $aluno ? $aluno['nome'] : 'Aluno';
                $planoAluno = $aluno ? $aluno['plano'] : '';
            } else if (!$aluno) {
                $motivo = 'Cadastro Inexistente';
            } else if ((int)$aluno['catraca_liberada'] === 1) {
                $autorizado = 1;
                $motivo = 'Acesso Autorizado';
                $nomeAluno = $aluno['nome'];
                $planoAluno = $aluno['plano'];
            } else {
                $motivo = 'Bloqueio Financeiro';
                $nomeAluno = $aluno['nome'];
                $planoAluno = $aluno['plano'];
            }

            $stmtLog = $pdo->prepare("INSERT INTO logs_catraca (horario, aluno, cpf, sentido, autorizado, motivo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtLog->execute([$horaAtual, $nomeAluno, $termo, $sentido, $autorizado, $motivo]);

            echo json_encode([
                'sucesso' => true,
                'autorizado' => $autorizado,
                'motivo' => $motivo,
                'nome' => $nomeAluno,
                'plano' => $planoAluno
            ]);
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    case 'cadastrar_aluno':
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $plano = trim($_POST['plano'] ?? '');
        $catraca = (int)($_POST['catraca_liberada'] ?? 1);

        if (empty($nome) || empty($cpf)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Preencha os campos obrigatórios.']);
            exit;
        }

        try {
            $stmtMax = $pdo->query("SELECT MAX(id) as max_id FROM alunos");
            $resMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
            $nextId = ($resMax['max_id'] ?? 0) + 1;
            $matricula = 'MAT-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $stmtIns = $pdo->prepare("INSERT INTO alunos (matricula, nome, cpf, telefone, plano, catraca_liberada) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtIns->execute([$matricula, $nome, $cpf, $telefone, $plano, $catraca]);

            echo json_encode(['sucesso' => true, 'mensagem' => 'Aluno cadastrado com sucesso!']);
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    case 'dar_baixa_fatura':
        $faturaId = trim($_POST['fatura_id'] ?? '');
        if (empty($faturaId)) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da fatura inválido.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmtFat = $pdo->prepare("UPDATE faturas SET status = 'pago' WHERE id = ?");
            $stmtFat->execute([$faturaId]);

            $stmtGet = $pdo->prepare("SELECT cpf FROM faturas WHERE id = ?");
            $stmtGet->execute([$faturaId]);
            $fat = $stmtGet->fetch(PDO::FETCH_ASSOC);

            if ($fat && !empty($fat['cpf'])) {
                $stmtAlu = $pdo->prepare("UPDATE alunos SET catraca_liberada = 1 WHERE cpf = ?");
                $stmtAlu->execute([$fat['cpf']]);
            }

            $pdo->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Baixa realizada e catraca liberada!']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao dar baixa: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação não reconhecida.']);
        break;
}