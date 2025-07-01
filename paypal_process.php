<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_utilizador']) || !isset($_GET['success'])) {
    header("Location: index.php");
    exit();
}

$idUtilizador = $_SESSION['id_utilizador'];
$produtosCarrinho = buscarItensCarrinho($idUtilizador);

if (empty($produtosCarrinho)) {
    header('Location: cart.php');
    exit();
}

// Verifica se o PayPal devolveu os dados
$nome_destinatario = $_GET['on0'] ?? 'Cliente PayPal';
$morada = $_GET['os0'] ?? 'Morada PayPal';
$codigo_postal = $_GET['on1'] ?? '0000-000';
$cidade = $_GET['os1'] ?? 'Cidade PayPal';
$pais = $_GET['on2'] ?? 'Portugal';

// Calcular total
$total = 0;
foreach ($produtosCarrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

// Criar nova encomenda
$stmt = $liga->prepare("INSERT INTO encomendas 
(id_utilizador, nome_destinatario, morada, codigo_postal, cidade, pais, total, estado, data_encomenda) 
VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', NOW())");
$stmt->bind_param("isssssd", $idUtilizador, $nome_destinatario, $morada, $codigo_postal, $cidade, $pais, $total);

$stmt->execute();
$idEncomenda = $stmt->insert_id;

// Associar produtos
foreach ($produtosCarrinho as $item) {
    $stmt = $liga->prepare("INSERT INTO encomenda_produtos (id_encomenda, id_produtos, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $idEncomenda, $item['id_produtos'], $item['quantidade'], $item['preco']);
    $stmt->execute();

    $stmt = $liga->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id_produtos = ?");
    $stmt->bind_param("ii", $item['quantidade'], $item['id_produtos']);
    $stmt->execute();
}

// Limpar carrinho
$stmt = $liga->prepare("DELETE FROM carrinho WHERE id_utilizador = ?");
$stmt->bind_param("i", $idUtilizador);
$stmt->execute();

header("Location: success.php?id=$idEncomenda");
exit();
?>
