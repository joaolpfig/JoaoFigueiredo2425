<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['id_carrinho'])) {
    header("Location: cart.php");
    exit;
}

$idCarrinho = intval($_POST['id_carrinho']);

$removido = removerProdutoCarrinho($idCarrinho);

if ($removido) {
    $_SESSION['success_message'] = "Produto removido com sucesso.";
} else {
    $_SESSION['error_message'] = "Erro ao remover o produto.";
}

header("Location: cart.php");
exit;

?>
