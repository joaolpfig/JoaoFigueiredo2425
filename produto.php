<?php
// Inclui o ficheiro de configuração para conexão com a base de dados
include('config.php');
session_start();

// Obtém o ID do produto da URL
$idProduto = isset($_GET['id_produtos']) ? (int) $_GET['id_produtos'] : 0;

// Verifica se o ID é válido
if ($idProduto <= 0) {
    echo "<h1>ID do produto inválido.</h1>";
    exit;
}

// Busca os detalhes do produto e suas imagens
$produtos = buscarProdutosPorId($idProduto);
$imagensProduto = buscarImagensProduto($idProduto);

if (!$produtos) {
    echo "<h1>Produto não encontrado.</h1>";
    exit;
}

// Verifica se o produto está no carrinho
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

// Verifica se o botão "Adicionar ao Carrinho" foi clicado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['id_utilizador'])) {
        header("Location: login.php");
        exit();
    }

    $idUtilizador = $_SESSION['id_utilizador'];

    // Adiciona o produto ao carrinho e atualiza o estoque
    $adicionado = adicionarAoCarrinho($idUtilizador, $idProduto, $liga);

    if ($adicionado) {
        echo "<script>alert('Produto adicionado ao carrinho!');</script>";
        header("Location: cart.php");
        exit();
    } else {
        echo "<script>alert('Produto esgotado ou não disponível.');</script>";
    }
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
  <!-- Ícone de carrinho -->
  <a href="cart.php">
    <img src="img/IMAGENS INDEX/carrinho.png" alt="Carrinho" class="icon-image">
  </a>
  <!-- Ícone de perfil -->
  <a href="login.php">
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

    <!-- Product Page -->
  <div class="product-page">
      <div class="produto-layout">
          <!-- Slider de imagens -->
          <div class="slider-container">
              <div class="slider">
                  <div class="list">
                      <?php foreach ($imagensProduto as $imagem): ?>
                          <div class="item">
                              <img src="<?php echo htmlspecialchars($imagem); ?>" alt="Imagem do Produto">
                          </div>
                      <?php endforeach; ?>
                  </div>
              </div>
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
              <form method="POST" action="">
    <input type="hidden" name="id_produto" value="<?php echo $idProduto; ?>">

    <?php if ($produtoNoCarrinho): ?>
        <!-- Produto já está no carrinho -->
        <button type="button" class="add-to-cart" disabled>
            Adicionado ao Carrinho
        </button>
    <?php else: ?>
        <!-- Produto ainda não está no carrinho -->
        <button type="submit" class="add-to-cart">
            Adicionar ao Carrinho
        </button>
    <?php endif; ?>
</form>
          </div>
      </div>
  </div>



    <!-- JavaScript no final da página -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const slider = document.querySelector('.slider .list');
            const dots = document.querySelectorAll('.dots li');
            const prevButton = document.getElementById('prev');
            const nextButton = document.getElementById('next');
            let currentIndex = 0;  // Índice da imagem atualmente visível

            function goToSlide(index) {
                // Impede o índice de ser menor que 0 ou maior que o número total de imagens
                if (index < 0) {
                    currentIndex = dots.length - 1;  // Vai para a última imagem
                } else if (index >= dots.length) {
                    currentIndex = 0;  // Vai para a primeira imagem
                } else {
                    currentIndex = index;
                }

                // Altera a posição do slider para mostrar a imagem certa
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;

                // Atualiza os pontos (dots)
                dots.forEach((dot, i) => {
                    dot.classList.remove('active');
                    if (i === currentIndex) {
                        dot.classList.add('active');
                    }
                });
            }

            // Funcionalidade para o botão "próximo"
            nextButton.addEventListener('click', function () {
                goToSlide(currentIndex + 1);
            });

            // Funcionalidade para o botão "anterior"
            prevButton.addEventListener('click', function () {
                goToSlide(currentIndex - 1);
            });

            // Funcionalidade para clicar nos dots
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function () {
                    goToSlide(index);
                });
            });

            // Inicializa o slider (a primeira imagem)
            goToSlide(currentIndex);
        });
    </script>


<script>
  document.addEventListener("DOMContentLoaded", function() {
    const addToCartButton = document.querySelector(".add-to-cart");

    addToCartButton.addEventListener("click", function() {
      // Exemplo de ação simples: mostrar um alerta
      alert("Produto adicionado ao carrinho!");

      // Aqui pode adicionar outras ações, como enviar para o carrinho, atualizar um contador, etc.
      // Exemplo: 
      // window.location.href = "/carrinho"; // Redirecionar para a página do carrinho, por exemplo.
    });
  });
</script>



<script>
    document.addEventListener("DOMContentLoaded", function () {
    const addToCartButton = document.querySelector(".add-to-cart");
    const cancelContainer = document.querySelector(".cancel-container");

    addToCartButton.addEventListener("click", function () {
        // Verifica se o botão já foi clicado
        if (!addToCartButton.classList.contains("added")) {
            // Atualiza o botão
            addToCartButton.textContent = "Adicionado ao Carrinho";
            addToCartButton.classList.add("added");

            // Cria o botão de cancelar
            const cancelButton = document.createElement("button");
            cancelButton.textContent = "Cancelar";
            cancelButton.classList.add("cancel-button");

            // Adiciona o botão ao container
            cancelContainer.appendChild(cancelButton);

            // Adiciona o evento de clique ao botão de cancelar
            cancelButton.addEventListener("click", function () {
                // Restaura o estado inicial do botão "Adicionar ao Carrinho"
                addToCartButton.textContent = "Adicionar ao Carrinho";
                addToCartButton.classList.remove("added");

                // Remove o botão de cancelar
                cancelContainer.removeChild(cancelButton);
            });
        }
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
    const addToCartButton = document.querySelector(".add-to-cart");

    if (addToCartButton) {
        addToCartButton.addEventListener("click", function (e) {
            e.preventDefault(); // Impede o envio normal do formulário

            const form = this.closest("form"); // Obtém o formulário ao qual o botão pertence
            const formData = new FormData(form); // Cria um FormData com os dados do formulário

            fetch("", {
                method: "POST", // Envia os dados para o próprio ficheiro (processado pelo PHP)
                body: formData, // Inclui os dados do produto
            })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Erro ao processar a requisição");
                }
                return response.text();
            })
            .then(() => {
                alert("Produto adicionado ao carrinho!"); // Exibe uma mensagem de sucesso
                atualizarCarrinho(); // Atualiza o contador no ícone do carrinho
            })
            .catch((error) => {
                console.error("Erro ao adicionar ao carrinho:", error);
            });
        });
    }

    // Função para atualizar o contador do carrinho
    function atualizarCarrinho() {
        fetch("contar_carrinho.php") // Endpoint que retorna o número de itens no carrinho
            .then((response) => response.text())
            .then((data) => {
                const contador = document.querySelector(".icons a[href='cart.php'] .contador");
                if (contador) {
                    contador.textContent = data > 0 ? data : ""; // Atualiza o contador ou esconde
                }
            });
    }

    // Atualiza o contador do carrinho ao carregar a página
    atualizarCarrinho();
});
</script>


<?php include('footer.php'); ?>

</body>
</html>
