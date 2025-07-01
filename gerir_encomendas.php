<?php
// gerir_encomendas.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "config.php";

if (!isset($_SESSION['tipo_utilizador']) || !in_array($_SESSION['tipo_utilizador'], ['admin', 'worker'])) {
    header("Location: access_denied.php");
    exit();
}


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = (isset($_GET['status']) && $_GET['status'] !== 'all') ? $_GET['status'] : '';

$limit = 10;

$orders = listarEncomendas($status, $page, $limit);
$totalOrders = contarEncomendas($status);
$totalPages = ceil($totalOrders / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-user">
        <h2><?= ucfirst($_SESSION['tipo_utilizador']) ?> - <?= htmlspecialchars($_SESSION['nome_utilizador'] ?? 'No name') ?></h2>
    </div>
    <ul>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="adicionar_produtos_admin.php">Manage Product</a></li>
        <li><a href="adicionar_marca_admin.php">Manage Brands</a></li>
        <li><a href="gerir_encomendas.php" class="active">Orders</a></li>
        <?php if ($_SESSION['tipo_utilizador'] === 'admin'): ?>
            <li><a href="contas_ativas.php">Accounts</a></li>
        <?php endif; ?>
        <li><a href="myperfil.php">Back to Profile</a></li>
    </ul>
</div>

<div class="main-content">
    <h1 class="page-title">Manage Orders</h1>

    <form method="GET" style="margin-bottom: 20px;">
        <label>Filter by Status:</label>
        <select name="status" onchange="this.form.submit()">
            <option value="">All</option>
            <?php foreach (["Pending", "Paid", "Sent", "Delivered", "Canceled"] as $estado): ?>
                <option value="<?= $estado ?>" <?= $status == $estado ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id_encomenda'] ?></td>
                <td><?= htmlspecialchars($order['nome_utilizador']) ?></td>
                <td><?= $order['data_encomenda'] ?></td>
                <td>€<?= number_format($order['total'], 2, ',', '.') ?></td>
                <td class="status-<?= strtolower($order['estado'] ?? 'unknown') ?>">
    <?= ucfirst($order['estado'] ?? 'Unknown') ?>
</td>


                <td>
  <a href="detalhes_encomenda.php?id=<?= $order['id_encomenda']; ?>" class="btn-edit">Details</a>
  <?php if ($_SESSION['tipo_utilizador'] === 'admin' && $order['estado'] !== 'canceled'): ?>

      <a href="#" class="btn-delete" onclick="abrirModalCancelar('cancelar_encomenda.php?id=<?= $order['id_encomenda']; ?>')">Cancel</a>
  <?php endif; ?>
</td>





            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination-admin">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= $status ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>








<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function abrirModalCancelar(link) {
    Swal.fire({
        title: 'Confirm cancellation',
        text: "This order will be cancelled and the customer will be notified.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'Back'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = link;
        }
    });
}
</script>



<?php if (isset($_GET['cancel']) && $_GET['cancel'] === 'success'): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Order cancelled',
    text: 'The order was successfully cancelled and the customer was notified.',
    timer: 3000,
    showConfirmButton: false
});
</script>
<?php endif; ?>




</body>
</html>
