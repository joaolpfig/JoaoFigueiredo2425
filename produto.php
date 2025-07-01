<?php
session_start();
include('config.php');

$idProduto = isset($_GET['id_produtos']) ? (int) $_GET['id_produtos'] : 0;

if ($idProduto <= 0) {
    echo "<h1>ID do produto inválido.</h1>";
    exit;
}

$idUtilizador = $_SESSION['id_utilizador'] ?? null;

// Busca os detalhes do produto e imagens
$produtos = buscarProdutosPorId($idProduto);
$imagensProduto = buscarImagensProduto($idProduto);
$totalItensCarrinho = contarItensCarrinho($idUtilizador);
// Busca as imagens secundárias (slider)
$secondaryImages = buscarImagensProdutoCompleto($idProduto);


if (!$produtos) {
    echo "<h1>Produto não encontrado.</h1>";
    exit;
}

// Verifica se o produto está esgotado
$produtoEsgotado = ($produtos['quantidade'] <= 0);

// Verificar se o produto já está no carrinho (MySQLi)
$produtoNoCarrinho = false;
$sqlCarrinho = "SELECT COUNT(*) AS total FROM carrinho WHERE id_utilizador = ? AND id_produtos = ?";
$stmtCarrinho = mysqli_prepare($liga, $sqlCarrinho);
mysqli_stmt_bind_param($stmtCarrinho, "ii", $idUtilizador, $idProduto);
mysqli_stmt_execute($stmtCarrinho);
$resCarrinho = mysqli_stmt_get_result($stmtCarrinho);
$rowCarrinho = mysqli_fetch_assoc($resCarrinho);
$produtoNoCarrinho = $rowCarrinho['total'] > 0;

?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlugVintage</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css"> <!-- Incluindo o CSS externo -->
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

            <!-- Modal de Pesquisa -->
            <div id="search-modal">
                <div class="search-box">
                    <button id="close-modal" class="close-btn">&times;</button>
                    <input type="text" id="search-input" placeholder="Search products..." class="search-input">
                    <button id="search-button" class="search-button">Search</button>
                </div>
            </div>
    </header>

    <!-- Página do Produto -->
    <div class="product-page">
        <div class="produto-layout">
            <!-- Slider de Imagens -->
            <div class="slider-container">
    <?php if ($produtoEsgotado): ?>
        <div class="sold-out-label">ESGOTADO</div>
    <?php endif; ?>

    <div class="slider">
        <?php if (!empty($secondaryImages)): ?>
            <?php foreach ($secondaryImages as $img): 
                if (str_contains($img['imagens'], 'img/') || str_contains($img['imagens'], 'img\\')) {
                    $caminhoImagem = str_replace('\\', '/', $img['imagens']);
                } else {
                    $caminhoImagem = 'img/imagens_produtos/' . $img['imagens'];
                }
            ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($caminhoImagem) ?>" alt="Product Image">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No images available for slider.</p>
        <?php endif; ?>
    </div>

    <div class="slider-dots"></div>

    <button class="prev" aria-label="Slide anterior">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
            <path d="M15.41 16.58L10.83 12l4.58-4.59L14 6l-6 6 6 6z"/>
        </svg>
    </button>

    <button class="next" aria-label="Próximo slide">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
            <path d="M8.59 16.58L13.17 12 8.59 7.41 10 6l6 6-6 6z"/>
        </svg>
    </button>
</div>






            <!-- Informações do Produto -->
            <div class="descricao-container">
                <h1><?php echo htmlspecialchars($produtos['nome_produto']); ?></h1>
                <div class="Categorie">Categorie: <?php echo htmlspecialchars($produtos['categorie']); ?></div>
                <div class="Brand">Brand: <?php echo htmlspecialchars($produtos['brand']); ?></div>
                <div class="Preco">Price: <?php echo number_format($produtos['preco'], 2, ',', '.'); ?> €</div>
                <div class="Size">Size: <?php echo htmlspecialchars($produtos['size']); ?></div>
                <div class="Color">Color: <?php echo htmlspecialchars($produtos['color']); ?></div>

                <!-- Botão de adicionar ao carrinho -->
                <form action="adicionar_carrinho.php" method="POST">
                    <input type="hidden" name="id_produto" value="<?php echo $idProduto; ?>">

                    <?php
                    // Verifica se o produto já está no carrinho do utilizador
                    $jaNoCarrinho = false;

                    if (isset($_SESSION['id_utilizador'])) {
                        $idUtilizador = $_SESSION['id_utilizador'];

                        // Busca os itens do carrinho do usuário
                        $produtosCarrinho = buscarItensCarrinho($idUtilizador);

                        // Percorre os produtos no carrinho para verificar se o produto já foi adicionado
                        foreach ($produtosCarrinho as $produto) {
                            if ($produto['id_produtos'] == $idProduto) {
                                $jaNoCarrinho = true;
                                break;
                            }
                        }
                    }
                    ?>

                    <?php if ($produtos['quantidade'] == 0): ?>
                        <!-- Produto Esgotado -->
                        <button type="button" class="add-to-cart sold-out" disabled>Sold out</button>
                    <?php elseif ($jaNoCarrinho): ?>
                        <!-- Produto já adicionado -->
                        <button type="button" class="add-to-cart added" disabled>Added to cart</button>
                    <?php else: ?>
                        <!-- Produto Disponível para Adicionar -->
                        <button type="submit" class="add-to-cart">Add to cart  </button>
                    <?php endif; ?>
                </form>

            </div>
        </div>
    </div>




    <!-- JavaScript do slider da página -->
   <script>
document.addEventListener("DOMContentLoaded", function () {
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const dotsContainer = document.querySelector('.slider-dots');
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');
    let currentIndex = 0;

    // Cria os dots dinamicamente
    slides.forEach((_, index) => {
        const dot = document.createElement('span');
        dot.classList.add('dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => showSlide(index));
        dotsContainer.appendChild(dot);
    });
    const dots = document.querySelectorAll('.dot');

    function showSlide(index) {
        currentIndex = (index + slides.length) % slides.length;
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
    }

    prevButton.addEventListener('click', () => showSlide(currentIndex - 1));
    nextButton.addEventListener('click', () => showSlide(currentIndex + 1));

    // Esconder botões e dots se só houver uma imagem
    if (slides.length <= 1) {
        prevButton.style.display = "none";
        nextButton.style.display = "none";
        dotsContainer.style.display = "none";
    }

    showSlide(currentIndex);
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