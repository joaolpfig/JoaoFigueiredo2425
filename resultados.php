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
    // Buscar produtos por nome
    $produtos = buscarProdutosPorNome($liga, $query, $produtos_por_pagina, $offset);

    // Calcular total de produtos por nome
    $stmt_total = mysqli_prepare($liga, "
        SELECT COUNT(*) as total 
        FROM produtos 
        WHERE nome_produto LIKE ?
    ");
    $searchTerm = "%$query%";
    mysqli_stmt_bind_param($stmt_total, "s", $searchTerm);
    mysqli_stmt_execute($stmt_total);
    $total_result = mysqli_stmt_get_result($stmt_total);
    $total_produtos = mysqli_fetch_assoc($total_result)['total'];

    $total_paginas = ceil($total_produtos / $produtos_por_pagina);
} else {
    echo "Nenhuma marca ou termo de busca foi selecionado.";
}

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
                            <button id="logout-btn" class="logout-button">Logout</button>
                        <?php else: ?>
                            <a href="login.php">Sign In</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


      </div>
    </header>

    <div class="products">
      <?php if (empty($produtos)): ?>
        <p style="text-align: center; margin-top: 50px;">Nenhum produto encontrado.</p>
      <?php else: ?>
        <?php foreach ($produtos as $produto): ?>
          <div class="product" caminho_imagem_hover="<?php echo htmlspecialchars($produto['caminho_imagem_hover']); ?>">
            <a href="produto.php?id_produtos=<?php echo htmlspecialchars($produto['id_produtos']); ?>">
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

    <div class="pagination">
      <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <a href="?<?php echo !empty($marca_id) ? "marca=$marca_id" : "query=$query"; ?>&pagina=<?php echo $i; ?>" 
           class="<?php echo ($pagina_atual == $i) ? 'active' : ''; ?>">
          <?php echo $i; ?>
        </a>
      <?php endfor; ?>
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


<script>
// Seleção de elementos
const searchIcon = document.getElementById("search-icon");
const searchModal = document.getElementById("search-modal");
const closeModal = document.getElementById("close-modal");
const searchInput = document.getElementById("search-input");
const suggestionList = document.createElement("ul"); // Lista para sugestões
const searchButton = document.getElementById("search-button");
const productsLabel = document.createElement("p");

// Adiciona elementos dinâmicos ao modal
productsLabel.id = "products-label";
productsLabel.textContent = "Products";
productsLabel.style.display = "none";
suggestionList.id = "suggestion-list";
suggestionList.style.marginTop = "10px";
suggestionList.style.listStyle = "none";
suggestionList.style.maxHeight = "200px"; // Limita a altura
suggestionList.style.overflowY = "scroll"; // Adiciona scroll
searchInput.insertAdjacentElement("afterend", productsLabel);
productsLabel.insertAdjacentElement("afterend", suggestionList);

// Verifica se elementos críticos existem antes de adicionar eventos
if (searchIcon && searchModal && closeModal && searchInput) {
    // Mostrar o modal ao clicar no ícone de pesquisa
    searchIcon.addEventListener("click", (e) => {
        e.preventDefault();
        console.log("Ícone de pesquisa clicado!");
        searchModal.classList.add("active"); // Adiciona a classe active
    });

    // Fechar o modal ao clicar no botão de fechar
    closeModal.addEventListener("click", () => {
        searchModal.classList.remove("active"); // Remove a classe active
        console.log("Modal fechado!");
    });

    // Mostrar sugestões enquanto escreves
    searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim();

        if (query.length > 1) {
            fetch(`/JoaoFigueiredo2425/search.php?query=${encodeURIComponent(query)}`)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data); // Verificar a estrutura do objeto retornado
                    suggestionList.innerHTML = ""; // Limpa sugestões antigas

                    if (data.length > 0) {
                        productsLabel.style.display = "block"; // Mostra o rótulo "Produtos"
                        data.forEach((product) => {
                            const li = document.createElement("li");
                            li.style.display = "flex";
                            li.style.alignItems = "center";
                            li.style.marginBottom = "5px";

                            li.innerHTML = `
                                <img src="${product.caminho_imagem}" alt="${product.nome_produto}" style="width:50px;height:50px;margin-right:10px;">
                                <span>${product.nome_produto}</span>
                            `;
                            li.addEventListener("click", () => {
                                if (product.id_produtos) {
                                    window.location.href = `/JoaoFigueiredo2425/produto.php?id_produtos=${product.id_produtos}`;
                                } else {
                                    console.error("ID do produto não encontrado.");
                                }
                            });
                            suggestionList.appendChild(li);
                        });
                    } else {
                        productsLabel.style.display = "none"; // Esconde o rótulo
                    }
                })
                .catch((error) => console.error("Erro ao buscar produtos:", error));
        } else {
            suggestionList.innerHTML = ""; // Limpa sugestões
            productsLabel.style.display = "none";
        }
    });

    // Redirecionar para a página de resultados ao clicar em "Pesquisar"
    searchButton.addEventListener("click", () => {
        const query = searchInput.value.trim();
        console.log("Botão 'Pesquisar' clicado!"); // Verifica se o evento é acionado
        console.log("Termo de pesquisa:", query); // Mostra o termo de pesquisa no console
        if (query) {
            window.location.href = `/JoaoFigueiredo2425/resultados.php?query=${encodeURIComponent(query)}`;
        }
    });

    // Redirecionar para a página de resultados ao pressionar "Enter"
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            const query = searchInput.value.trim();
            if (query) {
                window.location.href = `/JoaoFigueiredo2425/resultados.php?query=${encodeURIComponent(query)}`;
            }
        }
    });
} else {
    console.error("Elementos necessários para o modal de pesquisa não foram encontrados.");
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