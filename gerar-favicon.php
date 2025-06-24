<?php
// Criar diretório se não existir
if (!file_exists('assets/img')) {
    mkdir('assets/img', 0777, true);
}

// Gerar favicon.ico
function gerarFavicon() {
    // Criar uma imagem 32x32 (tamanho padrão do favicon)
    $imagem = imagecreatetruecolor(32, 32);
    
    // Definir cores
    $azul = imagecolorallocate($imagem, 0, 113, 227); // #0071e3 (cor da Unitec)
    $branco = imagecolorallocate($imagem, 255, 255, 255);
    
    // Preencher fundo
    imagefill($imagem, 0, 0, $azul);
    
    // Desenhar "U" estilizada
    $pontos = array(
        8, 8,    // Ponto superior esquerdo
        8, 24,   // Ponto inferior esquerdo
        16, 24,  // Ponto inferior direito
        16, 16,  // Ponto do meio
        24, 16,  // Ponto do meio direito
        24, 8    // Ponto superior direito
    );
    imagepolygon($imagem, $pontos, 6, $branco);
    
    // Salvar como ICO
    imagepng($imagem, 'favicon.ico');
    imagedestroy($imagem);
}

// Gerar o favicon
gerarFavicon();
echo "Favicon gerado com sucesso!\n";
?> 