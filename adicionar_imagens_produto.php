<?php
session_start();
include("config.php");

// Verifica permissões
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: myperfil.php");
    exit();
}

// Valida o ID do produto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$productId = intval($_GET['id']);

// Diretório de upload
$uploadDir = __DIR__ . "/img/imagens_produtos/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Extensões e tamanho máximo
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$maxFileSize = 5 * 1024 * 1024;

// Processamento do upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    $totalFiles = count($_FILES['images']['name']);

    for ($i = 0; $i < $totalFiles; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $originalName = basename($_FILES['images']['name'][$i]);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions)) continue;
            if ($_FILES['images']['size'][$i] > $maxFileSize) continue;

            $uniqueName = uniqid() . '_' . $originalName;
            $destination = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmpName, $destination)) {
                adicionarImagemSecundaria($productId, $uniqueName);
            }
        }
    }
    header("Location: adicionar_imagens_produto.php?id=$productId");
    exit();
}

// Processar delete de imagem secundária
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $imageId = intval($_GET['delete']);
    eliminarImagemSecundaria($imageId, $productId);
    header("Location: adicionar_imagens_produto.php?id=$productId");
    exit();
}

// Buscar imagens principais
$stmt = mysqli_prepare($liga, "SELECT caminho_imagem, caminho_imagem_hover FROM produtos WHERE id_produtos = ?");
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$productImages = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Buscar imagens secundárias
$existingSecondaryImages = buscarImagensProdutoCompleto($productId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Images - Product <?= $productId ?></title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-user">
        <h2><?= ucfirst($_SESSION['tipo_utilizador']) ?> - <?= htmlspecialchars($_SESSION['nome_utilizador'] ?? 'No Name') ?></h2>
    </div>
    <ul>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="adicionar_produtos_admin.php">Back to the list</a></li>
    </ul>
</div>

<div class="main-content">
    <h1>Manage Images - Product #<?= $productId ?></h1>

    <!-- Formulário de upload -->
    <div class="upload-section">
        <form method="post" enctype="multipart/form-data">
            <label class="custom-file-upload">
                <input type="file" name="images[]" multiple required onchange="updateFileCount(this)" style="display:none;">
                Select Additional Images
            </label>
            <span id="file-count">No files selected</span><br><br>
            <button type="submit" class="upload-button">Upload</button>
        </form>
    </div>

    <!-- Mostrar imagens principais -->
    <div class="images-grid">
        <?php if (!empty($productImages['caminho_imagem'])): ?>
            <div class="image-card">
                <img src="<?= htmlspecialchars($productImages['caminho_imagem']) ?>" alt="Main Image">
                <div>Main Image</div>
            </div>
        <?php endif; ?>
        <?php if (!empty($productImages['caminho_imagem_hover'])): ?>
            <div class="image-card">
                <img src="<?= htmlspecialchars($productImages['caminho_imagem_hover']) ?>" alt="Hover Image">
                <div>Hover Image</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Mostrar imagens secundárias -->
    <h3>Secondary Images:</h3>
    <div class="images-grid">
        <?php if (empty($existingSecondaryImages)): ?>
            <p>No additional images.</p>
        <?php else: ?>
            <?php foreach ($existingSecondaryImages as $img):
                if (str_contains($img['imagens'], 'img/') || str_contains($img['imagens'], 'img\\')) {
                    $caminhoImagem = str_replace('\\', '/', $img['imagens']);
                } else {
                    $caminhoImagem = 'img/imagens_produtos/' . $img['imagens'];
                }
            ?>
                <div class="image-card">
                    <img src="<?= htmlspecialchars($caminhoImagem) ?>" alt="Secondary Image">
                    <a class="btn-delete delete-button" href="?id=<?= $productId ?>&delete=<?= $img['id_imagem'] ?>">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function updateFileCount(input) {
    const count = input.files.length;
    document.getElementById('file-count').innerText = count === 0 
        ? "No files selected" 
        : `${count} file${count > 1 ? 's' : ''} selected`;
}

// SweetAlert2 para o delete
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'Are you sure?',
                text: "This image will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});
</script>

</body>
</html>
