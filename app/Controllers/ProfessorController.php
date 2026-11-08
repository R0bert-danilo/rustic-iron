<?php
/**
 * RUSTIC IRON - CONTROLLER DO PAINEL DO PROFESSOR (INTEGRADO COM A TABELA `alunos` E TREINOS BASE)
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
    case 'carregar_painel':
        try {
            // 1. Carrega o catálogo geral de exercícios
            $stmtEx = $pdo->query("SELECT grupo_muscular, nome_exercicio FROM banco_exercicios ORDER BY id ASC");
            $exerciciosRaw = $stmtEx->fetchAll(PDO::FETCH_ASSOC);
            
            $bancoExercicios = [];
            foreach ($exerciciosRaw as $row) {
                $grupo = $row['grupo_muscular'];
                if (!isset($bancoExercicios[$grupo])) {
                    $bancoExercicios[$grupo] = [];
                }
                $bancoExercicios[$grupo][] = $row['nome_exercicio'];
            }

            // 1.1 Carrega os treinos base por modalidade cadastrados no banco
            $stmtBase = $pdo->query("SELECT modalidade, grupo_muscular, nome_exercicio, series, reps, carga FROM exercicios_base_modalidade");
            $baseRaw = $stmtBase->fetchAll(PDO::FETCH_ASSOC);

            $treinosBaseModalidade = [];
            foreach ($baseRaw as $b) {
                $mod = $b['modalidade'];
                $musc = $b['grupo_muscular'];
                if (!isset($treinosBaseModalidade[$mod])) {
                    $treinosBaseModalidade[$mod] = [];
                }
                if (!isset($treinosBaseModalidade[$mod][$musc])) {
                    $treinosBaseModalidade[$mod][$musc] = [];
                }
                $treinosBaseModalidade[$mod][$musc][] = [
                    'nome' => $b['nome_exercicio'],
                    'series' => $b['series'],
                    'reps' => $b['reps'],
                    'carga' => $b['carga'] ?? ''
                ];
            }

            // 2. Carrega os alunos ativos da tabela `alunos` e junta com `fichas_treino`
            $sqlAlunos = "
                SELECT 
                    a.id,
                    a.matricula,
                    a.nome,
                    a.cpf,
                    a.peso,
                    a.altura,
                    a.area_treino_especifico,
                    a.ultima_prescricao,
                    a.ultimo_acesso,
                    a.status_aluno,
                    f.treinos_json
                FROM alunos a
                LEFT JOIN fichas_treino f ON f.aluno_id = a.id
                WHERE (a.status_aluno = 'ATIVO' OR a.status_aluno = 'Ativo' OR a.status_aluno IS NULL)
                ORDER BY a.nome ASC
            ";
            $stmtAlunos = $pdo->query($sqlAlunos);
            $alunosRows = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

            $atletas = [];
            $hoje = new DateTime();

            foreach ($alunosRows as $a) {
                $statusCalc = 'Sem Ficha';
                $dataFormatada = 'Sem prescrição';

                if (!empty($a['ultima_prescricao'])) {
                    $dtPresc = new DateTime($a['ultima_prescricao']);
                    $dataFormatada = $dtPresc->format('d/m/Y');
                    $diasFicha = $hoje->diff($dtPresc)->days;

                    if ($diasFicha > 45) {
                        $statusCalc = 'Ficha Vencendo';
                    } else {
                        $statusCalc = 'Em Dia';
                    }
                }

                // Verifica alerta de evasão (>7 dias sem passar na catraca)
                if (!empty($a['ultimo_acesso'])) {
                    $dtAcesso = new DateTime($a['ultimo_acesso']);
                    if ($hoje->diff($dtAcesso)->days > 7) {
                        $statusCalc = 'Alerta Evasão';
                    }
                }

                $atletas[] = [
                    'id' => (int)$a['id'],
                    'matricula' => $a['matricula'] ?? '',
                    'nome' => $a['nome'],
                    'cpf' => $a['cpf'],
                    'peso' => isset($a['peso']) ? (float)$a['peso'] : 0,
                    'altura' => isset($a['altura']) ? (int)$a['altura'] : 0,
                    'modalidade' => !empty($a['area_treino_especifico']) ? $a['area_treino_especifico'] : 'Musculação Pesada',
                    'ultimaPrescricao' => $dataFormatada,
                    'statusTreino' => $statusCalc,
                    'treinos' => !empty($a['treinos_json']) ? json_decode($a['treinos_json'], true) : new stdClass()
                ];
            }

            echo json_encode([
                'sucesso' => true,
                'banco_exercicios' => $bancoExercicios,
                'treinos_base_modalidade' => $treinosBaseModalidade,
                'atletas' => $atletas
            ]);
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    case 'salvar_prescricao':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido.']);
            exit;
        }

        $alunoId = (int)($_POST['atleta_id'] ?? 0);
        $modalidade = trim($_POST['modalidade'] ?? 'Musculação Pesada');
        $treinosJson = $_POST['treinos_json'] ?? '{}';

        if ($alunoId <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do aluno inválido.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Atualiza `area_treino_especifico` e `ultima_prescricao` na tabela `alunos`
            $stmtUpd = $pdo->prepare("UPDATE alunos SET area_treino_especifico = ?, ultima_prescricao = CURDATE() WHERE id = ?");
            $stmtUpd->execute([$modalidade, $alunoId]);

            // Verifica se já existe ficha para este aluno
            $stmtCheck = $pdo->prepare("SELECT id FROM fichas_treino WHERE aluno_id = ?");
            $stmtCheck->execute([$alunoId]);
            $fichaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($fichaExistente) {
                $stmtFicha = $pdo->prepare("UPDATE fichas_treino SET professor_id = ?, modalidade = ?, treinos_json = ? WHERE aluno_id = ?");
                $stmtFicha->execute([$usuarioId, $modalidade, $treinosJson, $alunoId]);
            } else {
                $stmtFicha = $pdo->prepare("INSERT INTO fichas_treino (aluno_id, professor_id, modalidade, treinos_json) VALUES (?, ?, ?, ?)");
                $stmtFicha->execute([$alunoId, $usuarioId, $modalidade, $treinosJson]);
            }

            $pdo->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Prescrição gravada com sucesso!']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao gravar prescrição: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação não reconhecida.']);
        break;
}