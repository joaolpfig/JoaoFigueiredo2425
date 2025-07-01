<?php
session_start();
include("config.php");

// Apenas admins podem aceder
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    http_response_code(403);
    exit("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_utilizador'] ?? null;
    $new_type = $_POST['novo_tipo'] ?? null;

    if ($id && is_numeric($id) && in_array($new_type, ['admin', 'worker', 'user'])) {

        // Impede o próprio admin de se rebaixar
        if ((int)$_SESSION['id_utilizador'] === (int)$id) {
            http_response_code(400);
            exit(" You cannot change your own account type.");
        }

        $stmt = $liga->prepare("UPDATE utilizadores SET tipo_utilizador = ? WHERE id_utilizador = ?");
        $stmt->bind_param("si", $new_type, $id);

        if ($stmt->execute()) {
            echo " Account type updated successfully.";
        } else {
            http_response_code(500);
            echo " Failed to update account type.";
        }

        $stmt->close();
    } else {
        http_response_code(400);
        echo " Invalid data.";
    }
} else {
    http_response_code(405); // Método não permitido
    echo " Invalid request method.";
}
?>
