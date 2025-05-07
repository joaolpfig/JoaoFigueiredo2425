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

if (!$produtos) {
    echo "<h1>Produto não encontrado.</h1>";
    exit;
}

// Verifica se o produto está esgotado
$produtoEsgotado = ($produtos['quantidade'] <= 0);

// Verifica se o produto já está no carrinho
$produtoNoCarrinho = false;
if (isset($_SESSION['id_utilizador'])) {
    $idUtilizador = $_SESSION['id_utilizador'];
    $sqlCarrinho = "SELECT COUNT(*) AS total FROM carrinho WHERE id_utilizador = ? AND id_produtos = ?";
    $stmtCarrinho = $liga->prepare($sqlCarrinho);
    $stmtCarrinho->bind_param("ii", $idUtilizador, $idProduto);
    $stmtCarrinho->execute();
    $resultCarrinho = $stmtCarrinho->get_result()->fetch_assoc();
    $produtoNoCarrinho = $resultCarrinho['total'] > 0;
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlugVintage</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/plugicon.png" type="image/png">
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
                    <?php foreach ($imagensProduto as $imagem): ?>
                        <div class="slide">
                            <img src="<?php echo htmlspecialchars($imagem); ?>" alt="Imagem do Produto">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="prev">&#10094;</button>
                <button class="next">&#10095;</button>
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
            const dots = document.querySelectorAll('.dot');
            const prevButton = document.querySelector('.prev');
            const nextButton = document.querySelector('.next');
            let currentIndex = 0;

            function showSlide(index) {
                if (index < 0) {
                    currentIndex = slides.length - 1;
                } else if (index >= slides.length) {
                    currentIndex = 0;
                } else {
                    currentIndex = index;
                }

                // Movendo o slider corretamente
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;

                // Atualiza os dots
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === currentIndex);
                });
            }

            // Botões de navegação
            prevButton.addEventListener('click', function () {
                showSlide(currentIndex - 1);
            });

            nextButton.addEventListener('click', function () {
                showSlide(currentIndex + 1);
            });

            // Eventos para os dots
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function () {
                    showSlide(index);
                });
            });

            // Iniciar com o primeiro slide ativo
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






    <!-- Popup do carrinho -->
    <div id="cart-popup" class="cart-popup">
        <div id="cart-items" class="cart-items"></div>
    </div>

</body>

</html>