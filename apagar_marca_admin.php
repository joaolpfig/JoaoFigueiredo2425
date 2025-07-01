<?php
require_once "config.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: acesso_negado.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID.");
}

$id_marca = (int) $_GET['id'];

// First remove from relationship table
$stmt1 = $liga->prepare("DELETE FROM produtos_marcas WHERE id_marcas = ?");
$stmt1->bind_param("i", $id_marca);
$stmt1->execute();

// Then delete the brand
$stmt2 = $liga->prepare("DELETE FROM marcas WHERE id_marca = ?");
$stmt2->bind_param("i", $id_marca);

if ($stmt2->execute()) {
    header("Location: adicionar_marca_admin.php?delete=success");
    exit;
} else {
    echo "Error deleting brand.";
}
