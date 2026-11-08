<?php
session_start();
require_once __DIR__ . '/../../../config/database.php'; // Ajusta o caminho se necessário para o teu database.php

// Garante que só o super admin pode ver
if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'acesso_negado']);
    exit;
}

header('Content-Type: application/json');

try {
    $pdoMaster = Database::getMasterConnection();
    
    // Busca os logs ordenados do mais recente para o mais antigo
    $stmt = $pdoMaster->query("SELECT * FROM master_logs ORDER BY id DESC LIMIT 100");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sucesso' => true, 'dados' => $logs]);
    exit;
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    exit;
}