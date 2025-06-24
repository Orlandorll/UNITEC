<?php
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico do Hero</h1>";

// Verificar conexão com o banco de dados
try {
    require_once "config/database.php";
    echo "<p style='color: green;'>✅ Conexão com o banco de dados estabelecida</p>";
    
    // Verificar se a tabela hero_images existe
    $sql = "SHOW TABLES LIKE 'hero_images'";
    $result = $conn->query($sql);
    if ($result->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela hero_images existe</p>";
        
        // Verificar conteúdo da tabela
        $sql = "SELECT * FROM hero_images";
        $stmt = $conn->query($sql);
        $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Slides cadastrados:</h2>";
        if (count($slides) > 0) {
            echo "<p style='color: green;'>✅ " . count($slides) . " slides encontrados</p>";
            foreach ($slides as $slide) {
                echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
                echo "<p><strong>Título:</strong> " . htmlspecialchars($slide['titulo']) . "</p>";
                echo "<p><strong>Subtítulo:</strong> " . htmlspecialchars($slide['subtitulo']) . "</p>";
                echo "<p><strong>Imagem:</strong> " . htmlspecialchars($slide['imagem']) . "</p>";
                echo "<p><strong>Status:</strong> " . ($slide['status'] ? 'Ativo' : 'Inativo') . "</p>";
                
                // Verificar se a imagem existe fisicamente
                if (file_exists($slide['imagem'])) {
                    echo "<p style='color: green;'>✅ Arquivo de imagem existe</p>";
                    echo "<img src='" . htmlspecialchars($slide['imagem']) . "' style='max-width: 300px;'><br>";
                } else {
                    echo "<p style='color: red;'>❌ Arquivo de imagem NÃO existe</p>";
                }
                echo "</div>";
            }
        } else {
            echo "<p style='color: red;'>❌ Nenhum slide encontrado na tabela</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Tabela hero_images não existe</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
}

// Verificar arquivos na pasta uploads/hero
echo "<h2>Arquivos na pasta uploads/hero:</h2>";
$hero_dir = 'uploads/hero';
if (is_dir($hero_dir)) {
    $files = scandir($hero_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<p>Arquivo: " . $hero_dir . '/' . $file . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Pasta uploads/hero não existe</p>";
}
?> 