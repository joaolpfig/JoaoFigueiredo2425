<?php
session_start();
require_once 'config.php';

// Verifica se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
  header("Location: login.php");
  exit();
}

$idUtilizador = $_SESSION['id_utilizador'];

// Obtém os produtos do carrinho do usuário
$produtosCarrinho = buscarItensCarrinho($idUtilizador);
$totalItens = contarItensCarrinho($idUtilizador);
if (isset($_SESSION['id_utilizador'])) {
  $totalItensCarrinho = contarItensCarrinho($_SESSION['id_utilizador']);
}

$total = 0;


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
  <nav class="header">
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
  </nav>
  <main>

    <div class="cart-title">
      <h1>My cart</h1>
      <?php if (!empty($produtosCarrinho)): ?>
        <table>
          <thead>
            <tr>
              <th>Image</th>
              <th>Product</th>
              <th>Brand</th>
              <th>Price</th>
              <th>Amount</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $total = 0; // Inicializa o total aqui
            foreach ($produtosCarrinho as $produto):
              $total += $produto['preco']; // Soma ao total geral
              ?>
              <tr>
                <td><img src="<?php echo htmlspecialchars($produto['caminho_imagem']); ?>" width="50"></td>
                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                <td><?php echo htmlspecialchars($produto['nome_marca']); ?></td>
                <td><?php echo number_format($produto['preco'], 2, ',', '.'); ?> €</td>
                <td><?php echo htmlspecialchars($produto['quantidade']); ?></td>
                <td>
                  <form method="POST" action="remove_cart.php">
                    <input type="hidden" name="id_carrinho" value="<?php echo $produto['id_carrinho']; ?>">
                    <button type="submit">Remove</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>Your cart is empty!</p>
      <?php endif; ?>
      <div class="total-container">
        <h2 class="cart-total">Total: <?php echo number_format($total, 2, ',', '.'); ?> €</h2>
      </div>

      <?php if (!empty($produtosCarrinho)): ?>
  <div class="checkout-button-container">
    <a href="checkout.php" class="checkout-button">Checkout</a>
  </div>
<?php endif; ?>

    </div>
  </main>




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

  <?php include('footer.php'); ?>

</body>

</html>