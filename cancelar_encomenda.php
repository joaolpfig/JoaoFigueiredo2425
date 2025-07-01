<?php 
require_once 'config.php';

// ✅ Verifica sessão e permissões de admin
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: access_denied.php");
    exit();
}

// ✅ Verifica se o ID da encomenda foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid order ID.");
}

$id = (int)$_GET['id'];

// ✅ Busca os detalhes da encomenda
$encomenda = obterDetalhesEncomenda($id);
if (!$encomenda) {
    die("Order not found.");
}

// ✅ Cancela a encomenda na base de dados
if (!cancelarEncomenda($id)) {
    die("Failed to cancel the order.");
}

// ✅ Atualiza o stock de cada produto (define quantidade como 1)
foreach ($encomenda['produtos'] as $produto) {
    if (!isset($produto['id_produtos'])) continue;

    $idProduto = $produto['id_produtos'];

    $sql = "UPDATE produtos SET quantidade = 1 WHERE id_produtos = ?";
    $stmt = $liga->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $idProduto);
        $stmt->execute();
    } else {
        echo "Stock update error: " . $liga->error;
    }
}

// ✅ Envia email de cancelamento (sem imagens)
require_once 'email.php';

$to = $encomenda['email'];
$nome_cliente = $encomenda['nome_utilizador'];
$numero_pedido = $encomenda['id_encomenda'];
$produtos = $encomenda['produtos'];
$total = $encomenda['total'];

enviarEmailCancelamento($to, $nome_cliente, $numero_pedido, $produtos, $total);

// ✅ Redireciona com sucesso
header("Location: gerir_encomendas.php?cancel=success");
exit;
