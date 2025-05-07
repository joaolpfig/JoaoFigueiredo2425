<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_utilizador']) || !isset($_GET['sucesso'])) {
    header("Location: success.php?id=$idEncomenda");
    exit();
}

$idUtilizador = $_SESSION['id_utilizador'];

// Buscar itens do carrinho novamente
$produtosCarrinho = buscarItensCarrinho($idUtilizador);

if (empty($produtosCarrinho)) {
    header('Location: cart.php');
    exit();
}

// Calcular total
$total = 0;
foreach ($produtosCarrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

// Criar nova encomenda
$stmt = $liga->prepare("INSERT INTO encomendas (id_utilizador, total, estado, data_encomenda) VALUES (?, ?, 'pago', NOW())");
$stmt->bind_param("id", $idUtilizador, $total);
$stmt->execute();
$idEncomenda = $stmt->insert_id;

// Associar produtos à encomenda
foreach ($produtosCarrinho as $item) {
    $stmt = $liga->prepare("INSERT INTO encomenda_produtos (id_encomenda, id_produtos, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $idEncomenda, $item['id_produtos'], $item['quantidade'], $item['preco']);
    $stmt->execute();

    // Atualizar stock
    $stmt = $liga->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id_produtos = ?");
    $stmt->bind_param("ii", $item['quantidade'], $item['id_produtos']);
    $stmt->execute();
}

// Limpar carrinho
$stmt = $liga->prepare("DELETE FROM carrinho WHERE id_utilizador = ?");
$stmt->bind_param("i", $idUtilizador);
$stmt->execute();

// Redirecionar para página de sucesso
header("Location: success.php?id=$idEncomenda");
exit();
?>
