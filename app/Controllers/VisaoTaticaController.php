<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

try {
    $tenantDbName = $_SESSION['tenant_db'] ?? 'rustic_tenant_p';
    $pdoTenant = Database::getTenantConnection($tenantDbName);

    // Tabelas de Escalas, Exceções Diárias e Trocas de Emergência
    $pdoTenant->exec("CREATE TABLE IF NOT EXISTS escalas_mensais (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colaborador_id INT NOT NULL,
        mes_ano VARCHAR(7) NOT NULL,
        padrao_seg_sex VARCHAR(50) DEFAULT '06:00 - 14:00',
        padrao_sabado VARCHAR(50) DEFAULT 'Folga',
        padrao_domingo VARCHAR(50) DEFAULT 'Folga'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdoTenant->exec("CREATE TABLE IF NOT EXISTS escala_excecoes_diarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colaborador_id INT NOT NULL,
        data_especifica DATE NOT NULL,
        novo_status VARCHAR(100) NOT NULL,
        motivo VARCHAR(255) DEFAULT NULL,
        UNIQUE KEY unique_colab_data (colaborador_id, data_especifica)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdoTenant->exec("CREATE TABLE IF NOT EXISTS trocas_escala_emergencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colaborador_original_id INT NOT NULL,
        colaborador_substituto_id INT NOT NULL,
        data_ocorrencia DATE NOT NULL,
        tipo VARCHAR(30) DEFAULT 'Emergência / Doença',
        motivo TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // 1. DADOS TÁTICOS & KPIS (DINÂMICOS)
    if ($action === 'dados_taticos') {
        $hoje = date('Y-m-d');
        
        // Colaboradores escalados hoje (verificando exceções ou turnos ativos)
        $stmtCollab = $pdoTenant->query("SELECT id, nome, cargo, turno FROM colaboradores WHERE status = 'ATIVO'");
        $colaboradores = $stmtCollab->fetchAll(PDO::FETCH_ASSOC);

        $escalaHoje = [];
        foreach ($colaboradores as $c) {
            $stmtEx = $pdoTenant->prepare("SELECT novo_status FROM escala_excecoes_diarias WHERE colaborador_id = ? AND data_especifica = ?");
            $stmtEx->execute([$c['id'], $hoje]);
            $excecao = $stmtEx->fetch(PDO::FETCH_ASSOC);

            $statusTurno = $excecao ? $excecao['novo_status'] : ($c['turno'] ?? 'Turno Geral');

            if ($statusTurno !== 'Folga') {
                $escalaHoje[] = [
                    'nome' => $c['nome'],
                    'cargo' => $c['cargo'],
                    'turno' => $statusTurno,
                    'status' => 'Presente'
                ];
            }
        }

        // Manutenções preventivas próximas
        $stmtEqp = $pdoTenant->query("SELECT nome, setor, proxima_revisao, estado FROM equipamentos WHERE estado != 'Excelente' OR proxima_revisao >= '$hoje' ORDER BY proxima_revisao ASC LIMIT 5");
        $manutencoes = $stmtEqp->fetchAll(PDO::FETCH_ASSOC);

        // Capacidade e lotação atual do ginásio
        $stmtSetores = $pdoTenant->query("SELECT SUM(capacidade) as cap_total FROM setores WHERE status = 'ATIVO'");
        $resSetores = $stmtSetores->fetch(PDO::FETCH_ASSOC);
        $capacidadeTotal = $resSetores['cap_total'] ?? 120;
        if ($capacidadeTotal == 0) $capacidadeTotal = 120;

        $stmtFluxo = $pdoTenant->query("SELECT COUNT(*) as total FROM fluxo_presenca WHERE status = 'PRESENTE'");
        $resFluxo = $stmtFluxo->fetch(PDO::FETCH_ASSOC);
        $alunosPresentes = $resFluxo['total'] ?? 0;
        $ocupacaoPercent = round(($alunosPresentes / $capacidadeTotal) * 100);

        // Estatísticas do parque de máquinas
        $stmtTotalEqp = $pdoTenant->query("SELECT COUNT(*) as total, SUM(CASE WHEN estado = 'Excelente' THEN 1 ELSE 0 END) as excelentes, SUM(CASE WHEN estado != 'Excelente' THEN 1 ELSE 0 END) as manutencao FROM equipamentos");
        $resEqpStats = $stmtTotalEqp->fetch(PDO::FETCH_ASSOC);
        $totalEqp = $resEqpStats['total'] ?? 1;
        $excelentes = $resEqpStats['excelentes'] ?? $totalEqp;
        $manutencaoQtd = $resEqpStats['manutencao'] ?? 0;
        $parquePercent = round(($excelentes / max(1, $totalEqp)) * 100);

        echo json_encode([
            'sucesso' => true,
            'kpis' => [
                'equipe_presente' => count($escalaHoje),
                'ocupacao_percent' => $ocupacaoPercent,
                'alunos_presentes' => $alunosPresentes,
                'capacidade_total' => $capacidadeTotal,
                'parque_percent' => $parquePercent,
                'maquinas_operacionais' => $excelentes,
                'maquinas_manutencao' => $manutencaoQtd,
                'alertas_qtd' => count($manutencoes)
            ],
            'escala' => $escalaHoje,
            'manutencoes' => $manutencoes
        ]);
        exit;
    }

    // 2. LISTAR ESCALAS FIXAS
    if ($action === 'listar_escalas_mensais') {
        $stmt = $pdoTenant->query("SELECT em.*, c.nome as colaborador_nome FROM escalas_mensais em JOIN colaboradores c ON em.colaborador_id = c.id ORDER BY em.id DESC");
        echo json_encode(['sucesso' => true, 'dados' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // 3. SALVAR ESCALA PADRÃO RÁPIDA (Seg-Sex + Fim de Semana)
    if ($action === 'salvar_escala_mensal' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['escala_id_edit'] ?? '';
        $colabId = $_POST['colaborador_id'] ?? '';
        $mesAno = $_POST['mes_ano'] ?? date('Y-m');
        $segSex = $_POST['seg_sex'] ?? '06:00 - 14:00';
        $sabado = $_POST['sabado'] ?? 'Folga';
        $domingo = $_POST['domingo'] ?? 'Folga';

        if (empty($colabId)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Selecione um colaborador.']);
            exit;
        }

        if (!empty($id)) {
            $stmt = $pdoTenant->prepare("UPDATE escalas_mensais SET colaborador_id=?, mes_ano=?, padrao_seg_sex=?, padrao_sabado=?, padrao_domingo=? WHERE id=?");
            $stmt->execute([$colabId, $mesAno, $segSex, $sabado, $domingo, $id]);
        } else {
            $stmt = $pdoTenant->prepare("INSERT INTO escalas_mensais (colaborador_id, mes_ano, padrao_seg_sex, padrao_sabado, padrao_domingo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$colabId, $mesAno, $segSex, $sabado, $domingo]);
        }

        echo json_encode(['sucesso' => true, 'mensagem' => 'Escala padrão guardada com sucesso!']);
        exit;
    }

    // 4. EXCLUIR ESCALA (Demissão / Limpeza)
    if ($action === 'excluir_escala_mensal' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdoTenant->prepare("DELETE FROM escalas_mensais WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['sucesso' => true, 'mensagem' => 'Escala removida com sucesso!']);
            exit;
        }
        echo json_encode(['sucesso' => false, 'erro' => 'ID de escala inválido.']);
        exit;
    }

    // 5. CARREGAR CALENDÁRIO DETALHADO DO MÊS PARA UM COLABORADOR
    if ($action === 'carregar_calendario_colaborador') {
        if (ob_get_length()) ob_clean();
        $colabId = $_GET['colaborador_id'] ?? '';
        $mesAno = $_GET['mes_ano'] ?? date('Y-m');

        if (empty($colabId)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Colaborador não informado.']);
            exit;
        }

        $stmtColabInfo = $pdoTenant->prepare("SELECT turno FROM colaboradores WHERE id = ?");
        $stmtColabInfo->execute([$colabId]);
        $colabInfo = $stmtColabInfo->fetch(PDO::FETCH_ASSOC);
        $turnoPadraoColab = !empty($colabInfo['turno']) ? $colabInfo['turno'] : '06:00 - 14:00';

        $stmtPadrao = $pdoTenant->prepare("SELECT * FROM escalas_mensais WHERE colaborador_id = ? AND mes_ano = ?");
        $stmtPadrao->execute([$colabId, $mesAno]);
        $padrao = $stmtPadrao->fetch(PDO::FETCH_ASSOC) ?: [];

        $segSexPadrao = $padrao['padrao_seg_sex'] ?? ($padrao['padrao_segunda'] ?? $turnoPadraoColab);
        $sabPadrao = $padrao['padrao_sabado'] ?? 'Folga';
        $domPadrao = $padrao['padrao_domingo'] ?? 'Folga';

        $inicioMes = $mesAno . '-01';
        $fimMes = date('Y-m-t', strtotime($inicioMes));

        $stmtEx = $pdoTenant->prepare("SELECT data_especifica, novo_status, motivo FROM escala_excecoes_diarias WHERE colaborador_id = ? AND data_especifica BETWEEN ? AND ?");
        $stmtEx->execute([$colabId, $inicioMes, $fimMes]);
        $excecoesRaw = $stmtEx->fetchAll(PDO::FETCH_ASSOC);
        $excecoes = [];
        foreach ($excecoesRaw as $ex) {
            $excecoes[$ex['data_especifica']] = $ex;
        }

        $diasDoMes = [];
        $totalDias = date('t', strtotime($inicioMes));

        for ($d = 1; $d <= $totalDias; $d++) {
            $dataStr = sprintf('%s-%02d', $mesAno, $d);
            $diaSemana = date('N', strtotime($dataStr));

            if ($diaSemana >= 1 && $diaSemana <= 5) {
                $statusBase = $segSexPadrao;
            } elseif ($diaSemana == 6) {
                $statusBase = $sabPadrao;
            } else {
                $statusBase = $domPadrao;
            }

            $statusFinal = $statusBase;
            $motivo = '';
            $personalizado = false;

            if (isset($excecoes[$dataStr])) {
                $statusFinal = $excecoes[$dataStr]['novo_status'];
                $motivo = $excecoes[$dataStr]['motivo'];
                $personalizado = true;
            }

            $diasDoMes[] = [
                'data' => $dataStr,
                'dia_mes' => $d,
                'dia_semana' => $diaSemana,
                'status' => $statusFinal,
                'motivo' => $motivo,
                'personalizado' => $personalizado
            ];
        }

        echo json_encode(['sucesso' => true, 'dias' => $diasDoMes]);
        exit;
    }

    // 6. ALTERAR DIA ESPECÍFICO NO CALENDÁRIO (Feriado, Atestado, etc.)
    if ($action === 'salvar_excecao_dia' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (ob_get_length()) ob_clean();
        $colabId = $_POST['colaborador_id'] ?? '';
        $dataEspecifica = $_POST['data_especifica'] ?? '';
        $novoStatus = $_POST['novo_status'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        if (empty($colabId) || empty($dataEspecifica)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos.']);
            exit;
        }

        $stmt = $pdoTenant->prepare("
            INSERT INTO escala_excecoes_diarias (colaborador_id, data_especifica, novo_status, motivo) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE novo_status = VALUES(novo_status), motivo = VALUES(motivo)
        ");
        $stmt->execute([$colabId, $dataEspecifica, $novoStatus, $motivo]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'Dia atualizado com sucesso!']);
        exit;
    }

    // 6.1. APAGAR / RESETAR EXCEÇÃO DO DIA PARA O PADRÃO NORMAL
    if ($action === 'remover_excecao_dia' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (ob_get_length()) ob_clean();
        $colabId = $_POST['colaborador_id'] ?? '';
        $dataEspecifica = $_POST['data_especifica'] ?? '';

        if (empty($colabId) || empty($dataEspecifica)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos.']);
            exit;
        }

        $stmt = $pdoTenant->prepare("DELETE FROM escala_excecoes_diarias WHERE colaborador_id = ? AND data_especifica = ?");
        $stmt->execute([$colabId, $dataEspecifica]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'Escala restaurada para o padrão normal!']);
        exit;
    }

    // 7. MATRIZ GERAL DO MÊS (TODOS OS FUNCIONÁRIOS COM TURNO AUTOMÁTICO)
    if ($action === 'escala_geral_mes') {
        if (ob_get_length()) ob_clean();
        
        $mesAno = $_GET['mes_ano'] ?? date('Y-m');
        $inicioMes = $mesAno . '-01';
        $totalDias = (int)date('t', strtotime($inicioMes));

        $colabs = $pdoTenant->query("SELECT id, nome, turno FROM colaboradores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        $escalasPadrao = $pdoTenant->query("SELECT * FROM escalas_mensais WHERE mes_ano = '$mesAno'")->fetchAll(PDO::FETCH_ASSOC);
        $mapPadrao = [];
        foreach ($escalasPadrao as $ep) {
            $mapPadrao[$ep['colaborador_id']] = $ep;
        }

        $fimMes = $mesAno . '-' . $totalDias;
        $excecoes = $pdoTenant->query("SELECT colaborador_id, data_especifica, novo_status FROM escala_excecoes_diarias WHERE data_especifica BETWEEN '$inicioMes' AND '$fimMes'")->fetchAll(PDO::FETCH_ASSOC);
        $mapExcecoes = [];
        foreach ($excecoes as $ex) {
            $mapExcecoes[$ex['colaborador_id']][$ex['data_especifica']] = $ex['novo_status'];
        }

        $escalasMap = [];
        foreach ($colabs as $c) {
            $cid = $c['id'];
            $turnoColab = !empty($c['turno']) ? $c['turno'] : '06:00 - 14:00';
            
            $padrao = $mapPadrao[$cid] ?? [];
            $segSex = $padrao['padrao_seg_sex'] ?? ($padrao['padrao_segunda'] ?? $turnoColab);
            $sabado = $padrao['padrao_sabado'] ?? 'Folga';
            $domingo = $padrao['padrao_domingo'] ?? 'Folga';

            for ($d = 1; $d <= $totalDias; $d++) {
                $dataStr = sprintf('%s-%02d', $mesAno, $d);
                $diaSemana = date('N', strtotime($dataStr));

                if (isset($mapExcecoes[$cid][$dataStr])) {
                    $escalasMap[$cid][$dataStr] = $mapExcecoes[$cid][$dataStr];
                    continue;
                }

                if ($diaSemana >= 1 && $diaSemana <= 5) {
                    $escalasMap[$cid][$dataStr] = $segSex;
                } elseif ($diaSemana == 6) {
                    $escalasMap[$cid][$dataStr] = $sabado;
                } else {
                    $escalasMap[$cid][$dataStr] = $domingo;
                }
            }
        }

        echo json_encode([
            'sucesso' => true,
            'total_dias' => $totalDias,
            'colaboradores' => $colabs,
            'escalas_map' => $escalasMap
        ]);
        exit;
    }

    // 8. REGISTAR TROCA DE EMERGÊNCIA
    if ($action === 'troca_emergencia' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (ob_get_length()) ob_clean();
        $dataOcorrencia = $_POST['data_ocorrencia'] ?? '';
        $ausenteId = $_POST['colaborador_ausente_id'] ?? '';
        $substitutoId = $_POST['colaborador_substituto_id'] ?? '';
        $motivo = $_POST['motivo'] ?? 'Substituição pontual';
        $tipo = $_POST['tipo_ocorrencia'] ?? 'Emergência';

        if (empty($dataOcorrencia) || empty($ausenteId) || empty($substitutoId)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios.']);
            exit;
        }

        $stmt = $pdoTenant->prepare("INSERT INTO trocas_escala_emergencia (colaborador_original_id, colaborador_substituto_id, data_ocorrencia, tipo, motivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ausenteId, $substitutoId, $dataOcorrencia, $tipo, $motivo]);

        echo json_encode(['sucesso' => true, 'mensagem' => "Troca registada com sucesso para o dia " . date('d/m/Y', strtotime($dataOcorrencia)) . "!"]);
        exit;
    }

    if (ob_get_length()) ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Ação desconhecida.']);

} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}