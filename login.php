<?php
session_start();
require_once 'config.php';

$error = '';


if (isset($_POST['email'], $_POST['senha'])) {
    $email = trim($_POST['email']);
    $senha = $_PST['senha'];

    if (empty($email) || empty($senha)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $liga->prepare("SELECT id_utilizador, nome_utilizador, senha FROM utilizadores WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($senha, $user['senha'])) {
            session_regenerate_id(true);
            $_SESSION['id_utilizador'] = $user['id_utilizador'];
            $_SESSION['nome_utilizador'] = $user['nome_utilizador'];

            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
} else {
    $error = "Please fill in all fields.";
}

?>

<!DOCTYPE html>
<html lang="en-UK">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PlugVintage</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css"> <!-- Incluindo o CSS externo -->
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
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

    <button type="button" class="back-button" onclick="window.history.back();">
    <img src="img/IMAGENS INDEX/seta-esquerda.png" alt="Voltar">
</button>


</header>


<!-- Botão de Voltar -->
<button type="button" class="back-button" onclick="window.history.back();">
    <img src="img/IMAGENS INDEX/seta-esquerda.png" alt="Back">
</button>

<main>
    <div class="login-container">
        <h1>Login In</h1>
        <form method="POST" action="login.php" class="login-form">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <label for="email">Email Address:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Sign In</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here.</a></p>
    </div>
</main>


<script>
function togglePassword(inputId, icon) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.src = "img/IMAGENS INDEX/hide-eye.png";
    } else {
        input.type = "password";
        icon.src = "img/IMAGENS INDEX/eye-icon.png";
    }
}
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

</body>
</html>
