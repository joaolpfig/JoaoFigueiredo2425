<?php
require_once 'config.php';
require_once 'email.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Please enter your email address.";
    } else {
        $stmt = $liga->prepare("SELECT nome_utilizador FROM utilizadores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // ✅ INSERE na tabela recuperacoes_password
            $insert = $liga->prepare("INSERT INTO recuperacoes_password (email, token, expiracao) VALUES (?, ?, ?)");
            if ($insert) {
                $insert->bind_param("sss", $email, $token, $expires);
                if ($insert->execute()) {
                    enviarEmailRecuperacao($email, $user['nome_utilizador'], $token);
                    $message = "A recovery email has been sent. Please check your inbox.";
                } else {
                    $message = "Error inserting token: " . $insert->error;
                }
            } else {
                $message = "Error preparing INSERT statement: " . $liga->error;
            }
        } else {
            $message = "This email is not registered.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Recovery</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div class="recover-container">
    <h1 class="recover-title">Password Recovery</h1>

    <?php if (!empty($message)): ?>
        <p class="recover-success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" class="recover-form">
        <div class="recover-group">
            <label for="email">Email address:</label>
            <input type="email" name="email" class="recover-input" required>
        </div>
        <button type="submit" class="recover-btn">Send Recovery Link</button>
    </form>
</div>

</body>
</html>
