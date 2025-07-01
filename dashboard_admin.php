<?php
session_start();
include("config.php");

if (!isset($_SESSION['tipo_utilizador']) || !in_array($_SESSION['tipo_utilizador'], ['admin', 'worker'])) {
    header("Location: myperfil.php");
    exit();
}

// Totais
$totalEncomendas = contarTotalEncomendas();
$totalClientes = contarTotalClientes();
$totalPago = contarEncomendasPorEstado('Paid');
$totalPendente = contarEncomendasPorEstado('Pending');
$totalCancelado = contarEncomendasPorEstado('Canceled');
$receitaTotal = (float) somarTotalReceita();
$ultimasEncomendas = buscarUltimasEncomendas();

// Receita mensal (últimos 6 meses)
$dadosReceitaMensal = [];
for ($i = 0; $i < 6; $i++) {
    $mes = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT SUM(total) as receita FROM encomendas 
            WHERE estado = 'Paid' AND DATE_FORMAT(data_encomenda, '%Y-%m') = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("s", $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $dadosReceitaMensal[$mes] = $res['receita'] ?? 0;
}
$dadosReceitaMensal = array_reverse($dadosReceitaMensal, true);

// Novos clientes (últimos 6 meses)
$dadosUtilizadoresMensal = [];
for ($i = 0; $i < 6; $i++) {
    $mes = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as total FROM utilizadores 
            WHERE LOWER(tipo_utilizador) = 'user' AND DATE_FORMAT(data_criacao, '%Y-%m') = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("s", $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $dadosUtilizadoresMensal[$mes] = $res['total'] ?? 0;
}
$dadosUtilizadoresMensal = array_reverse($dadosUtilizadoresMensal, true);

$currentPage = basename(__FILE__);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | PlugVintage</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <h1>Admin Dashboard</h1>

    <div class="dashboard-cards">
        <div class="dashboard-card"><h2><?= $totalEncomendas ?></h2><p>Total Orders</p></div>
        <div class="dashboard-card"><h2><?= $totalClientes ?></h2><p>Total Customers</p></div>
        <div class="dashboard-card"><h2><?= number_format($receitaTotal, 2, ',', '.') ?> €</h2><p>Total Revenue</p></div>
        <div class="dashboard-card"><h2><?= $totalPago ?></h2><p>Paid</p></div>
        <div class="dashboard-card"><h2><?= $totalCancelado ?></h2><p>Canceled</p></div>
    </div>

    <h2>Statistics</h2>
    <div class="charts-container">
        <div class="chart-box">
            <h3>Order Status</h3>
            <canvas id="ordersChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Revenue Last 6 Months</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>New Customers Last 6 Months</h3>
            <canvas id="customersChart"></canvas>
        </div>
    </div>

    <h2>Latest Orders</h2>
    <table>
        <thead><tr><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($ultimasEncomendas as $enc): ?>
            <tr>
                
                <td><?= htmlspecialchars($enc['nome_utilizador']) ?></td>
                <td><?= number_format($enc['total'], 2, ',', '.') ?> €</td>
                <td><?= ucfirst($enc['estado']) ?></td>
                <td><?= date('d/m/Y', strtotime($enc['data_encomenda'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Gráfico de encomendas por estado
new Chart(document.getElementById('ordersChart'), {
    type: 'pie',
    data: {
        labels: ['Paid', 'Pending', 'Canceled'],
        datasets: [{
            data: [<?= $totalPago ?>, <?= $totalPendente ?>, <?= $totalCancelado ?>],
            backgroundColor: ['#4caf50', '#ff9800', '#f44336']
        }]
    }
});

// Receita por mês
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($dadosReceitaMensal)) ?>,
        datasets: [{
            label: '€',
            data: <?= json_encode(array_values($dadosReceitaMensal)) ?>,
            backgroundColor: '#2196f3'
        }]
    },
    options: {scales: {y: {beginAtZero: true}}}
});

// Novos clientes por mês
new Chart(document.getElementById('customersChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($dadosUtilizadoresMensal)) ?>,
        datasets: [{
            label: 'New Customers',
            data: <?= json_encode(array_values($dadosUtilizadoresMensal)) ?>,
            fill: false,
            borderColor: '#673ab7',
            tension: 0.3
        }]
    },
    options: {scales: {y: {beginAtZero: true}}}
});
</script>

</body>
</html>
