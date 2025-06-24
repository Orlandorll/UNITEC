<?php
require_once "config/database.php";
require_once "includes/functions.php";

// Buscar o último produto cadastrado com imagem
$sql = "SELECT p.id, p.nome, ip.caminho_imagem 
        FROM produtos p 
        JOIN imagens_produtos ip ON p.id = ip.produto_id 
        ORDER BY p.id DESC 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produto = $stmt->fetch();

echo "<h2>Teste de Caminhos de Imagem</h2>";
echo "<pre>";

if ($produto) {
    echo "Produto encontrado:\n";
    echo "ID: " . $produto['id'] . "\n";
    echo "Nome: " . $produto['nome'] . "\n";
    echo "Caminho no banco: " . $produto['caminho_imagem'] . "\n\n";
    
    // Testar caminhos
    $caminho_banco = $produto['caminho_imagem'];
    $caminho_fisico = __DIR__ . '/' . $caminho_banco;
    $caminho_url = get_imagem_produto($caminho_banco);
    $caminho_seguro = get_imagem_produto_segura($caminho_banco);
    
    echo "Caminhos gerados:\n";
    echo "1. Caminho físico: " . $caminho_fisico . "\n";
    echo "2. Caminho URL: " . $caminho_url . "\n";
    echo "3. Caminho seguro: " . $caminho_seguro . "\n\n";
    
    echo "Verificações:\n";
    echo "1. Arquivo existe fisicamente? " . (file_exists($caminho_fisico) ? "✓ Sim" : "✗ Não") . "\n";
    echo "2. Diretório existe? " . (is_dir(dirname($caminho_fisico)) ? "✓ Sim" : "✗ Não") . "\n";
    if (file_exists($caminho_fisico)) {
        echo "3. Tamanho do arquivo: " . round(filesize($caminho_fisico) / 1024, 2) . " KB\n";
        echo "4. Permissões do arquivo: " . substr(sprintf('%o', fileperms($caminho_fisico)), -4) . "\n";
    }
    
    echo "\nTeste de exibição:\n";
    echo "<img src='" . $caminho_url . "' style='max-width: 200px;' onerror='this.onerror=null; this.src=\"/UNITEC/assets/img/no-image.jpg\";'>\n";
    echo "<img src='" . $caminho_seguro . "' style='max-width: 200px;' onerror='this.onerror=null; this.src=\"/UNITEC/assets/img/no-image.jpg\";'>\n";
    
} else {
    echo "Nenhum produto com imagem encontrado.\n";
}

// Verificar diretório de uploads
$diretorio_uploads = __DIR__ . '/uploads/produtos';
echo "\nVerificação do diretório de uploads:\n";
echo "Caminho do diretório: " . $diretorio_uploads . "\n";
echo "Diretório existe? " . (is_dir($diretorio_uploads) ? "✓ Sim" : "✗ Não") . "\n";
if (is_dir($diretorio_uploads)) {
    echo "Permissões do diretório: " . substr(sprintf('%o', fileperms($diretorio_uploads)), -4) . "\n";
    echo "Conteúdo do diretório:\n";
    $arquivos = scandir($diretorio_uploads);
    foreach ($arquivos as $arquivo) {
        if ($arquivo != "." && $arquivo != "..") {
            echo "- " . $arquivo . " (" . round(filesize($diretorio_uploads . '/' . $arquivo) / 1024, 2) . " KB)\n";
        }
    }
}

echo "</pre>";
?> 