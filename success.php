<?php
require_once 'config.php';

// Verifica se o ID da encomenda foi passado
$id_encomenda = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca os dados da encomenda
$dados = buscarDadosEncomenda($id_encomenda);
$produtos = buscarProdutosEncomenda($id_encomenda);

// Se a encomenda não existir
if (!$dados) {
    echo "<p>Encomenda não encontrada.</p>";
    exit;
}

$nome = $dados['nome_utilizador'];
$email = $dados['email'];
$total = $dados['total'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Compra Finalizada - PlugVintage</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- CONTAINER COM OS DETALHES -->
<div class="success-container">
  <h1 class="success-title">Obrigado pela sua encomenda!</h1>

  <div class="success-details">
    <p><strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
  </div>

  <div class="success-summary">
    <h2>Resumo da Compra</h2>
    <ul class="product-list">
      <?php foreach ($produtos as $produto): ?>
        <li>
          <?php echo htmlspecialchars($produto['nome_produto']); ?>
          (x<?php echo $produto['quantidade']; ?>) -
          <?php echo number_format($produto['preco_unitario'], 2, ',', '.'); ?> €
        </li>
      <?php endforeach; ?>
    </ul>
    <p class="total"><strong>Total:</strong> <?php echo number_format($total, 2, ',', '.'); ?> €</p>
  </div>
</div>

<!-- BOTÃO FORA DO CONTAINER -->
<div class="success-back-outer">
  <a href="index.php" class="back-button">← Back to homepage</a>
</div>

</body>
</html>
