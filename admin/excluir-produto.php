<?php
session_start();
require_once "../config/database.php";

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Acesso não autorizado']);
    exit;
}

// ... resto do código ... 