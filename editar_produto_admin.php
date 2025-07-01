<?php
require_once "config.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID do produto inválido.");
}

if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: acesso_negado.php");
    exit();
}

$id_produto = (int) $_GET['id'];
$mensagem = "";

if (isset($_GET['edit']) && $_GET['edit'] === 'success') {
    $mensagem = "Produto atualizado com sucesso!";
}

$sql = "SELECT * FROM produtos WHERE id_produtos = ?";
$stmt = $liga->prepare($sql);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();

if (!$produto) die("Produto não encontrado.");

$categorias = buscarCategoriasDistintas();
$marcas = buscarMarcasDisponiveis();
$marcasSelecionadas = buscarMarcasDoProduto($id_produto);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_produto']);
    $preco = floatval($_POST['preco']);
    $novoUpload = $_FILES['caminho_imagem'];
    $novoUploadHover = $_FILES['caminho_imagem_hover'];
    $categoria = trim($_POST['categoria']);
    $marcasSelecionadasPost = $_POST['id_marcas'] ?? [];

    if (!empty($nome) && $preco > 0) {

        $sqlUpdate = "UPDATE produtos SET nome_produto = ?, preco = ?, categoria = ?";
        $params = [$nome, $preco, $categoria];

        if ($novoUpload['error'] === 0) {
            $ext = pathinfo($novoUpload['name'], PATHINFO_EXTENSION);
            $nomeFicheiro = 'uploads/' . uniqid('prod_') . '.' . $ext;
            move_uploaded_file($novoUpload['tmp_name'], $nomeFicheiro);
            $sqlUpdate .= ", caminho_imagem = ?";
            $params[] = $nomeFicheiro;
        }

        if ($novoUploadHover['error'] === 0) {
            $extHover = pathinfo($novoUploadHover['name'], PATHINFO_EXTENSION);
            $nomeFicheiroHover = 'uploads/' . uniqid('hover_') . '.' . $extHover;
            move_uploaded_file($novoUploadHover['tmp_name'], $nomeFicheiroHover);
            $sqlUpdate .= ", caminho_imagem_hover = ?";
            $params[] = $nomeFicheiroHover;
        }

        $sqlUpdate .= " WHERE id_produtos = ?";
        $params[] = $id_produto;

        $stmtUpdate = $liga->prepare($sqlUpdate);
        $types = str_repeat('s', count($params) - 1) . 'i';
        $stmtUpdate->bind_param($types, ...$params);

        if ($stmtUpdate->execute()) {
            $liga->query("DELETE FROM produtos_marcas WHERE id_produto = $id_produto");
            foreach ($marcasSelecionadasPost as $id_marca) {
                $stmtMarca = $liga->prepare("INSERT INTO produtos_marcas (id_produto, id_marcas) VALUES (?, ?)");
                $stmtMarca->bind_param("ii", $id_produto, $id_marca);
                $stmtMarca->execute();
            }

            header("Location: editar_produto_admin.php?id=$id_produto&edit=success");
            exit;
        } else {
            $mensagem = " Erro ao atualizar o produto.";
        }
    } else {
        $mensagem = " Nome ou preço inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-user">
        <h2>Admin - <?= htmlspecialchars($_SESSION['nome_utilizador'] ?? 'Sem nome') ?></h2>
    </div>
    <ul>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="adicionar_produtos_admin.php">Back to the list</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="edit-form-container">
        <h1>Edit Product</h1>

        <?php if ($mensagem): ?>
            <div class="message-box"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-flex-produto">
            <div class="form-group-produto">
                <label>Product Name:</label>
                <input type="text" name="nome_produto" value="<?= htmlspecialchars($produto['nome_produto']) ?>" required>
            </div>

            <div class="form-group-produto">
                <label>Category:</label>
                <div class="dropdown-categorias" onclick="toggleDropdownCategorias(this)">
                    <div class="dropdown-button-categorias"><?= htmlspecialchars($produto['categoria']) ?: 'SELECT' ?></div>
                    <div class="dropdown-list-categorias">
                        <?php foreach ($categorias as $cat): ?>
                            <label>
                                <input type="radio" name="categoria" value="<?= htmlspecialchars($cat) ?>" <?= $produto['categoria'] === $cat ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($cat) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-group-produto">
                <label>Brands:</label>
                <div class="dropdown-marcas" onclick="toggleDropdownMarcas(this)">
                    <div class="dropdown-button-marcas" id="selectedBrands">
                        <?= htmlspecialchars(implode(' x ', array_column(array_filter($marcas, fn($m) => in_array($m['id_marca'], $marcasSelecionadas)), 'nome_marca'))) ?: 'SELECT' ?>
                    </div>
                    <div class="dropdown-list-marcas">
                        <?php foreach ($marcas as $marca): ?>
                            <label>
                                <input type="checkbox" name="id_marcas[]" value="<?= $marca['id_marca'] ?>" <?= in_array($marca['id_marca'], $marcasSelecionadas) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($marca['nome_marca']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-group-produto">
                <label>Price (€):</label>
                <input type="number" step="0.01" name="preco" value="<?= number_format($produto['preco'], 2, '.', '') ?>" required class="input-preco">
            </div>

            <div class="form-group-produto-imagem">
                <div>
                    <label>Current Main Image:</label><br>
                    <img src="<?= $produto['caminho_imagem'] ?>" alt="Imagem atual" class="imagem-produto-preview">
                    <div class="upload-below">
                        <label class="custom-file-upload-small">
                            <input type="file" name="caminho_imagem" accept="image/*" onchange="updateFileName(this, 'file-chosen')">
                            Choose file
                        </label>
                        <span id="file-chosen" class="file-name-label">No files selected</span>
                    </div>
                </div>

                <div>
                    <label>Current Hover Image:</label><br>
                    <img src="<?= $produto['caminho_imagem_hover'] ?>" alt="Imagem hover" class="imagem-produto-preview">
                    <div class="upload-below">
                        <label class="custom-file-upload-small">
                            <input type="file" name="caminho_imagem_hover" accept="image/*" onchange="updateFileName(this, 'file-chosen-hover')">
                            Choose file
                        </label>
                        <span id="file-chosen-hover" class="file-name-label">No files selected</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-verde">Save Changes</button>
        </form>
    </div>
</div>

<script>
function toggleDropdownMarcas(el) {
    const list = el.querySelector(".dropdown-list-marcas");
    list.style.display = list.style.display === "block" ? "none" : "block";
}

function toggleDropdownCategorias(el) {
    el.classList.toggle('open');
}

document.querySelectorAll(".dropdown-list-marcas input[type='checkbox']").forEach(function(checkbox) {
    checkbox.addEventListener("change", function () {
        const dropdown = this.closest(".dropdown-marcas");
        const button = dropdown.querySelector(".dropdown-button-marcas");
        const selected = Array.from(dropdown.querySelectorAll("input[type='checkbox']:checked"))
            .map(cb => cb.nextElementSibling.textContent.trim())
            .join(" x ") || "SELECT";
        button.textContent = selected;
    });
});

function updateFileName(input, spanId) {
    const fileName = input.files[0]?.name || "No files selected";
    document.getElementById(spanId).textContent = fileName;
}
</script>

</body>
</html>
