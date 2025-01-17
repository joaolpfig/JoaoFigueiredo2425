<?php
include 'config.php'; // Inclui o ficheiro de configuração

// Verificar se a ligação foi bem-sucedida
if (!$liga) {
    die("Erro ao conectar à base de dados: " . mysqli_connect_error());
}

$query = $_GET['query'] ?? ''; // Obtém o termo de pesquisa
$response = [];

if (!empty($query)) {
    // Prepara a consulta
    $stmt = mysqli_prepare($liga, "SELECT id_produtos, nome_produto, caminho_imagem FROM produtos WHERE nome_produto LIKE ? LIMIT 5");
    $searchTerm = "%$query%";
    mysqli_stmt_bind_param($stmt, "s", $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Adiciona os resultados ao array
    while ($row = mysqli_fetch_assoc($result)) {
        $response[] = $row;
    }
}

// Retorna os resultados como JSON
header('Content-Type: application/json');
// Apenas para testes, remove o comentário abaixo se quiser visualizar no navegador
// print_r($response); // Comentado para não interferir na resposta JSON
echo json_encode($response);
?>
