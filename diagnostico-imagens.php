<?php
require_once "config/database.php";

// 1. Produtos sem imagem principal
echo "<h2>Produtos sem imagem principal</h2>";
$sql = "SELECT p.id, p.nome FROM produtos p
        LEFT JOIN imagens_produtos ip ON p.id = ip.produto_id AND ip.imagem_principal = 1
        WHERE ip.id IS NULL
        ORDER BY p.id DESC";
$stmt = $conn->query($sql);
$produtos_sem_imagem = $stmt->fetchAll();
if (empty($produtos_sem_imagem)) {
    echo "<p style='color:green'>Todos os produtos têm imagem principal cadastrada.</p>";
} else {
    echo "<ul style='color:red'>";
    foreach ($produtos_sem_imagem as $produto) {
        echo "<li>ID: {$produto['id']} - {$produto['nome']}</li>";
    }
    echo "</ul>";
}

// 2. Imagens cadastradas que não existem fisicamente

echo "<h2>Imagens cadastradas que não existem fisicamente</h2>";
$sql = "SELECT ip.id, ip.produto_id, ip.caminho_imagem, p.nome as produto_nome
        FROM imagens_produtos ip
        JOIN produtos p ON ip.produto_id = p.id";
$stmt = $conn->query($sql);
$imagens = $stmt->fetchAll();
$encontrou = false;
foreach ($imagens as $img) {
    $caminho_fisico = __DIR__ . '/' . $img['caminho_imagem'];
    if (!file_exists($caminho_fisico)) {
        if (!$encontrou) {
            echo "<ul style='color:orange'>";
            $encontrou = true;
        }
        echo "<li>Produto: {$img['produto_nome']} (ID: {$img['produto_id']}) - Caminho: {$img['caminho_imagem']}</li>";
    }
}
if ($encontrou) {
    echo "</ul>";
} else {
    echo "<p style='color:green'>Todas as imagens cadastradas existem fisicamente.</p>";
}

// 3. Imagens físicas não cadastradas no banco
$diretorio = __DIR__ . '/uploads/produtos';
$arquivos = is_dir($diretorio) ? scandir($diretorio) : [];
$imagens_diretorio = array_filter($arquivos, function($arquivo) {
    return $arquivo != "." && $arquivo != ".." && in_array(strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
});

$sql = "SELECT caminho_imagem FROM imagens_produtos";
$stmt = $conn->query($sql);
$imagens_banco = $stmt->fetchAll(PDO::FETCH_COLUMN);
$imagens_banco_nomes = array_map(function($caminho) {
    return basename($caminho);
}, $imagens_banco);
$imagens_nao_utilizadas = array_diff($imagens_diretorio, $imagens_banco_nomes);

echo "<h2>Imagens físicas não cadastradas no banco</h2>";
if (!empty($imagens_nao_utilizadas)) {
    echo "<ul style='color:blue'>";
    foreach ($imagens_nao_utilizadas as $img) {
        echo "<li>$img</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:green'>Todas as imagens físicas estão cadastradas no banco.</p>";
} 