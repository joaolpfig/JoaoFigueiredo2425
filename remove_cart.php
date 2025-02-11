<?php
// Inclui o arquivo de configuração para a conexão com o banco de dados
include('config.php');
session_start();

// Verifica se o ID do carrinho foi enviado
if (isset($_POST['id_carrinho'])) {
    $idCarrinho = (int)$_POST['id_carrinho'];

    // Verifica se a função está definida no config e a conexão está ativa
    if (isset($liga)) {
        // Chama a função para remover o produto do carrinho
        $removido = removerProdutoCarrinho($idCarrinho, $liga);

        if ($removido) {
            echo "<script>alert('Produto removido do carrinho com sucesso.');</script>";
        } else {
            echo "<script>alert('Erro ao remover o produto do carrinho.');</script>";
        }
    } else {
        echo "<script>alert('Erro de conexão com o banco de dados.');</script>";
    }
} else {
    echo "<script>alert('ID do carrinho não encontrado.');</script>";
}

// Redireciona de volta ao carrinho
header("Location: cart.php");
exit;
?>
