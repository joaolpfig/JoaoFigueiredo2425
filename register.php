<?php
require_once 'config.php'; // Conexão com a base de dados
$error = '';
$success = '';

// Verifica se a conexão está ativa
if (!$liga) {
    die("Erro de conexão com a base de dados: " . mysqli_connect_error());
}

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Corrigido para corresponder aos names do HTML
    $nome = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmarSenha = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Valida os campos
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email.";
    } elseif (strlen($senha) < 6) { // Senha deve ter pelo menos 6 caracteres
        $error = "Password must have at least 6 characters.";
    } elseif ($senha !== $confirmarSenha) {
        $error = "Passwords do not match.";
    } else {
        // Verifica se o email já está registado
        $sql = "SELECT id_utilizador FROM utilizadores WHERE email = ?";
        $stmt = $liga->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            // Insere o novo utilizador
            $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO utilizadores (nome_utilizador, email, senha, tipo_utilizador) VALUES (?, ?, ?, 'normal')";
            $stmt = $liga->prepare($sql);
            $stmt->bind_param('sss', $nome, $email, $hashedPassword);

            if ($stmt->execute()) {
                $success = "Account created successfully! You can now log in.";

                // Envio do email
                $assunto = "Welcome to PlugVintage!";
                $mensagem = "Hello $nome,\n\nThank you for registering at PlugVintage!\n\nIf you need any help, contact us.\n\nBest regards,\nPlugVintage Team";

                // Cabeçalhos do email
                $headers = "From: no-reply@plugvintage.com\r\n";
                $headers .= "Reply-To: support@plugvintage.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                // Tenta enviar o email
                if (!mail($email, $assunto, $mensagem, $headers)) {
                    $error = "Account created, but an error occurred while sending the email.";
                }
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

<!-- Back Button -->
<button class="back-button" onclick="history.back()">
    <img src="img/IMAGENS INDEX/seta-esquerda.png" alt="Back">
</button>

<main class="register-container">
    <div class="form-side">
        <h1>Sign Up</h1>
        <form method="POST" action="register.php" class="register-form">
            <!-- Error / Success Messages -->
            <?php if (!empty($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <label for="name">Full Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="email">Email Address:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" name="password" id="password" required>
                <img src="img/IMAGENS INDEX/ocultar.png" alt="Show/Hide" class="toggle-password" onclick="togglePassword('password', this)">
            </div>

            <label for="confirm_password">Confirm Password:</label>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <img src="img/IMAGENS INDEX/ocultar.png" alt="Show/Hide" class="toggle-password" onclick="togglePassword('confirm_password', this)">
            </div>

            <button type="submit">Create Account</button>
        </form>

    </div>
</main>

<script>
function togglePassword(inputId, icon) {
    var input = document.getElementById(inputId);
    
    if (input.type === "password") {
        input.type = "text";
        icon.src = "img/IMAGENS INDEX/olho.png"; // Ícone de ocultar
    } else {
        input.type = "password";
        icon.src = "img/IMAGENS INDEX/ocultar.png"; // Ícone de mostrar
    }
}
</script>

</body>
</html>
