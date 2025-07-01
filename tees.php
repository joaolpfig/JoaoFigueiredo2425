<?php
session_start();
include("config.php");


$idUtilizador = $_SESSION['id_utilizador'] ?? null;
$totalItensCarrinho = contarItensCarrinho($idUtilizador);

// Configuração da paginação
$produtos_por_pagina = 12;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// Definir a categoria como 'Tees'
$categoria = 'Tees';

$tamanhosSelecionados = $_GET['tamanho'] ?? [];
$stock = $_GET['stock'] ?? '';
$ordenar = $_GET['ordenar'] ?? '';
$preco_max = $_GET['preco_max'] ?? 9999;

$tamanhosDisponiveis = obterTamanhosDisponiveis($liga, $categoria);


$produtos = listarProdutosPorCategoriaComFiltros($liga, $categoria, $pagina_atual, $produtos_por_pagina, $tamanhosSelecionados, $stock, $ordenar, $preco_max);

// Total de produtos na categoria T-shirts
$total_produtos = mysqli_fetch_assoc(mysqli_query($liga, "SELECT COUNT(*) as total FROM produtos WHERE categoria = '$categoria'"))['total'];
$total_paginas = ceil($total_produtos / $produtos_por_pagina);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlugVintage - T-shirts</title>
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




<!-- Filtros -->
<section class="filtros-section">
  <form method="GET" id="filtros-form">

    <!-- Tamanhos com múltipla seleção -->
    <div class="filtro-item dropdown">
        <label for="dropdown-tamanho-btn">Size</label>
<div class="dropdown-button" id="dropdown-tamanho-btn">Select</div>
      <div class="dropdown-list" id="dropdown-tamanho-list">
        <?php foreach ($tamanhosDisponiveis as $t): ?>

          <label>
            <input type="checkbox" name="tamanho[]" value="<?= htmlspecialchars($t) ?>"> <?= htmlspecialchars($t) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Availability -->
<div class="dropdown filtro-item">
  <label for="dropdown-stock-btn">Availability</label>
  <div class="dropdown-button" id="dropdown-stock-btn">All</div>
  <div class="dropdown-list" id="dropdown-stock-list">
    <label><input type="radio" name="stock" value=""> All</label>
    <label><input type="radio" name="stock" value="In Stock"> In stock</label>
    <label><input type="radio" name="stock" value="Sold Out"> Sold out</label>
  </div>
</div>

    <!-- Ordenar -->
  <div class="filtro-item dropdown">
    <label for="dropdown-ordenar-btn">Order by</label>
    <div class="dropdown-button" id="dropdown-ordenar-btn">Standard</div>
    <div class="dropdown-list" id="dropdown-ordenar-list">
      <label><input type="radio" name="ordenar" value=""> Standard</label>
      <label><input type="radio" name="ordenar" value="preco_asc"> Price (Cheapest)</label>
      <label><input type="radio" name="ordenar" value="preco_desc"> Price (More expensive)</label>
    </div>
  </div>

    
<div class="contador-wrapper">
  <p id="contador-resultados">Loading products...</p>
</div>

  </form>
</section>



<!-- Filtros ativos dinâmicos -->
<div class="filtros-ativos" id="filtros-ativos"></div>





    <!-- Product Listing -->
<div class="products">
    <?php foreach ($produtos as $produto): ?>
        <div class="product"
            caminho_imagem_hover="<?php echo htmlspecialchars($produto['caminho_imagem_hover']);?>">  
            
            <?php if ($produto['quantidade'] == 0): ?>
                <span class="sold-out-label-list">Sold Out</span>
            <?php endif; ?>

            <a href="produto.php?id_produtos=<?php echo htmlspecialchars($produto['id_produtos']); ?>">
                <img src="<?php echo htmlspecialchars($produto['caminho_imagem']);?>"
                alt="<?php echo htmlspecialchars($produto['nome_produto']);?>">
            </a>
            <div class="product-title"><?php echo htmlspecialchars($produto['nome_produto']);?></div>
            <div class="product-brand"><?php echo htmlspecialchars($produto['nome_marcas']);?></div>
            <div class="product-price"><?php echo number_format($produto['preco'], 2, ',', ' ') . ' €';?></div>
        </div>
    
    <?php endforeach; ?>
</div>

      <!-- Paginação -->
<div class="pagination">

    <!-- Seta para página anterior -->
    <?php if ($pagina_atual > 1): ?>
        <a href="?pagina=<?= $pagina_atual - 1 ?><?= isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>" class="arrow-btn">
            <img src="img/IMAGENS INDEX/angulo-esquerdo.png" alt="Anterior" class="pagination-arrow">
        </a>
    <?php endif; ?>

   <!-- Paginação -->
<div class="pagination">

    <!-- Seta para página anterior -->
    <?php if ($pagina_atual > 1): ?>
        <a href="?pagina=<?= $pagina_atual - 1 ?><?= isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>" class="arrow-btn">
            <img src="img/IMAGENS INDEX/angulo-esquerdo.png" alt="Anterior" class="pagination-arrow">
        </a>
    <?php endif; ?>

    <!-- Primeira página -->
    <?php if ($pagina_atual > 2): ?>
        <a href="?pagina=1<?= isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>">1</a>
        <?php if ($pagina_atual > 3): ?>
            <span class="dots">...</span>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Páginas vizinhas -->
    <?php for ($i = max(1, $pagina_atual - 1); $i <= min($total_paginas, $pagina_atual + 1); $i++): ?>
        <a href="?pagina=<?= $i ?><?= isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>"
           class="<?= $pagina_atual == $i ? 'active' : '' ?>">
           <?= $i ?>
        </a>
    <?php endfor; ?>

    <!-- Última página -->
    <?php if ($pagina_atual < $total_paginas - 1): ?>
        <?php if ($pagina_atual < $total_paginas - 2): ?>
            <span class="dots">...</span>
        <?php endif; ?>
        <a href="?pagina=<?= $total_paginas ?><?= isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>"><?= $total_paginas ?></a>
    <?php endif; ?>

    <!-- Seta para próxima página -->
    <?php if ($pagina_atual < $total_paginas): ?>
        <a href="?pagina=<?= $pagina_atual + 1 ?><?= isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>" class="arrow-btn">
            <img src="img/IMAGENS INDEX/angulo-direito.png" alt="Seguinte" class="pagination-arrow">
        </a>
    <?php endif; ?>

</div>
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







<!----------------Java Script Do AJAX---------------->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = window.location.origin + "/JoaoFigueiredo2425/";
  const filtros = {
    tamanho: "",
    stock: "",
    ordenar: ""
  };

  const filtrosAtivos = document.getElementById("filtros-ativos");

  const btnsDropdown = document.querySelectorAll(".dropdown-button");

  // Abrir dropdowns
  btnsDropdown.forEach(btn => {
    const container = btn.closest(".dropdown");
    btn.addEventListener("click", () => {
      document.querySelectorAll(".dropdown").forEach(d => {
        if (d !== container) d.classList.remove("open");
      });
      container.classList.toggle("open");
    });
  });

  document.addEventListener("click", e => {
    document.querySelectorAll(".dropdown").forEach(drop => {
      if (!drop.contains(e.target)) drop.classList.remove("open");
    });
  });

  function obterTamanhosSelecionados() {
    const checkboxes = document.querySelectorAll('#dropdown-tamanho-list input[type="checkbox"]:checked');
    return Array.from(checkboxes).map(cb => cb.value);
  }

  function atualizarTextoDropdowns() {
    const tamanhos = obterTamanhosSelecionados();
    document.getElementById("dropdown-tamanho-btn").textContent = tamanhos.length > 0
      ? `Sizes (${tamanhos.length})`
      : "Select";

    const radioStock = document.querySelector('#dropdown-stock-list input[type="radio"]:checked');
    document.getElementById("dropdown-stock-btn").textContent = radioStock
      ? radioStock.parentElement.textContent.trim()
      : "All";

    const radioOrdenar = document.querySelector('#dropdown-ordenar-list input[type="radio"]:checked');
    document.getElementById("dropdown-ordenar-btn").textContent = radioOrdenar
      ? radioOrdenar.parentElement.textContent.trim()
      : "Standard";
  }

  function ativarHover() {
    const items = document.querySelectorAll('.product');

    items.forEach(item => {
      const img = item.querySelector('img');
      const caminhoImagemOriginal = img.src;
      let caminhoImagemHover = item.getAttribute('caminho_imagem_hover');

      if (caminhoImagemHover && caminhoImagemHover.trim() !== "") {
        caminhoImagemHover = baseUrl + caminhoImagemHover;

        item.addEventListener('mouseover', () => {
          img.src = caminhoImagemHover;
        });

        item.addEventListener('mouseout', () => {
          img.src = caminhoImagemOriginal;
        });
      }
    });
  }

  function atualizarProdutos(pagina = 1) {
    filtros.tamanho = obterTamanhosSelecionados().join(",");

    const radioStock = document.querySelector('#dropdown-stock-list input[type="radio"]:checked');
    filtros.stock = radioStock ? radioStock.value : "";

    const radioOrdenar = document.querySelector('#dropdown-ordenar-list input[type="radio"]:checked');
    filtros.ordenar = radioOrdenar ? radioOrdenar.value : "";

    const query = new URLSearchParams({
  ...filtros,
  pagina: pagina,
  categoria: "Tees"
}).toString();


    // Atualiza a URL (sem recarregar)
    history.replaceState(null, "", "?" + query);

    fetch("listar_ajax.php?" + query)
      .then(res => res.text())
      .then(html => {
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = html;

        const novosProdutos = tempDiv.querySelector(".products");
        const novaPaginacao = tempDiv.querySelector(".pagination");
        const totalFiltrados = tempDiv.querySelector("#total-produtos-filtrados")?.getAttribute("data-total") || 0;

        document.querySelector(".products").innerHTML = novosProdutos ? novosProdutos.innerHTML : "";
        document.querySelector(".pagination").innerHTML = novaPaginacao ? novaPaginacao.innerHTML : "";
        document.getElementById("contador-resultados").innerHTML =
          `<strong>${totalFiltrados}</strong> product/s found`;

        mostrarFiltrosAtivos();
        ativarPaginacaoDinamica();
        ativarHover(); // ⚡ Chama o hover sempre após atualizar os produtos
      })
      .catch(err => console.error("Erro ao carregar produtos:", err));
  }

  function ativarPaginacaoDinamica() {
    document.querySelectorAll(".pagination a[data-pagina]").forEach(link => {
      link.addEventListener("click", e => {
        e.preventDefault();
        const novaPagina = link.getAttribute("data-pagina");
        atualizarProdutos(novaPagina);
      });
    });
  }

  function mostrarFiltrosAtivos() {
    filtrosAtivos.innerHTML = "";

    if (filtros.tamanho) {
      filtros.tamanho.split(",").forEach(t => {
        const tag = document.createElement("span");
        tag.className = "filtro-tag";
        tag.textContent = t;

        const x = document.createElement("span");
        x.className = "remove-filtro";
        x.textContent = " ×";
        x.onclick = () => {
          document.querySelector(`#dropdown-tamanho-list input[value="${t}"]`).checked = false;
          atualizarTextoDropdowns();
          atualizarProdutos();
        };

        tag.appendChild(x);
        filtrosAtivos.appendChild(tag);
      });
    }

    ["stock", "ordenar"].forEach(chave => {
      if (filtros[chave]) {
        const tag = document.createElement("span");
        tag.className = "filtro-tag";
        tag.textContent = filtros[chave];

        const x = document.createElement("span");
        x.className = "remove-filtro";
        x.textContent = " ×";
        x.onclick = () => {
          const radios = document.querySelectorAll(`#dropdown-${chave}-list input[type="radio"]`);
          radios.forEach(r => { if (r.checked) r.checked = false; });
          atualizarTextoDropdowns();
          atualizarProdutos();
        };

        tag.appendChild(x);
        filtrosAtivos.appendChild(tag);
      }
    });

    if (Object.values(filtros).some(v => v !== '')) {
      const clearBtn = document.createElement("span");
      clearBtn.className = "clear-all-btn";
      clearBtn.textContent = "Clear all";
      clearBtn.onclick = () => {
        filtros.tamanho = filtros.stock = filtros.ordenar = "";
        document.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(i => i.checked = false);
        atualizarTextoDropdowns();
        atualizarProdutos();
      };
      filtrosAtivos.appendChild(clearBtn);
    }
  }

  // Eventos iniciais
  document.querySelectorAll('#dropdown-stock-list input[type="radio"]').forEach(radio => {
    radio.addEventListener("change", () => {
      atualizarTextoDropdowns();
      atualizarProdutos();
    });
  });

  document.querySelectorAll('#dropdown-ordenar-list input[type="radio"]').forEach(radio => {
    radio.addEventListener("change", () => {
      atualizarTextoDropdowns();
      atualizarProdutos();
    });
  });

  document.querySelectorAll('#dropdown-tamanho-list input[type="checkbox"]').forEach(cb => {
    cb.addEventListener("change", () => {
      atualizarTextoDropdowns();
      atualizarProdutos();
    });
  });

  // Inicializar página
  atualizarTextoDropdowns();
  atualizarProdutos();
  ativarPaginacaoDinamica();
  ativarHover(); // ⚡ Ativa o hover no load inicial
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