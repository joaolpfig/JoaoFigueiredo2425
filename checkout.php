<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_utilizador'])) {
    header('Location: login.php');
    exit();
}

$idUtilizador = $_SESSION['id_utilizador'];
$produtosCarrinho = buscarItensCarrinho($idUtilizador);

// Garantir que carrinho não está vazio
if (empty($produtosCarrinho)) {
    header('Location: cart.php');
    exit();
}

// Calcular total
$total = 0;
foreach ($produtosCarrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlugVintage - Checkout</title>
  <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
  <link rel="stylesheet" href="./css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  
</head>

<body>
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
      <a href="#" id="search-icon">
        <img src="img/IMAGENS INDEX/pesquisa.png" alt="Pesquisa" class="icon-image">
      </a>

      <a href="cart.php" class="cart-container">
        <img src="img/IMAGENS INDEX/carrinho.png" alt="Carrinho" class="icon-image">
        <?php if (!empty($totalItensCarrinho)): ?>
          <span class="cart-counter"><?php echo $totalItensCarrinho; ?></span>
        <?php endif; ?>
      </a>

      <div class="profile-container">
        <a href="#" id="profile-icon">
          <img src="img/IMAGENS INDEX/profile.png" alt="Profile" class="icon-image">
        </a>
        <div class="profile-dropdown" id="profile-dropdown">
          <?php if (isset($_SESSION['nome_utilizador'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['nome_utilizador']); ?></p>
            <button id="logout-btn" class="logout-button">Logout</button>
          <?php else: ?>
            <a href="login.php">Sign In</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <!-- Conteúdo da página de checkout -->
  <div class="checkout-page">
  <h2>Revisão da Encomenda</h2>

  <ul class="checkout-itens">
    <?php foreach ($produtosCarrinho as $item): ?>
      <li class="produto-checkout">
      <img src="<?= htmlspecialchars($item['caminho_imagem']) ?>" alt="<?= htmlspecialchars($item['nome_produto']) ?>" class="produto-img">


        <div class="info">
          <strong><?= $item['nome_produto'] ?></strong><br>
          <?= $item['quantidade'] ?> x €<?= number_format($item['preco'], 2) ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <p class="total-checkout"><strong>Total:</strong> €<?= number_format($total, 2) ?></p>

  <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" class="paypal-form">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="sb-nkpzy41124484@business.example.com">
    <input type="hidden" name="item_name" value="Encomenda PlugVintage">
    <input type="hidden" name="amount" value="<?= number_format($total, 2, '.', '') ?>">
    <input type="hidden" name="currency_code" value="EUR">
    <input type="hidden" name="custom" value="<?= $idUtilizador ?>">
    <input type="hidden" name="return" value="http://localhost/JOAOFIGUEIREDO2425/paypal_process.php?sucesso=1">
    <input type="hidden" name="cancel_return" value="http://localhost/plugvintage/cart.php">
    
    <button type="submit" class="paypal-button">Pagar com PayPal</button>

   

  </form>
</div>
</body>
</html>
