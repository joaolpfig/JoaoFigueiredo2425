<?php
include 'config.php'; // Inclui a ligação à base de dados

if (!$liga) {
    die("Erro ao conectar à base de dados: " . mysqli_connect_error());
}

$query = $_GET['query'] ?? '';
$response = [];

if (!empty($query)) {
    // Prepara os termos para o modo booleano do FULLTEXT (+nike +tn)
    $fulltextSearch = "+" . implode(" +", explode(" ", $query));
    $likeSearch = "%" . $query . "%";

    // Tenta primeiro com FULLTEXT, se falhar pode usar LIKE como fallback (se quiseres)
    $stmt = mysqli_prepare($liga, "
        SELECT id_produtos, nome_produto, caminho_imagem, quantidade 
        FROM produtos 
        WHERE MATCH(nome_produto, keywords) AGAINST (? IN BOOLEAN MODE) 
        LIMIT 5
    ");

    mysqli_stmt_bind_param($stmt, "s", $fulltextSearch);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $response[] = $row;
    }

    // Fallback: se não encontrou nada, tenta com LIKE
    if (empty($response)) {
        $stmt = mysqli_prepare($liga, "
            SELECT id_produtos, nome_produto, caminho_imagem, quantidade 
            FROM produtos 
            WHERE nome_produto LIKE ? 
            LIMIT 5
        ");
        mysqli_stmt_bind_param($stmt, "s", $likeSearch);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
