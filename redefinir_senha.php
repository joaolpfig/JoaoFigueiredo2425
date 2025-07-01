<?php
session_start();
require_once 'config.php';

$erro = '';
$sucesso = '';

if (!isset($_GET['token'])) {
    die("Invalid access.");
}

$token = $_GET['token'];

// Verifica se o token existe e não expirou
$stmt = $liga->prepare("SELECT email, expiracao FROM recuperacoes_password WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$recuperacao = $result->fetch_assoc();

if (!$recuperacao) {
    die("Invalid or expired token.");
}

if (new DateTime() > new DateTime($recuperacao['expiracao'])) {
    die("Token has expired. Please request a new one.");
}

// Se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (empty($nova_senha) || empty($confirmar_senha)) {
        $erro = "Please fill out all fields.";
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = "Passwords do not match.";
    } else {
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Atualiza a senha do utilizador
        $stmt = $liga->prepare("UPDATE utilizadores SET senha = ? WHERE email = ?");
        $stmt->bind_param('ss', $hash, $recuperacao['email']);
        $stmt->execute();

        // Remove o token usado
        $stmt = $liga->prepare("DELETE FROM recuperacoes_password WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();

        $sucesso = "Your password has been updated. You can now <a href='login.php'>log in</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - PlugVintage</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div class="reset-container">
    <h1 class="reset-title">Reset Password</h1>

    <?php if (!empty($erro)): ?>
        <p class="reset-error"><?= $erro ?></p>
    <?php elseif (!empty($sucesso)): ?>
        <p class="reset-success"><?= $sucesso ?></p>
    <?php else: ?>
        <form method="POST" class="reset-form">
            <div class="reset-group">
                <label for="nova_senha">New Password:</label>
                <input type="password" name="nova_senha" class="reset-input" required>
            </div>

            <div class="reset-group">
                <label for="confirmar_senha">Confirm Password:</label>
                <input type="password" name="confirmar_senha" class="reset-input" required>
            </div>

            <button type="submit" class="reset-btn">Change Password</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
