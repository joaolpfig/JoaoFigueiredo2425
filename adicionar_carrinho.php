<?php
session_start();
include('config.php');

if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_produto"])) {
    $idUtilizador = $_SESSION['id_utilizador'];
    $idProduto = intval($_POST["id_produto"]);

    if ($idProduto <= 0) {
        $_SESSION['error_message'] = "ID do produto inválido.";
        header("Location: produto.php?id_produtos=" . $idProduto);
        exit;
    }

    $adicionado = adicionarAoCarrinho($idUtilizador, $idProduto, $liga);

    if ($adicionado) {
        header("Location: cart.php"); // Redireciona para o carrinho
        exit;
    } else {
        $_SESSION['error_message'] = "Produto já foi adicionado ou está esgotado.";
        header("Location: produto.php?id_produtos=" . $idProduto);
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
