<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Verificar se foi fornecido um ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: gerenciar-hero.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Buscar informações do slide antes de excluir
    $stmt = $conn->prepare("SELECT imagem FROM hero_images WHERE id = ?");
    $stmt->execute([$id]);
    $slide = $stmt->fetch();

    if ($slide) {
        // Iniciar transação
        $conn->beginTransaction();

        // Excluir o slide do banco de dados
        $stmt = $conn->prepare("DELETE FROM hero_images WHERE id = ?");
        $stmt->execute([$id]);

        // Se a exclusão foi bem sucedida e existe uma imagem
        if ($stmt->rowCount() > 0 && $slide['imagem']) {
            // Excluir o arquivo de imagem
            $caminho_imagem = "../" . $slide['imagem'];
            if (file_exists($caminho_imagem)) {
                unlink($caminho_imagem);
            }
        }

        $conn->commit();
        $_SESSION['mensagem'] = "Slide excluído com sucesso!";
    } else {
        $_SESSION['erro'] = "Slide não encontrado.";
    }
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['erro'] = "Erro ao excluir slide: " . $e->getMessage();
}

// Redirecionar de volta para a página de gerenciamento
header('Location: gerenciar-hero.php');
exit; 