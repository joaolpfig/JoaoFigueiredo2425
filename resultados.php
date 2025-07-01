<?php 
session_start();
include("config.php");

$idUtilizador = $_SESSION['id_utilizador'] ?? null;
$totalItensCarrinho = contarItensCarrinho($idUtilizador);

$produtos_por_pagina = 12;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $produtos_por_pagina;

$query = $_GET['query'] ?? ''; // Busca por texto
$marca_id = isset($_GET['marca']) ? (int)$_GET['marca'] : 0; // Busca por marca

$produtos = [];
$total_produtos = 0;
$total_paginas = 0;

if (!empty($marca_id)) {
    // Buscar produtos por marca
    $produtos = buscarProdutosPorMarca($liga, $marca_id, $produtos_por_pagina, $offset);

    // Calcular total de produtos por marca
    $stmt_total = mysqli_prepare($liga, "
        SELECT COUNT(DISTINCT p.id_produtos) as total 
        FROM produtos p
        LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
        WHERE pm.id_marcas = ?
    ");
    mysqli_stmt_bind_param($stmt_total, "i", $marca_id);
    mysqli_stmt_execute($stmt_total);
    $total_result = mysqli_stmt_get_result($stmt_total);
    $total_produtos = mysqli_fetch_assoc($total_result)['total'];

    $total_paginas = ceil($total_produtos / $produtos_por_pagina);

} elseif (!empty($query)) {
    // Buscar produtos por nome (com contagem)
    $resultadoBusca = buscarProdutosPorNomeComContagem($liga, $query, $produtos_por_pagina, $offset);
    $produtos = $resultadoBusca['produtos'];
    $total_produtos = $resultadoBusca['total'];
    $total_paginas = ceil($total_produtos / $produtos_por_pagina);
    
} else {
    echo "Nenhuma marca ou termo de busca foi selecionado.";
}

// Construção da query string para paginação
$params = [];
if (!empty($query)) $params[] = 'query=' . urlencode($query);
if (!empty($marca_id)) $params[] = 'marca=' . urlencode($marca_id);
$queryString = !empty($params) ? '&' . implode('&', $params) : '';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultados</title>
  <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
  <link rel="stylesheet" href="./css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="listar-page">
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
    <!-- Contador -->
<div class="contador-wrapper">
    <p id="contador-resultados">
        <strong><?= $total_produtos ?></strong> product/s found
    </p>
</div>

    <div class="products">
      <?php if (empty($produtos)): ?>
        <p style="text-align: center; margin-top: 50px;">No products found.</p>
      <?php else: ?>
        
        <?php foreach ($produtos as $produto): ?>
          <div class="product" caminho_imagem_hover="<?php echo htmlspecialchars($produto['caminho_imagem_hover']); ?>">
            <a href="produto.php?id_produtos=<?php echo htmlspecialchars($produto['id_produtos']); ?>">
                <?php if ($produto['quantidade'] == 0): ?>
      <div class="sold-out-label">SOLD OUT</div>
    <?php endif; ?>
              <img src="<?php echo htmlspecialchars($produto['caminho_imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
            </a>
            <div class="product-title"><?php echo htmlspecialchars($produto['nome_produto']); ?></div>
            <div class="product-brand">
              <?php echo htmlspecialchars($produto['nome_marcas']); ?>
            </div>
            <div class="product-price"><?php echo number_format($produto['preco'], 2, ',', ' ') . ' €'; ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

 





  
    
    <!-- Paginação -->
<div class="pagination">

    <!-- Seta para página anterior -->
    <?php if ($pagina_atual > 1): ?>
        <a href="?pagina=<?= $pagina_atual - 1 . $queryString ?>" class="arrow-btn">
            <img src="img/IMAGENS INDEX/angulo-esquerdo.png" alt="Anterior" class="pagination-arrow">
        </a>
    <?php endif; ?>

    <!-- Primeira página -->
    <?php if ($pagina_atual > 2): ?>
        <a href="?pagina=1<?= $queryString ?>">1</a>
        <?php if ($pagina_atual > 3): ?>
            <span class="dots">...</span>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Páginas vizinhas -->
    <?php for ($i = max(1, $pagina_atual - 1); $i <= min($total_paginas, $pagina_atual + 1); $i++): ?>
        <a href="?pagina=<?= $i . $queryString ?>"
           class="<?= $pagina_atual == $i ? 'active' : '' ?>">
           <?= $i ?>
        </a>
    <?php endfor; ?>

    <!-- Última página -->
    <?php if ($pagina_atual < $total_paginas - 1): ?>
        <?php if ($pagina_atual < $total_paginas - 2): ?>
            <span class="dots">...</span>
        <?php endif; ?>
        <a href="?pagina=<?= $total_paginas . $queryString ?>"><?= $total_paginas ?></a>
    <?php endif; ?>

    <!-- Seta para próxima página -->
    <?php if ($pagina_atual < $total_paginas): ?>
        <a href="?pagina=<?= $pagina_atual + 1 . $queryString ?>" class="arrow-btn">
            <img src="img/IMAGENS INDEX/angulo-direito.png" alt="Seguinte" class="pagination-arrow">
        </a>
    <?php endif; ?>

</div>

  <script>
    const items = document.querySelectorAll('.product');
    items.forEach(item => {
        const caminhoImagemOriginal = item.querySelector('img').src;
        const caminhoImagemAlternativa = item.getAttribute('caminho_imagem_hover');
        item.addEventListener('mouseover', () => {
            item.querySelector('img').src = caminhoImagemAlternativa;
        });
        item.addEventListener('mouseout', () => {
            item.querySelector('img').src = caminhoImagemOriginal;
        });
    });
  </script>



<!----------------Java Script Do Pesquisar---------------->
<script>
// Seleção de elementos
const searchIcon = document.getElementById("search-icon");
const searchModal = document.getElementById("search-modal");
const closeModal = document.getElementById("close-modal");
const searchInput = document.getElementById("search-input");
const suggestionList = document.createElement("ul");
const searchButton = document.getElementById("search-button");
const productsLabel = document.createElement("p");

// Estilo da lista
productsLabel.id = "products-label";
productsLabel.textContent = "Products";
productsLabel.style.display = "none";
suggestionList.id = "suggestion-list";
suggestionList.style.marginTop = "10px";
suggestionList.style.listStyle = "none";
suggestionList.style.maxHeight = "200px";
suggestionList.style.overflowY = "scroll";
searchInput.insertAdjacentElement("afterend", productsLabel);
productsLabel.insertAdjacentElement("afterend", suggestionList);

if (searchIcon && searchModal && closeModal && searchInput) {
    // Abrir modal
    searchIcon.addEventListener("click", (e) => {
        e.preventDefault();
        searchModal.classList.add("active");
    });

    // Fechar modal
    closeModal.addEventListener("click", () => {
        searchModal.classList.remove("active");
    });

    // Sugestões dinâmicas
    searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim();

        if (query.length > 1) {
            fetch(`/JoaoFigueiredo2425/search.php?query=${encodeURIComponent(query)}`)
                .then((response) => response.json())
                .then((data) => {
                    suggestionList.innerHTML = "";
                    if (data.length > 0) {
                        productsLabel.style.display = "block";
                        data.forEach((product) => {
                            const li = document.createElement("li");
                            li.style.display = "flex";
                            li.style.alignItems = "center";
                            li.style.marginBottom = "5px";

                            // Verifica quantidade
                            let soldOutLabel = "";
                            if (product.quantidade == 0) {
                                soldOutLabel = `<span style="
                                    background-color: red;
                                    color: white;
                                    font-weight: bold;
                                    font-size: 10px;
                                    padding: 2px 6px;
                                    margin-left: 8px;
                                    border-radius: 4px;
                                ">SOLD OUT</span>`;
                            }

                            li.innerHTML = `
                                <img src="${product.caminho_imagem}" alt="${product.nome_produto}" style="width:50px;height:50px;margin-right:10px;">
                                <span>${product.nome_produto}${soldOutLabel}</span>
                            `;

                            li.addEventListener("click", () => {
                                if (product.id_produtos) {
                                    window.location.href = `/JoaoFigueiredo2425/produto.php?id_produtos=${product.id_produtos}`;
                                }
                            });

                            suggestionList.appendChild(li);
                        });
                    } else {
                        productsLabel.style.display = "none";
                    }
                })
                .catch((error) => console.error("Erro ao buscar produtos:", error));
        } else {
            suggestionList.innerHTML = "";
            productsLabel.style.display = "none";
        }
    });

    // Botão "Search"
    searchButton.addEventListener("click", () => {
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = `/JoaoFigueiredo2425/resultados.php?query=${encodeURIComponent(query)}`;
        }
    });

    // Enter para pesquisar
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            const query = searchInput.value.trim();
            if (query) {
                window.location.href = `/JoaoFigueiredo2425/resultados.php?query=${encodeURIComponent(query)}`;
            }
        }
    });
} else {
    console.error("Elementos do modal de pesquisa não encontrados.");
}
</script>







<!----------------Java Script Do Login---------------->
<script>
        document.addEventListener("DOMContentLoaded", function () {
            const profileIcon = document.getElementById("profile-icon");
            const profileDropdown = document.getElementById("profile-dropdown");
            const logoutBtn = document.getElementById("logout-btn");

            // Alterna a visibilidade do dropdown ao clicar no ícone do perfil
            profileIcon.addEventListener("click", function (event) {
                event.preventDefault();
                profileDropdown.classList.toggle("show");
            });

            // Fecha o dropdown se clicar fora dele
            document.addEventListener("click", function (event) {
                if (!profileIcon.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.remove("show");
                }
            });

            // Aplica o estilo diretamente no JavaScript
            if (logoutBtn) {
                logoutBtn.style.backgroundColor = "red";
                logoutBtn.style.color = "white";
                logoutBtn.style.border = "none";
                logoutBtn.style.padding = "10px";
                logoutBtn.style.cursor = "pointer";
                logoutBtn.style.width = "100%";
                logoutBtn.style.borderRadius = "5px";
                logoutBtn.style.fontWeight = "bold";
                logoutBtn.style.textAlign = "center";

                logoutBtn.addEventListener("mouseover", function () {
                    logoutBtn.style.backgroundColor = "darkred";
                });

                logoutBtn.addEventListener("mouseout", function () {
                    logoutBtn.style.backgroundColor = "red";
                });

                // Logout ao clicar no botão
                logoutBtn.addEventListener("click", function () {
                    fetch("logout.php", { method: "POST" }) // Envia uma requisição para logout.php
                        .then(() => {
                            window.location.href = "login.php"; // Redireciona para a página de login
                        })
                        .catch(error => console.error("Erro ao fazer logout:", error));
                });
            }
        });

    </script>




    
<?php include('footer.php'); ?>
</body>
</html>