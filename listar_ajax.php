<?php
include("config.php");

// Recebe os filtros enviados por AJAX
$tamanhos = isset($_GET['tamanho']) ? explode(',', $_GET['tamanho']) : [];
$stock = $_GET['stock'] ?? '';
$ordenar = $_GET['ordenar'] ?? '';
$pesquisa = $_GET['pesquisa'] ?? '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$categoria = $_GET['categoria'] ?? null;
$limite = 12;
$preco_max = 9999;

// Decide qual função usar dependendo se há categoria ou não
if ($categoria) {
    // Estamos numa página de categoria
    $produtos = listarProdutosPorCategoriaComFiltros($liga, $categoria, $pagina, $limite, $tamanhos, $stock, $ordenar, $preco_max);
    $total = contarProdutosPorCategoriaComFiltros($liga, $categoria, $tamanhos, $stock, $preco_max);
} else {
    // Página geral (shop all)
    $produtos = listarProdutosComFiltros($liga, $pagina, $limite, $tamanhos, $stock, $ordenar, $preco_max);
    $total = contarProdutosComFiltros($liga, $tamanhos, $stock);
}

$total_paginas = ceil($total / $limite);

// Determina a faixa de páginas a ser exibida
$faixa_inicial = max(1, $pagina - 1);
$faixa_final = min($total_paginas, $pagina + 1);
?>

<div class="products">
  <?php foreach ($produtos as $produto): ?>
    <div class="product" caminho_imagem_hover="<?= htmlspecialchars($produto['caminho_imagem_hover']) ?>">
      <?php if ($produto['quantidade'] == 0): ?>
        <span class="sold-out-label-list">Sold Out</span>
      <?php endif; ?>
      <a href="produto.php?id_produtos=<?= htmlspecialchars($produto['id_produtos']) ?>">
        <img src="<?= htmlspecialchars($produto['caminho_imagem']) ?>" alt="<?= htmlspecialchars($produto['nome_produto']) ?>">
      </a>
      <div class="product-title"><?= htmlspecialchars($produto['nome_produto']) ?></div>
      <div class="product-brand"><?= htmlspecialchars($produto['nome_marcas']) ?></div>
      <div class="product-price"><?= number_format($produto['preco'], 2, ',', ' ') ?> €</div>
    </div>
  <?php endforeach; ?>
</div>

<div class="pagination">
  <!-- Seta para página anterior -->
  <?php if ($pagina > 1): ?>
    <a href="#" data-pagina="<?= $pagina - 1 ?>" class="arrow-btn">
      <img src="img/IMAGENS INDEX/angulo-esquerdo.png" alt="Anterior" class="pagination-arrow">
    </a>
  <?php endif; ?>

  <!-- Primeira página -->
  <?php if ($pagina > 2): ?>
    <a href="#" data-pagina="1">1</a>
    <?php if ($pagina > 3): ?>
      <span class="dots">...</span>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Páginas vizinhas -->
  <?php for ($i = $faixa_inicial; $i <= $faixa_final; $i++): ?>
    <a href="#" data-pagina="<?= $i ?>" class="<?= ($pagina == $i) ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>

  <!-- Última página -->
  <?php if ($pagina < $total_paginas - 1): ?>
    <?php if ($pagina < $total_paginas - 2): ?>
      <span class="dots">...</span>
    <?php endif; ?>
    <a href="#" data-pagina="<?= $total_paginas ?>"><?= $total_paginas ?></a>
  <?php endif; ?>

  <!-- Seta para próxima página -->
  <?php if ($pagina < $total_paginas): ?>
    <a href="#" data-pagina="<?= $pagina + 1 ?>" class="arrow-btn">
      <img src="img/IMAGENS INDEX/angulo-direito.png" alt="Seguinte" class="pagination-arrow">
    </a>
  <?php endif; ?>
</div>

<div id="total-produtos-filtrados" data-total="<?= $total ?>"></div>
