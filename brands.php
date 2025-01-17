<?php 
include("config.php");

// Obter a lista de marcas em ordem alfabética
$marcas_query = "SELECT id_marca, nome_marca, caminho_imagem_brand FROM marcas ORDER BY nome_marca ASC"; // Certifique-se que a tabela e os campos estão corretos
$resultado_marcas = mysqli_query($liga, $marcas_query);

if (!$resultado_marcas) {
    die('Erro ao obter marcas: ' . mysqli_error($liga));
}

$marcas = mysqli_fetch_all($resultado_marcas, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlugVintage - Marcas</title>
  <link rel="icon" href="img/IMAGENS PARA O ICON SITE/plugicon.png" type="image/png">
  <link rel="stylesheet" href="./css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
      <a href="index.php" class="logo">
        <img src="img/IMAGENS PARA O ICON SITE/logosite.png" alt="PlugVintage Logo">
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
        <a href="profile.php">
          <img src="img/IMAGENS INDEX/profile.png" alt="Profile" class="icon-image">
        </a>
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

    <div class="brands-header">
        <h1>BRANDS</h1>
    </div>
  <div class="listar-page"></div>
  


    <!-- Lista de Marcas -->
    <div class="products">
      <?php foreach ($marcas as $marca): ?>
        <div class="product">
          <a href="resultados.php?marca=<?php echo urlencode($marca['id_marca']); ?>">
    <img src="<?php echo htmlspecialchars($marca['caminho_imagem_brand']); ?>" 
         alt="<?php echo htmlspecialchars($marca['nome_marca']); ?>" 
         class="product-image">
</a>
<div class="product-title"><?php echo htmlspecialchars($marca['nome_marca']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  
</body>
</html>

  <script>
    

    if (searchIcon && searchModal && closeModal) {
      // Mostrar o modal ao clicar no ícone de pesquisa
      searchIcon.addEventListener("click", (e) => {
        e.preventDefault();
        searchModal.classList.add("active");
      });

      // Fechar o modal ao clicar no botão de fechar
      closeModal.addEventListener("click", () => {
        searchModal.classList.remove("active");
      });
    } else {
      console.error("Elementos necessários para o modal de pesquisa não foram encontrados.");
    }
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




</body>
</html>
