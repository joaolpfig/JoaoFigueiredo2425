<?php
session_start();
include("config.php");

// Verifica se o utilizador é admin
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header("Location: acesso_negado.php");
    exit();
}

// Buscar todos os utilizadores
$utilizadores = mysqli_query($liga, "SELECT id_utilizador, nome_utilizador, email, tipo_utilizador, data_criacao FROM utilizadores");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active Accounts - PlugVintage</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-user">
            <h2>Admin - <?= htmlspecialchars($_SESSION['nome_utilizador'] ?? 'Sem nome') ?></h2>
        </div>
        <ul>
            <li><a href="dashboard_admin.php">Dashboard</a></li>
            <li><a href="adicionar_produtos_admin.php">Manage Product</a></li>
            <li><a href="adicionar_marca_admin.php">Manage Brands</a></li>
            <li><a href="gerir_encomendas.php">Orders</a></li>
            <li><a href="contas_ativas.php" class="active">Accounts</a></li>
            <li><a href="myperfil.php">Back to Profile</a></li>
        </ul>
    </div>

    <div class="dashboard">
        <h1>User Accounts</h1>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Account Type</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($utilizadores)): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['nome_utilizador']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($_SESSION['id_utilizador'] != $user['id_utilizador']): ?>
                                <select 
                                    data-id="<?= $user['id_utilizador']; ?>" 
                                    onfocus="guardarValorAnterior(this)" 
                                    onchange="confirmarMudancaTipo(this)">
                                    <option value="user" <?= $user['tipo_utilizador'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="worker" <?= $user['tipo_utilizador'] === 'worker' ? 'selected' : '' ?>>Worker</option>
                                    <option value="admin" <?= $user['tipo_utilizador'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            <?php else: ?>
                                <strong><?= ucfirst($user['tipo_utilizador']); ?> (You)</strong>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($user['data_criacao'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        function guardarValorAnterior(el) {
            el.setAttribute("data-valor-anterior", el.value);
        }

        function confirmarMudancaTipo(selectElement) {
            const id = selectElement.dataset.id;
            const tipoNovo = selectElement.value;
            const tipoAntigo = selectElement.getAttribute("data-valor-anterior");

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to change the type of this account?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('atualizar_tipo.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id_utilizador=${id}&novo_tipo=${tipoNovo}`
                    })
                    .then(res => res.text())
                    .then(msg => Swal.fire('Sucess', msg, 'success'))
                    .catch(() => Swal.fire('Error', 'Error updating.', 'error'));
                } else {
                    selectElement.value = tipoAntigo;
                }
            });
        }
    </script>
</body>
</html>
