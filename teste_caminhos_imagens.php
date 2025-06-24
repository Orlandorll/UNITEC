<?php
require_once "config/database.php";

echo "<h1>Teste de Caminhos de Imagens</h1>";

// Verificar diretório de uploads
$upload_dir = "uploads/ceos/";
echo "<h2>Diretório de Uploads</h2>";
echo "<p>Caminho absoluto: " . realpath($upload_dir) . "</p>";
echo "<p>Caminho relativo: " . $upload_dir . "</p>";

// Verificar permissões
if (file_exists($upload_dir)) {
    echo "<p style='color: green;'>✓ Diretório existe</p>";
    echo "<p>Permissões: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Diretório não existe</p>";
}

// Listar arquivos no diretório
echo "<h2>Arquivos no Diretório</h2>";
if (file_exists($upload_dir)) {
    $files = scandir($upload_dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo "<li>" . $file . " - " . filesize($upload_dir . $file) . " bytes</li>";
        }
    }
    echo "</ul>";
}

// Verificar imagens no banco de dados
echo "<h2>Imagens no Banco de Dados</h2>";
try {
    $sql = "SELECT ceo1_imagem, ceo2_imagem FROM sobre LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sobre = $stmt->fetch();

    if ($sobre) {
        echo "<h3>CEO 1</h3>";
        echo "<p>Caminho no banco: " . htmlspecialchars($sobre['ceo1_imagem']) . "</p>";
        echo "<p>Caminho completo: " . realpath($sobre['ceo1_imagem']) . "</p>";
        if (file_exists($sobre['ceo1_imagem'])) {
            echo "<p style='color: green;'>✓ Arquivo existe</p>";
            echo "<img src='" . htmlspecialchars($sobre['ceo1_imagem']) . "' style='max-width: 200px;'>";
        } else {
            echo "<p style='color: red;'>✗ Arquivo não encontrado</p>";
        }

        echo "<h3>CEO 2</h3>";
        echo "<p>Caminho no banco: " . htmlspecialchars($sobre['ceo2_imagem']) . "</p>";
        echo "<p>Caminho completo: " . realpath($sobre['ceo2_imagem']) . "</p>";
        if (file_exists($sobre['ceo2_imagem'])) {
            echo "<p style='color: green;'>✓ Arquivo existe</p>";
            echo "<img src='" . htmlspecialchars($sobre['ceo2_imagem']) . "' style='max-width: 200px;'>";
        } else {
            echo "<p style='color: red;'>✗ Arquivo não encontrado</p>";
        }
    } else {
        echo "<p style='color: orange;'>! Nenhum registro encontrado na tabela sobre</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro ao consultar banco de dados: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Verificar configurações do PHP
echo "<h2>Configurações do PHP</h2>";
echo "<p>upload_tmp_dir: " . ini_get('upload_tmp_dir') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . "</p>";

// Verificar permissões do usuário
echo "<h2>Permissões do Usuário</h2>";
echo "<p>Usuário atual: " . get_current_user() . "</p>";
echo "<p>Diretório atual: " . getcwd() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
?> 