<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($email) || empty($senha)) {
        $error = "Por favor, preencha todos os campos.";
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
            $error = "Email ou senha incorretos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PlugVintage</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">
        <img src="img/IMAGENS PARA O ICON SITE/logoplug.jpg" alt="PlugVintage Logo">
    </a>
    <nav class="navbar">
        <a href="index.php">HOME</a>
        <a href="listar.php">SHOP ALL</a>
        <a href="tees.php">TEES</a>
        <a href="bottoms.php">BOTTOMS</a>
        <a href="sweats+jackets.php">SWEATS + JACKETS</a>
        <a href="shoes.php">SHOES</a>
        <a href="accesories.php">ACCESSORIES</a>
    </nav>

    <div class="icons">
        <a href="#" id="search-icon">
            <img src="img/IMAGENS INDEX/pesquisa.png" alt="Pesquisa" class="icon-image">
        </a>
        <a href="cart.php">
            <img src="img/IMAGENS INDEX/carrinho.png" alt="Carrinho" class="icon-image">
        </a>
        <div class="profile-container">
            <a href="#" id="profile-icon">
                <img src="img/IMAGENS INDEX/profile.png" alt="Profile" class="icon-image">
            </a>
            <div class="profile-dropdown" id="profile-dropdown">
                <?php if (isset($_SESSION['nome_utilizador'])): ?>
                    <p>Olá, <?php echo htmlspecialchars($_SESSION['nome_utilizador']); ?></p>
                    <button id="logout-btn">Logout</button>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Back Button -->
<button class="back-button" onclick="history.back()">
    <img src="img/IMAGENS INDEX/seta-esquerda.png" alt="Back">
</button>

<main>
    <div class="login-container">
        <h1>Login</h1>
        <form method="POST" action="" class="login-form">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>

            <button type="submit">Entrar</button>
        </form>
        <p>Não tem conta? <a href="register.php">Registre-se aqui.</a></p>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const profileIcon = document.getElementById("profile-icon");
    const profileDropdown = document.getElementById("profile-dropdown");
    const logoutBtn = document.getElementById("logout-btn");

    profileIcon.addEventListener("click", function (event) {
        event.preventDefault();
        profileDropdown.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
        if (!profileIcon.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove("show");
        }
    });

    logoutBtn.addEventListener("click", function () {
        window.location.href = "logout.php";
    });
});
</script>

</body>
</html>
