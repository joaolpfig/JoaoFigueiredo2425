<?php
session_start();
include("config.php");

if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: myperfil.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gerir_encomendas.php");
    exit();
}

$id_encomenda = intval($_GET['id']);
$detalhes = obterDetalhesEncomenda($id_encomenda);
if (!$detalhes) {
    die("Encomenda não encontrada.");
}

$currentPage = basename(__FILE__);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-user">
        <h2><?= ucfirst($_SESSION['tipo_utilizador']) ?> - <?= htmlspecialchars($_SESSION['nome_utilizador']) ?></h2>
    </div>
    <ul>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="gerir_encomendas.php">Back to the list</a></li>
    </ul>
</div>

<div class="main-content">
    <h1>Order #<?= $detalhes['id_encomenda'] ?> Details</h1>

    <div class="order-summary">
        <div><strong>User:</strong> <?= htmlspecialchars($detalhes['nome_utilizador']) ?> (<?= $detalhes['email'] ?>)</div>
        <div><strong>Date:</strong> <?= $detalhes['data_encomenda'] ?></div>
        <div><strong>Status:</strong> <?= ucfirst($detalhes['estado']) ?></div>
        <div><strong>Total:</strong> €<?= number_format($detalhes['total'], 2, ',', '.') ?></div>
    </div>

    <div class="shipping-info">
        <h3>Shipping Info</h3>
        <p><?= $detalhes['morada'] ?>, <?= $detalhes['cidade'] ?>, <?= $detalhes['codigo_postal'] ?>, <?= $detalhes['pais'] ?></p>
    </div>

    <h3>Products</h3>
    <table class="order-products">
        <thead>
            <tr>
                <th>IMAGE</th>
                <th>NAME</th>
                <th>SIZE</th>
                <th>COLOR</th>
                <th>QUANTITY</th>
                <th>PRICE</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalhes['produtos'] as $produto): ?>
            <tr>
<td><img src="<?= $produto['caminho_imagem'] ?>" alt="Product Image" class="order-product-image"></td>

                <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                <td><?= $produto['tamanho'] ?></td>
                <td><?= $produto['cor'] ?></td>
                <td><?= $produto['quantidade'] ?></td>
                <td>€<?= number_format($produto['preco_unitario'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    
</div>
</body>
</html>
