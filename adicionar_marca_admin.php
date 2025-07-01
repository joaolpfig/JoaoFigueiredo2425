<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "config.php";

if (!isset($_SESSION['tipo_utilizador']) || !in_array($_SESSION['tipo_utilizador'], ['admin', 'worker'])) {
    header("Location: acesso_negado.php");
    exit();
}

$mensagem = "";
if (isset($_GET['success'])) {
    $mensagem = "✅ Brand added successfully!";
} elseif (isset($_GET['error'])) {
    $mensagem = "❌ Error uploading image or saving brand.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandName = trim($_POST['nome_marca']);
    $image = $_FILES['caminho_imagem_brand'];

    if (!empty($brandName) && $image['error'] === 0) {
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/' . uniqid('brand_') . '.' . $extension;

        if (move_uploaded_file($image['tmp_name'], $filename)) {
            if (inserirMarca($brandName, $filename)) {
                header("Location: adicionar_marca_admin.php?success=1");
                exit;
            } else {
                header("Location: adicionar_marca_admin.php?error=1");
                exit;
            }
        } else {
            header("Location: adicionar_marca_admin.php?error=1");
            exit;
        }
    } else {
        header("Location: adicionar_marca_admin.php?error=1");
        exit;
    }
}

$page = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$per_page = 10;
$brands = listarMarcasPaginadas($page, $per_page);
$total_brands = contarTotalMarcas();
$total_pages = ceil($total_brands / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Brands | PlugVintage Admin</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-user">
        <h2><?= ucfirst($_SESSION['tipo_utilizador']) ?> - <?= htmlspecialchars($_SESSION['nome_utilizador'] ?? 'No name') ?></h2>
    </div>
    <ul>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="adicionar_produtos_admin.php">Manage Product</a></li>
        <li><a href="adicionar_marca_admin.php" class="active">Manage Brands</a></li>
        <?php if ($_SESSION['tipo_utilizador'] === 'admin'): ?>
            <li><a href="gerir_encomendas.php">Orders</a></li>
            <li><a href="contas_ativas.php">Accounts</a></li>
        <?php endif; ?>
        <li><a href="myperfil.php">Back to Profile</a></li>
    </ul>
</div>

<div class="main-content">
    <h1 class="page-title">Add New Brand</h1>

    <?php if ($mensagem): ?>
        <div class="message-box"><?= $mensagem ?></div>
    <?php endif; ?>

    <div class="card brand-form">
        <form method="POST" enctype="multipart/form-data" class="form-flex" id="brandForm">


            <div class="form-group">
                <label for="brandName">Brand Name:</label>
                <input type="text" id="brandName" name="nome_marca">
            </div>

            <div class="form-group">
    <label for="brandLogo">Logo Image:</label>
    <label class="custom-file-upload">
        <input type="file" id="brandLogo" name="caminho_imagem_brand" accept="image/*" onchange="updateFileName(this)">
        Choose file
    </label>
    <span id="file-chosen" class="file-name-label">No file chosen</span>
</div>


            <button type="submit" class="btn-verde">Add Brand</button>
        </form>
    </div>

    <h2>Available Brands</h2>
    <table>
        <thead>
            <tr>
                <th>Logo</th>
                <th>Name</th>
                <th>Total Products</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($brands) > 0): ?>
                <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($brand['caminho_imagem_brand']) ?>" alt="logo" style="height: 40px;"></td>
                        <td><?= htmlspecialchars($brand['nome_marca']) ?></td>
                        <td><?= $brand['total_produtos'] ?></td>
                        <td>
                            <?php if ($_SESSION['tipo_utilizador'] === 'admin'): ?>
                                <a href="editar_marca_admin.php?id=<?= $brand['id_marca'] ?>" class="btn-edit">Edit</a>
                                <a href="#" class="btn-delete" onclick="abrirModal('apagar_marca_admin.php?id=<?= $brand['id_marca'] ?>')">Delete</a>
                            <?php else: ?>
                                <span style="color: #999;">No permissions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No brands found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-admin">
        <?php if ($page > 1): ?>
            <a href="?pagina=<?= $page - 1 ?>" class="arrow-btn">
                <img src="img/IMAGENS INDEX/angulo-esquerdo.png" alt="Previous" class="pagination-arrow">
            </a>
        <?php endif; ?>

        <?php if ($page > 2): ?>
            <a href="?pagina=1">1</a>
            <?php if ($page > 3): ?><span class="dots">...</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 1); $i <= min($total_pages, $page + 1); $i++): ?>
            <a href="?pagina=<?= $i ?>" class="<?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages - 1): ?>
            <?php if ($page < $total_pages - 2): ?><span class="dots">...</span><?php endif; ?>
            <a href="?pagina=<?= $total_pages ?>"><?= $total_pages ?></a>
        <?php endif; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?pagina=<?= $page + 1 ?>" class="arrow-btn">
                <img src="img/IMAGENS INDEX/angulo-direito.png" alt="Next" class="pagination-arrow">
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- ✅ SweetAlert for delete confirmation -->
<script>
function abrirModal(link) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This brand will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = link;
        }
    });
}

function updateFileName(input) {
    const fileName = input.files[0]?.name || "No file chosen";
    document.getElementById("file-chosen").textContent = fileName;
}
</script>




<script>
document.getElementById("brandForm").addEventListener("submit", function(e) {
    const nome = document.getElementById("brandName").value.trim();
    const imagem = document.getElementById("brandLogo").files.length;

    if (!nome || !imagem) {
        e.preventDefault();
        const existing = document.querySelector(".message-box");
        if (!existing) {
            const msg = document.createElement("div");
            msg.className = "message-box error";
            msg.style.marginTop = "10px";
            msg.style.backgroundColor = "#f8d7da";
            msg.style.color = "#721c24";
            msg.style.border = "1px solid #f5c6cb";
            msg.style.padding = "10px 15px";
            msg.style.borderRadius = "5px";
            msg.innerText = "❌ Please fill in all required fields before submitting.";
            document.querySelector(".main-content h1").after(msg);
        }
    }
});
</script>





</body>
</html>
