<?php
require_once "config/database.php";

echo "<h1>Diagnóstico de Imagens</h1>";

// Buscar slides do banco de dados
try {
    $sql = "SELECT * FROM hero_images WHERE status = 1 ORDER BY ordem ASC";
    $stmt = $conn->query($sql);
    $slides = $stmt->fetchAll();
    
    echo "<h2>Slides no Banco de Dados:</h2>";
    foreach ($slides as $slide) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<p><strong>ID:</strong> " . $slide['id'] . "</p>";
        echo "<p><strong>Título:</strong> " . $slide['titulo'] . "</p>";
        echo "<p><strong>Caminho da Imagem:</strong> " . $slide['imagem'] . "</p>";
        
        // Verificar se o arquivo existe
        if (file_exists($slide['imagem'])) {
            echo "<p style='color: green;'>✅ Arquivo existe fisicamente</p>";
            echo "<p><strong>Tamanho:</strong> " . filesize($slide['imagem']) . " bytes</p>";
            echo "<p><strong>Permissões:</strong> " . substr(sprintf('%o', fileperms($slide['imagem'])), -4) . "</p>";
            echo "<p><strong>URL Completa:</strong> " . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . $slide['imagem'] . "</p>";
            echo "<img src='" . $slide['imagem'] . "' style='max-width: 300px;'><br>";
        } else {
            echo "<p style='color: red;'>❌ Arquivo NÃO existe fisicamente</p>";
        }
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erro ao buscar slides: " . $e->getMessage() . "</p>";
}

// Verificar diretório de uploads
echo "<h2>Conteúdo do Diretório uploads/hero:</h2>";
$hero_dir = 'uploads/hero';
if (is_dir($hero_dir)) {
    $files = scandir($hero_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $full_path = $hero_dir . '/' . $file;
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<p><strong>Arquivo:</strong> " . $full_path . "</p>";
            echo "<p><strong>Tamanho:</strong> " . filesize($full_path) . " bytes</p>";
            echo "<p><strong>Permissões:</strong> " . substr(sprintf('%o', fileperms($full_path)), -4) . "</p>";
            echo "<p><strong>URL Completa:</strong> " . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . $full_path . "</p>";
            echo "<img src='" . $full_path . "' style='max-width: 300px;'><br>";
            echo "</div>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Diretório uploads/hero não existe</p>";
}

// Verificar permissões do diretório
echo "<h2>Permissões do Diretório:</h2>";
if (is_dir($hero_dir)) {
    echo "<p><strong>Permissões do diretório:</strong> " . substr(sprintf('%o', fileperms($hero_dir)), -4) . "</p>";
    echo "<p><strong>Caminho Absoluto:</strong> " . realpath($hero_dir) . "</p>";
    echo "<p><strong>URL Base:</strong> " . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . "/</p>";
}
?> 