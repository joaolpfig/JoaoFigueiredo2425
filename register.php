<?php
session_start();
require_once 'config.php';
require_once 'email.php'; // Incluindo o arquivo do email.php
$error = '';

// Verifica a conexão
if (!$liga) {
    die("Database connection error: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['full-name']) ? trim($_POST['full-name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmarSenha = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address,please include an '@' in the email address.";
    } elseif (strlen($senha) < 6) {
        $error = "Password must have at least 6 characters.";
    } elseif ($senha !== $confirmarSenha) {
        $error = "Passwords do not match.";
    } else {
        $sql = "SELECT id_utilizador FROM utilizadores WHERE email = ?";
        $stmt = $liga->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with this email already exists. Please use another email or log in.";
        } else {
            $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO utilizadores (nome_utilizador, email, senha, tipo_utilizador) VALUES (?, ?, ?, 'User')";
            $stmt = $liga->prepare($sql);
            $stmt->bind_param('sss', $nome, $email, $hashedPassword);

            if ($stmt->execute()) {
                // Enviar e-mail de boas-vindas
                enviarEmailBoasVindas($email, $nome); // Chama a função para enviar o e-mail

                // Redirecionar para o login com sucesso
                header("Location: login.php?success=1");
                exit();
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - PlugVintage</title>
  <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
  <link rel="stylesheet" href="./css/style.css">
</head>
<body>
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
        <a href="#" id="search-icon">
            <img src="img/IMAGENS INDEX/pesquisa.png" alt="Search" class="icon-image">
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
</header>

<div class="register-container">
    <button class="back-button" onclick="history.back()">
        <img src="img/IMAGENS INDEX/seta-esquerda.png" alt="Back">
    </button>

    <div class="register-box">
        <h1>Sign Up</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="registerForm" action="register.php" method="POST" novalidate>
            <label for="full-name">Full Name:</label>
            <input type="text" id="full-name" name="full-name" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required title="Please enter a valid email address">

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required title="Password must have at least 6 characters">
                <img src="img/IMAGENS INDEX/ocultar.png" alt="Show Password" onclick="togglePassword('password', this)">
            </div>

            <label for="confirm-password">Confirm Password:</label>
            <div class="password-container">
                <input type="password" id="confirm-password" name="confirm-password" required title="Passwords must match">
                <img src="img/IMAGENS INDEX/ocultar.png" alt="Show Password" onclick="togglePassword('confirm-password', this)">
            </div>

            <button type="submit">Create Account</button>
        </form>
    </div>
</div>

<script>
document.getElementById("registerForm").onsubmit = function(event) {
    // Verificar e alterar a mensagem de erro para o e-mail
    var emailInput = document.getElementById("email");
    if (emailInput.validity.typeMismatch) {
        emailInput.setCustomValidity("Please include an '@' in the email address");
    } else {
        emailInput.setCustomValidity("");  // Limpar a mensagem de erro
    }

    // Verificar e alterar a mensagem de erro para a senha
    var passwordInput = document.getElementById("password");
    if (passwordInput.validity.tooShort) {
        passwordInput.setCustomValidity("Password must have at least 6 characters");
    } else {
        passwordInput.setCustomValidity("");  // Limpar a mensagem de erro
    }
};

function togglePassword(inputId, icon) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.src = "img/IMAGENS INDEX/olho.png";
    } else {
        input.type = "password";
        icon.src = "img/IMAGENS INDEX/ocultar.png";
    }
}
</script>

</body>
</html>
