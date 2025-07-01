<?php
require_once "config.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: acesso_negado.php");
    exit();
}

$id_produto = (int) $_GET['id'];

// Apagar ligações à tabela produtos_marcas (se existir)
$stmt1 = $liga->prepare("DELETE FROM produtos_marcas WHERE id_produto = ?");
$stmt1->bind_param("i", $id_produto);
$stmt1->execute();

// Apagar o produto
$stmt2 = $liga->prepare("DELETE FROM produtos WHERE id_produtos = ?");
$stmt2->bind_param("i", $id_produto);

if ($stmt2->execute()) {
    header("Location: adicionar_produtos_admin.php?delete=success");
    exit;
} else {
    echo "Erro ao apagar o produto.";
}
?>
