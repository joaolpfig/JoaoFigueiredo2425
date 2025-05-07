<?php
session_start();
include ("config.php");

$idUtilizador = $_SESSION['id_utilizador'];
$totalItensCarrinho = contarItensCarrinho($idUtilizador);




$idUtilizador = $_SESSION['id_utilizador'];

// Obtém os dados do utilizador e histórico de compras
$usuario = getUserData($idUtilizador, $liga);
$compras = getUserOrders($idUtilizador, $liga);
?>








<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlugVintage</title>
  <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
  <link rel="stylesheet" href="./css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  
</head>
<body>
  <div class="listar-page">
    <!-- Header -->
    <header class="header">
    <a href="index.php" class="logo">
    <img src="img/IMAGENS PARA O ICON SITE/logoplug-removebg-preview.png" alt="PlugVintage Logo">
    </a>

    <nav class="navbar">
        <a href="index.php">HOME</a>
        <a href="listar.php">SHOP ALL</a>
        <a href="tees.php">TEES</a>
        <a href="bottoms.php">BOTTOMS</a>
        <a href="sweats+jackets.php">SWEATS + JACKETS</a>
        <a href="shoes.php">SHOES</a>
        <a href="accesories.php">ACCESORIES</a>
      </nav>

      <div class="icons">
      <!-- Ícone de pesquisa -->
  <a href="#" id="search-icon">
    <img src="img/IMAGENS INDEX/pesquisa.png" alt="Pesquisa" class="icon-image">
  </a>


  <!-- Ícone do carrinho -->
  <a href="cart.php" class="cart-container">
                <img src="img/IMAGENS INDEX/carrinho.png" alt="Carrinho" class="icon-image">
                <?php if ($totalItensCarrinho > 0): ?>
                    <span class="cart-counter"><?php echo $totalItensCarrinho; ?></span>
                <?php endif; ?>
            </a>



  <!-- Ícone de perfil -->
  <div class="profile-container">
                <a href="#" id="profile-icon">
                    <img src="img/IMAGENS INDEX/profile.png" alt="Profile" class="icon-image">
                </a>
                <div class="profile-container">
                    <div class="profile-dropdown" id="profile-dropdown">
                        <?php if (isset($_SESSION['nome_utilizador'])): ?>
                            <p>Hello, <?php echo htmlspecialchars($_SESSION['nome_utilizador']); ?></p>
                            <a href="myperfil.php" class="myperfil-btn">My perfil</a>
                            <button id="logout-btn" class="logout-button">Logout</button>
                        <?php else: ?>
                            <a href="login.php">Sign In</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


<!-- Modal de Pesquisa -->
<div id="search-modal">
    <div class="search-box">
        <button id="close-modal" class="close-btn">&times;</button>
        <input type="text" id="search-input" placeholder="Search products..." class="search-input">
        <button id="search-button" class="search-button">Search</button>
    </div>
</div>
    </header>



    <div class="profile-info">
            <h2>Account details</h2>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome_utilizador']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Tipo de Conta:</strong> <?php echo ($usuario['tipo_utilizador'] == 'admin') ? 'Administrador' : 'Usuário Normal'; ?></p>
        </div>

        <div class="profile-orders">
            <h2>Order history</h2>
            <?php if ($compras->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($compra = $compras->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($compra['nome_produto']); ?></td>
                                <td><?php echo number_format($compra['preco'], 2, ',', '.'); ?> €</td>
                                <td><?php echo $compra['quantidade']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($compra['data_adicionado'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't placed any orders yet.</p>
            <?php endif; ?>
        </div>
    </div>
