<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION['tipo_utilizador']) || !in_array($_SESSION['tipo_utilizador'], ['admin', 'worker'])) {
    header("Location: acesso_negado.php");
    exit();
}

$currentPage = basename(__FILE__);

$mensagem = "";
if (isset($_GET['success'])) {
    $mensagem = "Product inserted successfully!";
} elseif (isset($_GET['error'])) {
    $mensagem = "Error uploading images.";
}

$page = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$per_page = 10;

$produtos = listarProdutosPaginados($page, $per_page);
$total_produtos = contarTotalProdutos();
$total_pages = ceil($total_produtos / $per_page);

$marcas = buscarMarcasDisponiveis();
$categorias = buscarCategoriasDistintas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_produto'];
    $categoria = $_POST['categoria'];
    $tamanho = $_POST['tamanho'];
    $cor = $_POST['cor'];
    $preco = $_POST['preco'];
    $keywords = $_POST['keywords'];

    $imagem = $_FILES['caminho_imagem'];
    $imagem_hover = $_FILES['caminho_imagem_hover'];

    $destino_img = 'uploads/' . uniqid('img_') . '_' . basename($imagem['name']);
    $destino_hover = 'uploads/' . uniqid('hover_') . '_' . basename($imagem_hover['name']);

    if (
    !empty($nome) &&
    !empty($categoria) &&
    !empty($tamanho) &&
    !empty($cor) &&
    $preco !== '' &&
    $imagem['error'] === 0 &&
    $imagem_hover['error'] === 0 &&
    move_uploaded_file($imagem['tmp_name'], $destino_img) &&
    move_uploaded_file($imagem_hover['tmp_name'], $destino_hover)
) {

        $dados = [
            'nome' => $nome,
            'categoria' => $categoria,
            'tamanho' => $tamanho,
            'cor' => $cor,
            'preco' => $preco,
            'imagem' => $destino_img,
            'hover' => $destino_hover,
            'quantidade' => 1,
            'keywords' => $keywords
        ];

        $id_produto = inserirProduto($dados);

        if (!empty($_POST['id_marcas'])) {
            foreach ($_POST['id_marcas'] as $marca_id) {
                associarMarcaAoProduto($id_produto, (int)$marca_id);
            }
        }

        header("Location: adicionar_produtos_admin.php?success=1");
        exit;
    } else {
        header("Location: adicionar_produtos_admin.php?error=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Manage Products | PlugVintage Admin</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<div class="sidebar">
    <div class="sidebar-user">
        <h2><?= ucfirst($_SESSION['tipo_utilizador']) ?> - <?= htmlspecialchars($_SESSION['nome_utilizador'] ?? 'Sem nome') ?></h2>
    </div>
    <ul>
        <?php if (in_array($_SESSION['tipo_utilizador'], ['admin', 'worker'])): ?>
            <li><a href="dashboard_admin.php" class="<?= $currentPage === 'dashboard_admin.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="adicionar_produtos_admin.php" class="<?= $currentPage === 'adicionar_produtos_admin.php' ? 'active' : '' ?>">Manage Product</a></li>
            <li><a href="adicionar_marca_admin.php" class="<?= $currentPage === 'adicionar_marca_admin.php' ? 'active' : '' ?>">Manage Brands</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['tipo_utilizador'] === 'admin'): ?>
            <li><a href="gerir_encomendas.php" class="<?= $currentPage === 'gerir_encomendas.php' ? 'active' : '' ?>">Orders</a></li>
            <li><a href="contas_ativas.php" class="<?= $currentPage === 'contas_ativas.php' ? 'active' : '' ?>">Accounts</a></li>
        <?php endif; ?>

        <li><a href="myperfil.php">Back to Profile</a></li>
    </ul>
</div>
<div class="main-content">
    <h1 class="page-title">Add New Product</h1>

    <?php if ($mensagem): ?>
        <div class="message-box">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

 <div class="card product-form">
    <form method="POST" enctype="multipart/form-data" class="form-flex-produto" id="produtoForm">

        <div class="form-group-produto">
            <label>Product Name:</label>
            <input type="text" name="nome_produto">
        </div>

        <div class="form-group-produto">
    <label>Category:</label>
    <div class="dropdown-categorias" onclick="toggleDropdownCategorias(this)">
        <div class="dropdown-button-categorias">SELECT</div>
        <div class="dropdown-list-categorias">
<?php foreach ($categorias as $categoria): ?>


                <label>
                    <input type="radio" name="categoria" value="<?= htmlspecialchars($categoria) ?>">
                    <span><?= htmlspecialchars($categoria) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>


        <div class="form-group-produto">
            <label>Size:</label>
            <input type="text" name="tamanho">
        </div>

        <div class="form-group-produto">
            <label>Color:</label>
            <input type="text" name="cor">
        </div>

        <div class="form-group-produto">
            <label>Price:</label>
            <input type="number" name="preco" step="0.01" class="input-preco">
        </div>

        <div class="form-group-produto">
            <label>Keywords:</label>
            <input type="text" name="keywords">
        </div>

        <div class="form-group-produto">
            <label>Brands:</label>
            <div class="dropdown-marcas" onclick="toggleDropdownMarcas(this)">
                <div class="dropdown-button-marcas" id="selectedBrands">SELECT</div>

                <div class="dropdown-list-marcas">
                    <?php foreach ($marcas as $marca): ?>
                        <label>
                            <input type="checkbox" name="id_marcas[]" value="<?= $marca['id_marca'] ?>">
                            <span><?= htmlspecialchars($marca['nome_marca']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-group-produto">
            <label>Main Image:</label>
            <label class="custom-file-upload">
                <input type="file" name="caminho_imagem" accept="image/*" onchange="updateFileName(this, 'main-chosen')">
                Choose file
            </label>
            <span id="main-chosen" class="file-name-label">No file chosen</span>
        </div>

        <div class="form-group-produto">
            <label>Hover Image:</label>
            <label class="custom-file-upload">
                <input type="file" name="caminho_imagem_hover" accept="image/*" onchange="updateFileName(this, 'hover-chosen')">
                Choose file
            </label>
            <span id="hover-chosen" class="file-name-label">No file chosen</span>
        </div>

        <button type="submit" class="btn-verde">Add Product</button>
    </form>
</div>


    <h2>Available Products</h2>
    <table>
    <thead>
    <tr>
        <th>Image</th>
        <th>Name</th>
        <th>Category</th> 
        <th>Price</th>
        <th>Brand</th>
        <th>Actions</th>
    </tr>
</thead>


        <tbody>
        <?php foreach ($produtos as $p): ?>
            <tr>
                <td><img src="<?= $p['caminho_imagem'] ?>" alt="img" style="height:40px;"></td>
                <td><?= htmlspecialchars($p['nome_produto']) ?></td>
<td><?= htmlspecialchars($p['categoria']) ?></td>
<td><?= number_format($p['preco'], 2, ',', '.') ?> €</td>
<td><?= $p['nome_marcas'] ?? 'Sem Marca' ?></td>


<td>
    <?php if ($_SESSION['tipo_utilizador'] === 'admin'): ?>
        <a href="editar_produto_admin.php?id=<?= $p['id_produtos'] ?>" class="btn-edit">Edit</a>
        <a href="adicionar_imagens_produto.php?id=<?= $p['id_produtos'] ?>" class="btn-imagens">Manage Images</a>


        <a href="#" class="btn-delete" onclick="abrirModal('apagar_produto_admin.php?id=<?= $p['id_produtos'] ?>')">Delete</a>
    <?php else: ?>
        <span style="color: #999;">No permissions</span>
    <?php endif; ?>
</td>

            </tr>
        <?php endforeach; ?>
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

<script>
function updateFileName(input, spanId) {
    const fileName = input.files[0]?.name || "No file chosen";
    document.getElementById(spanId).textContent = fileName;
}
</script>




<script>
// Alternar visibilidade do dropdown
function toggleDropdownMarcas(el) {
    const list = el.querySelector(".dropdown-list-marcas");
    list.style.display = list.style.display === "block" ? "none" : "block";
}

// Atualizar o texto com marcas selecionadas
document.querySelectorAll(".dropdown-list-marcas input[type='checkbox']").forEach(function(checkbox) {
    checkbox.addEventListener("change", function () {
        const dropdown = this.closest(".dropdown-marcas");
        const button = dropdown.querySelector(".dropdown-button-marcas");
        const selected = Array.from(dropdown.querySelectorAll("input[type='checkbox']:checked"))
            .map(cb => cb.nextElementSibling.textContent.trim())
            .join(" x ") || "SELECT";
        button.textContent = selected;
    });

    // Impede que o clique no checkbox feche o dropdown
    checkbox.addEventListener("click", function(e) {
        e.stopPropagation();
    });
});

// Impede que o clique em qualquer label interna feche o dropdown
document.querySelectorAll(".dropdown-list-marcas label").forEach(function(label) {
    label.addEventListener("click", function(e) {
        e.stopPropagation();
    });
});

// Fecha dropdown se clicar fora
window.addEventListener("click", function(e) {
    const dropdown = document.querySelector(".dropdown-marcas");
    const list = dropdown.querySelector(".dropdown-list-marcas");
    if (!dropdown.contains(e.target)) {
        list.style.display = "none";
    }
});
</script>






<script>
function toggleDropdownMarcas(el) {
    const list = el.querySelector(".dropdown-list-marcas");
    list.style.display = list.style.display === "block" ? "none" : "block";
}

function toggleDropdownCategorias(el) {
    el.classList.toggle('open');
}

document.addEventListener('click', function (event) {
    document.querySelectorAll('.dropdown-categorias').forEach(drop => {
        if (!drop.contains(event.target)) drop.classList.remove('open');
    });
});

document.querySelectorAll('.dropdown-categorias').forEach(dropdown => {
    const radios = dropdown.querySelectorAll('input[type="radio"]');
    const button = dropdown.querySelector('.dropdown-button-categorias');

    radios.forEach(rb => {
        rb.addEventListener('mousedown', (e) => {
            if (rb.checked) {
                e.preventDefault();
                setTimeout(() => {
                    rb.checked = false;
                    button.textContent = "SELECT";
                    dropdown.classList.remove('open');
                }, 0);
            } else {
                rb.addEventListener('change', () => {
                    if (rb.checked) {
                        button.textContent = rb.nextElementSibling.textContent.trim();
                        dropdown.classList.remove('open');
                    }
                }, { once: true });
            }
        });
    });
});

//  Novo bloco para permitir selecionar a categoria clicando em todo o label
document.querySelectorAll('.dropdown-list-categorias label').forEach(label => {
    label.addEventListener('click', function (e) {
        const radio = this.querySelector('input[type="radio"]');
        if (!radio.checked) {
            radio.checked = true;
            const dropdown = this.closest('.dropdown-categorias');
            const button = dropdown.querySelector('.dropdown-button-categorias');
            button.textContent = this.querySelector('span').textContent.trim();
            dropdown.classList.remove('open');
        }
    });
});

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
    const fileName = input.files[0]?.name || "Nenhum ficheiro selecionado";
    document.getElementById(spanId).textContent = fileName;
}
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function abrirModal(link) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This product will be permanently deleted.",
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
</script>





<script>
const form = document.getElementById("produtoForm");
form.addEventListener("submit", function(e) {
    const nome = document.querySelector("input[name='nome_produto']").value.trim();
    const categoria = document.querySelector("input[name='categoria']:checked");
    const tamanho = document.querySelector("input[name='tamanho']").value.trim();
    const cor = document.querySelector("input[name='cor']").value.trim();
    const preco = document.querySelector("input[name='preco']").value.trim();
    const imagem = document.querySelector("input[name='caminho_imagem']").files.length;
    const imagemHover = document.querySelector("input[name='caminho_imagem_hover']").files.length;

    // Remover mensagem anterior se existir
    const antiga = document.querySelector(".message-box");
    if (antiga) antiga.remove();

    // Se faltar algum campo
    if (!nome || !categoria || !tamanho || !cor || !preco || !imagem || !imagemHover) {
        e.preventDefault();

        const div = document.createElement("div");
        div.className = "message-box";
        div.style.backgroundColor = "#ffe6e6";
        div.style.border = "1px solid #ff4d4d";
        div.style.color = "#b30000";
        div.style.marginBottom = "20px";
        div.style.marginTop = "10px";
        div.textContent = " Please fill in all required fields before submitting.";

        const title = document.querySelector("h1.page-title");
        title.insertAdjacentElement("afterend", div);
    }
});
</script>


</body>
</html>
