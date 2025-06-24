<?php
/**
 * Funções utilitárias para o sistema
 */

/**
 * Retorna o caminho completo da imagem do produto
 * @param string|null $caminho_imagem Caminho da imagem no banco de dados
 * @return string Caminho completo da imagem ou caminho da imagem padrão
 */
function get_imagem_produto($caminho_imagem) {
    if (empty($caminho_imagem)) {
        return '/UNITEC/assets/img/no-image.jpg';
    }
    
    // Remove espaços em branco e normaliza barras
    $caminho_imagem = trim(str_replace('\\', '/', $caminho_imagem));
    
    // Se o caminho já começar com /UNITEC, retorna como está
    if (strpos($caminho_imagem, '/UNITEC') === 0) {
        return $caminho_imagem;
    }
    
    // Se o caminho começar com uploads/, adiciona /UNITEC/
    if (strpos($caminho_imagem, 'uploads/') === 0) {
        return '/UNITEC/' . $caminho_imagem;
    }
    
    // Se não começar com uploads/, assume que é um caminho relativo
    return '/UNITEC/uploads/produtos/' . $caminho_imagem;
}

/**
 * Verifica se uma imagem existe no servidor
 * @param string $caminho_imagem Caminho da imagem
 * @return bool True se a imagem existe, False caso contrário
 */
function imagem_existe($caminho_imagem) {
    if (empty($caminho_imagem)) {
        error_log("Caminho da imagem está vazio");
        return false;
    }
    
    // Remove espaços em branco e normaliza barras
    $caminho_imagem = trim(str_replace('\\', '/', $caminho_imagem));
    error_log("Caminho da imagem após normalização: " . $caminho_imagem);
    
    // Remove /UNITEC do início do caminho se existir
    $caminho_relativo = preg_replace('#^/UNITEC/#', '', $caminho_imagem);
    error_log("Caminho relativo após remover /UNITEC/: " . $caminho_relativo);
    
    // Se o caminho não começar com uploads/, adiciona uploads/produtos/
    if (strpos($caminho_relativo, 'uploads/') !== 0) {
        $caminho_relativo = 'uploads/produtos/' . $caminho_relativo;
        error_log("Caminho relativo após adicionar uploads/produtos/: " . $caminho_relativo);
    }
    
    // No XAMPP, o caminho físico é C:\xampp\htdocs\UNITEC\
    $caminho_fisico = 'C:/xampp/htdocs/UNITEC/' . $caminho_relativo;
    error_log("Caminho físico final: " . $caminho_fisico);
    
    // Verificar se o diretório existe
    $diretorio = dirname($caminho_fisico);
    if (!is_dir($diretorio)) {
        error_log("Diretório não existe: " . $diretorio);
        return false;
    }
    
    // Verificar se o arquivo existe
    $existe = file_exists($caminho_fisico);
    error_log("Arquivo existe: " . ($existe ? 'Sim' : 'Não'));
    
    return $existe;
}

/**
 * Retorna o caminho da imagem do produto, verificando se ela existe
 * @param string|null $caminho_imagem Caminho da imagem no banco de dados
 * @return string Caminho completo da imagem ou caminho da imagem padrão
 */
function get_imagem_produto_segura($caminho_imagem) {
    // Primeiro, normaliza o caminho para garantir que comece com /UNITEC/
    $caminho = get_imagem_produto($caminho_imagem);
    
    // Verifica se a imagem existe fisicamente
    if (imagem_existe($caminho)) {
        return $caminho;
    }
    
    // Se a imagem não existir, retorna a imagem padrão
    return '/UNITEC/assets/img/no-image.jpg';
}

/**
 * Verifica se um usuário é administrador
 * @param int $usuario_id ID do usuário
 * @return bool True se o usuário é administrador, False caso contrário
 */
function is_admin($usuario_id) {
    global $conn;
    
    $sql = "SELECT tipo FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    
    $usuario = $stmt->fetch();
    return $usuario && $usuario['tipo'] === 'admin';
}

/**
 * Busca todas as categorias ativas do banco de dados
 * @return array Array com as categorias
 */
function get_categorias_ativas() {
    global $conn;
    
    $sql = "SELECT * FROM categorias WHERE status = 1 ORDER BY nome";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
} 