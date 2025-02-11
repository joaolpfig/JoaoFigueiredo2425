<?php
session_start();

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlugVintage</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
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
  <!-- Ícone de carrinho -->
  <a href="cart.php">
    <img src="img/IMAGENS INDEX/carrinho.png" alt="Carrinho" class="icon-image">
  </a>
  <!-- Ícone de perfil -->
  <div class="profile-container">
    <a href="#" id="profile-icon">
        <img src="img/IMAGENS INDEX/profile.png" alt="Profile" class="icon-image">
    </a>
    <div class="profile-dropdown" id="profile-dropdown">
        <?php if (isset($_SESSION['nome_utilizador'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['nome_utilizador']); ?></p>
            <button id="logout-btn">Logout</button>
        <?php else: ?>
            <a href="login.php">Sign in</a>
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
        <div class="hero">
            <img src="img/IMAGENS INDEX/IMG_9537 (1).jpg" alt="Hero Image" class="hero-image">
            <div class="overlay">
                <a href="listar.php" class="shop-all-btn">SHOP ALL</a>
            </div>
        </div>

        <div class="new-hero">
    <img src="img/IMAGENS INDEX/IMG_9781.jpg" alt="New Hero Image" class="hero-image">
    <div class="overlay">
        <a href="brands.php" class="brands-btn">BRANDS</a>
    </div>
</div>

<div class="new-hero2">
    <img src="img/IMAGENS INDEX/IMG_0088.jpg" alt="New Hero Image" class="hero2-image">
    <div class="overlay2">
        <a href="aboutus.php" class="aboutus-btn">ABOUT US</a>
    </div>
</div>





    
    


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





<script>
    document.addEventListener("DOMContentLoaded", function () {
    const accordionButtons = document.querySelectorAll(".accordion-button");

    accordionButtons.forEach(button => {
        button.addEventListener("click", () => {
            const content = button.nextElementSibling;

            // Fechar outros accordions
            document.querySelectorAll(".accordion-content").forEach(item => {
                if (item !== content) {
                    item.style.display = "none";
                }
            });

            // Alternar visibilidade
            content.style.display = content.style.display === "block" ? "none" : "block";
        });
    });
});

</script>



<script>
document.addEventListener("DOMContentLoaded", function () {
    const profileIcon = document.getElementById("profile-icon");
    const profileDropdown = document.getElementById("profile-dropdown");
    const logoutBtn = document.getElementById("logout-btn");

    // Alterna o dropdown ao clicar no ícone do perfil
    profileIcon.addEventListener("click", function (event) {
        event.preventDefault();
        profileDropdown.classList.toggle("show");
    });

    // Fecha o dropdown se clicar fora
    document.addEventListener("click", function (event) {
        if (!profileIcon.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove("show");
        }
    });

    // Logout
    logoutBtn.addEventListener("click", function () {
        window.location.href = "logout.php";
    });
});

</script>

<?php include('footer.php'); ?>
</body>
</html>