<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "config.php";

if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: access_denied.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['estado'])) {
    header("Location: gerir_encomendas.php?error=invalid");
    exit();
}

$id_encomenda = (int)$_GET['id'];
$novo_estado = $_GET['estado'];

$estados_validos = ['pendente', 'pago', 'enviado', 'entregue', 'cancelada'];
if (!in_array($novo_estado, $estados_validos)) {
    header("Location: gerir_encomendas.php?error=invalid_state");
    exit();
}

// Obter detalhes da encomenda
$encomenda = obterDetalhesEncomenda($id_encomenda);
if (!$encomenda) {
    header("Location: gerir_encomendas.php?error=not_found");
    exit();
}

// Evitar cancelar encomendas entregues
if ($encomenda['estado'] === 'entregue') {
    header("Location: gerir_encomendas.php?error=not_cancelable");
    exit();
}

// Alterar estado
alterarEstadoEncomenda($id_encomenda, $novo_estado);

// Se for cancelada, repor stock
if ($novo_estado === 'cancelada') {
    foreach ($encomenda['produtos'] as $produto) {
        $sql = "UPDATE produtos SET quantidade = quantidade + ? WHERE id_produtos = (
                    SELECT id_produtos FROM produtos WHERE nome_produto = ? LIMIT 1
                )";
        $stmt = $liga->prepare($sql);
        $stmt->bind_param("is", $produto['quantidade'], $produto['nome_produto']);
        $stmt->execute();
    }

    // Enviar email ao cliente (opcional, comentado por agora)
    /*
    $to = $encomenda['email'];
    $subject = "Your order #{$id_encomenda} has been cancelled";
    $headers = "From: PlugVintage <no-reply@plugvintage.com>\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = "<h2>Order #{$id_encomenda} Cancelled</h2>";
    $message .= "<p><strong>Date:</strong> {$encomenda['data_encomenda']}</p>";
    $message .= "<p><strong>Total:</strong> €" . number_format($encomenda['total'], 2, ',', '.') . "</p>";
    $message .= "<h3>Products:</h3><ul>";
    foreach ($encomenda['produtos'] as $p) {
        $message .= "<li>{$p['nome_produto']} - {$p['quantidade']}x (€" . number_format($p['preco_unitario'], 2, ',', '.') . ")</li>";
    }
    $message .= "</ul><p>If this was not intended, contact support.</p>";

    mail($to, $subject, $message, $headers);
    */
}

header("Location: gerir_encomendas.php?success=updated");
exit();
