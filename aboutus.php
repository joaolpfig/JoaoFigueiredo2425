<?php
session_start();
include('config.php');


$idUtilizador = $_SESSION['id_utilizador'] ?? null;
$totalItensCarrinho = contarItensCarrinho($idUtilizador);


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
            </nav>
    </header>


    <section class="features-section">
        <div class="feature">
            <img src="img/IMAGENS ABOUT US/1882190.png" alt="Fast Shipping" class="feature-icon">
            <h3>FAST SHIPPING</h3>
            <p>Shipping out every weekday</p>
        </div>
        <div class="feature">
            <img src="img/IMAGENS ABOUT US/8539194.png" alt="Competitive Prices" class="feature-icon">
            <h3>COMPETITIVE PRICES</h3>
            <p>Below market rate</p>
        </div>
        <div class="feature">
            <img src="img/IMAGENS ABOUT US/4181268.png" alt="Authenticity Guaranteed" class="feature-icon">
            <h3>AUTHENTICITY GUARANTEED</h3>
            <p>Or your money back</p>
        </div>
        <div class="feature">
            <img src="img/IMAGENS ABOUT US/126482.png" alt="200+ Happy Customers" class="feature-icon">
            <h3>200+ HAPPY CUSTOMERS</h3>
            <p>Shop with confidence</p>
        </div>
    </section>



    <section class="info-section">
        <h2 class="info-title">INFO.</h2>
        <div class="accordion">
            <div class="accordion-item">
                <button class="accordion-button">
                    <span>&#9432;</span> FREQUENTLY ASKED QUESTIONS.
                    <span class="icon">+</span>
                </button>
                <div class="accordion-content">
                    <p>Here you can find answers to our most common questions.</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-button">
                    <span>&#10226;</span> RETURNS POLICY.
                    <span class="icon">+</span>
                </button>
                <div class="accordion-content">
                    <p>Returns and Refunds are only accepted if the product is not as described by us such as
                        undisclosed defects..</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-button">
                    <span>&#9889;</span> ABOUT US.
                    <span class="icon">+</span>
                </button>
                <div class="accordion-content">
                    <p>
                        We are a group of friends in Portugal and at the request of many we created a website to buy
                        your clothes.

                        We have been selling clothes since 2023, mainly in the UK streetwear style, with the best
                        prices.

                        The photos and prices were taken from the website: https://bountybodega.com
                    </p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-button">
                    <span>&#9745;</span> PRIVACY POLICY.
                    <span class="icon">+</span>
                </button>
                <div class="accordion-content">
                    <p>Your data privacy is important to us. Read more about how we handle your information securely.
                    </p>
                </div>
            </div>
        </div>
    </section>


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








    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const accordionButtons = document.querySelectorAll(".accordion-button");

            accordionButtons.forEach(button => {
                button.addEventListener("click", () => {
                    const accordionItem = button.parentElement;

                    // Fecha todos os outros accordions
                    document.querySelectorAll(".accordion-item").forEach(item => {
                        if (item !== accordionItem) {
                            item.classList.remove("active");
                        }
                    });

                    // Alterna o estado ativo
                    accordionItem.classList.toggle("active");
                });
            });
        });

    </script>
    <?php include('footer.php'); ?>