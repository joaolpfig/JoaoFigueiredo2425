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

    // Verificar stock
    $sqlStock = "SELECT quantidade FROM produtos WHERE id_produtos = ?";
    $stmtStock = mysqli_prepare($liga, $sqlStock);
    mysqli_stmt_bind_param($stmtStock, "i", $idProduto);
    mysqli_stmt_execute($stmtStock);
    $resStock = mysqli_stmt_get_result($stmtStock);
    $produto = mysqli_fetch_assoc($resStock);

    if (!$produto || $produto['quantidade'] == 0) {
        $_SESSION['error_message'] = "Produto esgotado.";
        header("Location: produto.php?id_produtos=" . $idProduto);
        exit;
    }

    // Verificar duplicado
    $sqlCheck = "SELECT COUNT(*) AS total FROM carrinho WHERE id_utilizador = ? AND id_produtos = ?";
    $stmtCheck = mysqli_prepare($liga, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ii", $idUtilizador, $idProduto);
    mysqli_stmt_execute($stmtCheck);
    $resCheck = mysqli_stmt_get_result($stmtCheck);
    $rowCheck = mysqli_fetch_assoc($resCheck);

    if ($rowCheck['total'] > 0) {
        $_SESSION['error_message'] = "Produto já adicionado ao carrinho.";
        header("Location: produto.php?id_produtos=" . $idProduto);
        exit;
    }

    // Inserir no carrinho
    $sqlInsert = "INSERT INTO carrinho (id_utilizador, id_produtos, quantidade, data_adicionado) VALUES (?, ?, 1, NOW())";
    $stmtInsert = mysqli_prepare($liga, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, "ii", $idUtilizador, $idProduto);
    mysqli_stmt_execute($stmtInsert);

    header("Location: cart.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
