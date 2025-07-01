<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_utilizador'])) {
    header('Location: login.php');
    exit();
}

$idUtilizador = $_SESSION['id_utilizador'];
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
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlugVintage - Checkout</title>
  <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
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

    <!-- Formulário para o PayPal -->
    <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" class="paypal-form">
      <h3>Informações de Entrega</h3>
      <input type="hidden" name="cmd" value="_xclick">
      <input type="hidden" name="business" value="sb-nkpzy41124484@business.example.com">
      <input type="hidden" name="item_name" value="Encomenda PlugVintage">
      <input type="hidden" name="amount" value="<?= number_format($total, 2, '.', '') ?>">
      <input type="hidden" name="currency_code" value="EUR">
      <input type="hidden" name="custom" value="<?= $idUtilizador ?>">
      <input type="hidden" name="return" value="http://localhost/JOAOFIGUEIREDO2425/paypal_process.php?success=1">
      <input type="hidden" name="cancel_return" value="http://localhost/plugvintage/cart.php">

      <input type="text" name="on0" placeholder="Nome completo" required>
      <input type="text" name="os0" placeholder="Morada completa" required>
      <input type="text" name="on1" placeholder="Código Postal" required>
      <input type="text" name="os1" placeholder="Cidade" required>
      <input type="text" name="on2" placeholder="País" required>

      <button type="submit" class="paypal-button">Pagar com PayPal</button>
    </form>
  </div>
</body>
</html>
