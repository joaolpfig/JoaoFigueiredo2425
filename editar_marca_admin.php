<?php
require_once "config.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: acesso_negado.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid brand ID.");
}

$id_marca = (int) $_GET['id'];
$message = "";

if (isset($_GET['edit']) && $_GET['edit'] === 'success') {
    $message = "✅ Brand updated successfully!";
}

$sql = "SELECT * FROM marcas WHERE id_marca = ?";
$stmt = $liga->prepare($sql);
$stmt->bind_param("i", $id_marca);
$stmt->execute();
$result = $stmt->get_result();
$brand = $result->fetch_assoc();

if (!$brand) {
    die("Brand not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['nome_marca']);
    $newLogo = $_FILES['caminho_imagem_brand'];

    if (!empty($name)) {
        $sqlUpdate = "UPDATE marcas SET nome_marca = ?" .
            ($newLogo['error'] === 0 ? ", caminho_imagem_brand = ?" : "") .
            " WHERE id_marca = ?";

        if ($newLogo['error'] === 0) {
            $ext = pathinfo($newLogo['name'], PATHINFO_EXTENSION);
            $fileName = 'uploads/' . uniqid('brand_') . '.' . $ext;
            move_uploaded_file($newLogo['tmp_name'], $fileName);

            $stmtUpdate = $liga->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ssi", $name, $fileName, $id_marca);
        } else {
            $stmtUpdate = $liga->prepare($sqlUpdate);
            $stmtUpdate->bind_param("si", $name, $id_marca);
        }

        if ($stmtUpdate->execute()) {
            header("Location: editar_marca_admin.php?id=$id_marca&edit=success");
            exit;
        } else {
            $message = "❌ Error updating brand.";
        }
    } else {
        $message = "❌ Brand name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Brand</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-user">
        <h2>Admin - <?= htmlspecialchars($_SESSION['nome_utilizador'] ?? 'No name') ?></h2>
    </div>
    <ul>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="adicionar_marca_admin.php">Back to the list</a></li>

    </ul>
</div>

<div class="main-content">
    <h1>Edit Brand</h1>

    <?php if ($message): ?>
        <div class="message-box"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="edit-brand-form">
        <label>Brand Name:</label>
        <input type="text" name="nome_marca" value="<?= htmlspecialchars($brand['nome_marca']) ?>" required>

        <label>Current Logo:</label>
        <img src="<?= $brand['caminho_imagem_brand'] ?>" alt="Current logo" class="imagem-produto-preview">

        <label>Replace Logo (optional):</label><br>
        <label class="custom-file-upload">
            <input type="file" name="caminho_imagem_brand" accept="image/*" onchange="updateFileName(this, 'logo-chosen')">
            Choose file
        </label>
        <span id="logo-chosen" class="file-name-label">No file chosen</span><br><br>

        <button type="submit" class="btn-verde">Save Changes</button>
    </form>
</div>

<script>
function updateFileName(input, spanId) {
    const fileName = input.files[0]?.name || "No file chosen";
    document.getElementById(spanId).textContent = fileName;
}
</script>
</body>
</html>
