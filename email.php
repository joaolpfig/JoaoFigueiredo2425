<?php
// Função para converter imagem para Base64
function converterImagemParaBase64($caminhoImagem) {
    $caminhoFisico = __DIR__ . '/' . $caminhoImagem; // Junta caminho absoluto

    if (!file_exists($caminhoFisico)) return '';

    $imagemBinaria = file_get_contents($caminhoFisico);
    $tipo = mime_content_type($caminhoFisico);
    $base64 = base64_encode($imagemBinaria);

    return "<img src='data:$tipo;base64,$base64' alt='Product Image' style='max-width: 80px; border-radius: 6px;' />";
}



function enviarEmailCancelamento($to, $nome_cliente, $numero_pedido, $produtos, $total) {
    $subject = "⚠️ Your Order Has Been Cancelled - PlugVintage";

    // Construir linhas da tabela dos produtos
    $productRows = '';
    foreach ($produtos as $produto) {
        $productRows .= "
            <tr>
                <td style='text-align: center;'>" . htmlspecialchars($produto['nome_produto']) . "</td>
                <td style='text-align: center;'>" . htmlspecialchars($produto['tamanho']) . "</td>
                <td style='text-align: center;'>" . htmlspecialchars($produto['cor']) . "</td>
                <td style='text-align: center;'>" . intval($produto['quantidade']) . "</td>
            </tr>
        ";
    }

    // Conteúdo HTML do email
    $message = "
    <html>
    <head>
        <title>Order Cancelled</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; padding: 20px;'>
        <div style='max-width: 700px; margin: auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <h2 style='color: #e74c3c; text-align: center;'>Hello, " . htmlspecialchars($nome_cliente) . "</h2>
            <p style='text-align: center;'>Your order with number <strong>$numero_pedido</strong> has been <strong>cancelled</strong>.</p>

            <h3 style='margin-top: 40px;'>Order Summary</h3>
            <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                <thead>
                    <tr style='background-color: #f0f0f0;'>
                        <th style='padding: 10px;'>Product</th>
                        <th style='padding: 10px;'>Size</th>
                        <th style='padding: 10px;'>Color</th>
                        <th style='padding: 10px;'>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    $productRows
                </tbody>
            </table>

            <p style='margin-top: 30px; text-align: right; font-size: 18px;'><strong>Total: €" . number_format($total, 2, ',', '.') . "</strong></p>

            <p style='text-align: center; color: #777; margin-top: 40px;'>— <strong>PlugVintage Team</strong></p>
        </div>
    </body>
    </html>
    ";

    // Cabeçalhos
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: PlugVintage <plugvintagept@gmail.com>\r\n";

    // Enviar email
    mail($to, $subject, $message, $headers);
}



function enviarEmailBoasVindas($to, $nome_cliente) {
    $subject = "Welcome to PlugVintage!";

    $message = "
    <html>
    <head>
        <title>Welcome to PlugVintage!</title>
        <style>
            body { font-family: 'Arial', sans-serif; background-color: #f9f9f9; color: #333; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background-color: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
            h1 { text-align: center; color: #4CAF50; }
            p { font-size: 16px; line-height: 1.5; text-align: center; color: #555; }
            .button { background-color: #4CAF50; color: white; padding: 12px 24px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px; margin: 20px 0; font-size: 16px; }
            .footer { font-size: 14px; color: #888; text-align: center; margin-top: 40px; }
            .header { text-align: center; margin-bottom: 20px; }
            .header img { width: 120px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='https://res.cloudinary.com/dudnbd9ue/image/upload/v1750868602/logoplug_jpensg.jpg' alt='PlugVintage Logo' class='logo'>
            </div>
            <h1>Hi, $nome_cliente!</h1>
            <p>Welcome to PlugVintage! We're very excited to have you with us.</p>
            <p>Now you can explore our products and start shopping. If you need help, our support team is always here for you.</p>
            <a href=http://localhost/JoaoFigueiredo2425/index.php class='button'>Go to the Shop</a>
            <div class='footer'>
                <p>— <strong>PlugVintage Team</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: PlugVintage <plugvintagept@gmail.com>" . "\r\n";

    if (mail($to, $subject, $message, $headers)) {
        echo "Welcome email sent successfully!";
    } else {
        echo "Failed to send welcome email.";
    }
}






function enviarEmailConfirmacao($to, $nome_cliente, $numero_pedido, $produtos, $total) {
    $subject = "✅ Order Confirmation - PlugVintage";

    $productRows = '';
    foreach ($produtos as $produto) {
        $productRows .= "
            <tr>
                <td style='text-align: center;'>" . htmlspecialchars($produto['nome_produto']) . "</td>
                <td style='text-align: center;'>" . htmlspecialchars($produto['tamanho']) . "</td>
                <td style='text-align: center;'>" . htmlspecialchars($produto['cor']) . "</td>
                <td style='text-align: center;'>" . intval($produto['quantidade']) . "</td>
            </tr>
        ";
    }

    $message = "
    <html>
    <head>
        <title>Order Confirmation</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; padding: 20px;'>
        <div style='max-width: 700px; margin: auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <h2 style='color: #2ecc71; text-align: center;'>Hi, " . htmlspecialchars($nome_cliente) . "</h2>
            <p style='text-align: center;'>Your order <strong>#$numero_pedido</strong> has been confirmed!</p>

            <h3 style='margin-top: 30px;'>Order Summary</h3>
            <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                <thead>
                    <tr style='background-color: #f0f0f0;'>
                        <th style='padding: 10px;'>Product</th>
                        <th style='padding: 10px;'>Size</th>
                        <th style='padding: 10px;'>Color</th>
                        <th style='padding: 10px;'>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    $productRows
                </tbody>
            </table>

            <p style='margin-top: 30px; text-align: right; font-size: 18px;'><strong>Total: €" . number_format($total, 2, ',', '.') . "</strong></p>

            <p style='text-align: center; color: #777; margin-top: 40px;'>Thank you for shopping with us!<br>— <strong>PlugVintage Team</strong></p>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: PlugVintage <plugvintagept@gmail.com>\r\n";

    mail($to, $subject, $message, $headers);
}



function enviarEmailRecuperacao($to, $nome_cliente, $token) {
    $link = "http://localhost/JOAOFIGUEIREDO2425/redefinir_senha.php?token=" . urlencode($token);

    $subject = "🔐 Password Reset - PlugVintage";

    $message = "
    <html>
    <head>
        <title>Reset Your Password</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
        <div style='max-width: 600px; margin: auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <h2 style='color: #333;'>Hello, " . htmlspecialchars($nome_cliente) . "</h2>
            <p>We received a request to reset your password.</p>
            <p>If it was you, click the button below to create a new password:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$link' style='background-color: #e74c3c; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a>
            </div>
            <p>If you didn't request this, just ignore this email. Your password will stay the same.</p>
            <p style='color: #777;'>— PlugVintage Team</p>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: PlugVintage <plugvintagept@gmail.com>\r\n";

    mail($to, $subject, $message, $headers);
}


?>
