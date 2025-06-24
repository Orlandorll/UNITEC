<?php
require_once "config/database.php";

echo "<h1>Diagnóstico de Imagens - Seção Sobre</h1>";

// Verificar diretório de uploads
$upload_dir = "uploads/ceos/";
echo "<h2>Verificação do Diretório de Uploads</h2>";
if (file_exists($upload_dir)) {
    echo "<p style='color: green;'>✓ Diretório de uploads existe</p>";
    echo "<p>Permissões do diretório: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Diretório de uploads não existe</p>";
    echo "<p>Tentando criar diretório...</p>";
    if (mkdir($upload_dir, 0777, true)) {
        echo "<p style='color: green;'>✓ Diretório criado com sucesso</p>";
    } else {
        echo "<p style='color: red;'>✗ Falha ao criar diretório</p>";
    }
}

// Verificar conteúdo da tabela sobre
echo "<h2>Verificação do Banco de Dados</h2>";
try {
    $sql = "SELECT ceo1_imagem, ceo2_imagem FROM sobre LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sobre = $stmt->fetch();

    if ($sobre) {
        echo "<p style='color: green;'>✓ Registro encontrado na tabela sobre</p>";
        
        // Verificar imagem do CEO 1
        echo "<h3>Imagem do CEO 1</h3>";
        if (!empty($sobre['ceo1_imagem'])) {
            echo "<p>Caminho no banco: " . htmlspecialchars($sobre['ceo1_imagem']) . "</p>";
            if (file_exists($sobre['ceo1_imagem'])) {
                echo "<p style='color: green;'>✓ Arquivo existe</p>";
                echo "<p>Tamanho: " . filesize($sobre['ceo1_imagem']) . " bytes</p>";
                echo "<p>Tipo: " . mime_content_type($sobre['ceo1_imagem']) . "</p>";
                echo "<img src='" . htmlspecialchars($sobre['ceo1_imagem']) . "' style='max-width: 200px;'>";
            } else {
                echo "<p style='color: red;'>✗ Arquivo não encontrado</p>";
            }
        } else {
            echo "<p style='color: orange;'>! Nenhuma imagem definida</p>";
        }

        // Verificar imagem do CEO 2
        echo "<h3>Imagem do CEO 2</h3>";
        if (!empty($sobre['ceo2_imagem'])) {
            echo "<p>Caminho no banco: " . htmlspecialchars($sobre['ceo2_imagem']) . "</p>";
            if (file_exists($sobre['ceo2_imagem'])) {
                echo "<p style='color: green;'>✓ Arquivo existe</p>";
                echo "<p>Tamanho: " . filesize($sobre['ceo2_imagem']) . " bytes</p>";
                echo "<p>Tipo: " . mime_content_type($sobre['ceo2_imagem']) . "</p>";
                echo "<img src='" . htmlspecialchars($sobre['ceo2_imagem']) . "' style='max-width: 200px;'>";
            } else {
                echo "<p style='color: red;'>✗ Arquivo não encontrado</p>";
            }
        } else {
            echo "<p style='color: orange;'>! Nenhuma imagem definida</p>";
        }
    } else {
        echo "<p style='color: orange;'>! Nenhum registro encontrado na tabela sobre</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro ao consultar banco de dados: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Verificar permissões do servidor web
echo "<h2>Informações do Servidor</h2>";
echo "<p>Usuário do PHP: " . get_current_user() . "</p>";
echo "<p>Diretório atual: " . getcwd() . "</p>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";

// Verificar configurações do PHP
echo "<h2>Configurações do PHP</h2>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . "</p>";
?> 