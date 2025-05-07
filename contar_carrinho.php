<?php
session_start();
include('config.php');

if (!isset($_SESSION['id_utilizador'])) {
    echo 0;
    exit;
}

$idUtilizador = $_SESSION['id_utilizador'];
$sql = "SELECT SUM(quantidade) as total FROM carrinho WHERE id_utilizador = ?";
$stmt = $liga->prepare($sql);
$stmt->bind_param("i", $idUtilizador);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo $result['total'] ?? 0;
?>
