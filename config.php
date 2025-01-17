<?php

$host = 'localhost';
$user = 'root';  // Nome de utilizador 
$password = '';  // Senha
$dbname = 'loja_roupa';  // Nome da base de dados

// Conectar à base de dados
$liga = mysqli_connect($host, $user, $password, $dbname);


// Verificar se a conexão foi bem-sucedida
if (!$liga) {
    echo ("Erro na conexão à base de dados: " . mysqli_connect_error());
}

// Função para logar o utilizador
function logarUtilizador($username, $password)
{
    global $liga;  // Usa a conexão estabelecida à base de dados 
    $query = "SELECT * FROM utilizador WHERE username = '$username' AND password = '$password'";
    return mysqli_query($liga, $query);
}

function listarProdutos($pagina_atual, $produtos_por_pagina) {
    global $liga;

    $offset = ($pagina_atual - 1) * $produtos_por_pagina;

    // Query SQL com LIMIT e OFFSET
    $sql = "SELECT 
                p.id_produtos,
                p.nome_produto, 
                p.preco,
                p.caminho_imagem, 
                p.caminho_imagem_hover,
                COALESCE(GROUP_CONCAT(m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
            FROM 
                produtos p
            LEFT JOIN 
                produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN 
                marcas m ON pm.id_marcas = m.id_marca
            GROUP BY 
                p.id_produtos, p.nome_produto, p.preco, p.caminho_imagem, p.caminho_imagem_hover
            LIMIT $produtos_por_pagina OFFSET $offset";

    // Executa a query
    $result = mysqli_query($liga, $sql);
    $produtos = [];

    // Verifica os resultados
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $produtos[] = $row;
        }
    }

    return $produtos;
}





    function buscarProdutosPorId($idProduto) {
        global $liga;
    
        // Query principal para obter detalhes do produto
        $sql = "SELECT
                    p.id_produtos,
                    p.nome_produto,
                    p.preco AS preco,
                    p.categoria AS categorie,
                    p.tamanho AS size,
                    p.cor AS color,
                    p.caminho_imagem AS caminho_imagem,
                    (SELECT GROUP_CONCAT(m.nome_marca SEPARATOR ' x ')
                     FROM produtos_marcas pm
                     JOIN marcas m ON pm.id_marcas = m.id_marca
                     WHERE pm.id_produto = p.id_produtos) AS brand
                FROM 
                    produtos AS p
                WHERE 
                    p.id_produtos = ?";
    
        // Preparar a query
        $stmt = mysqli_prepare($liga, $sql);
    
        if (!$stmt) {
            die('Erro ao preparar a query: ' . mysqli_error($liga));
        }
    
        // Associar o parâmetro
        mysqli_stmt_bind_param($stmt, 'i', $idProduto);
    
        // Executar a query
        mysqli_stmt_execute($stmt);
    
        // Obter o resultado
        $result = mysqli_stmt_get_result($stmt);
    
        // Verificar se há resultados
        if ($result && mysqli_num_rows($result) > 0) {
            // Retornar o primeiro resultado
            return mysqli_fetch_assoc($result);
        } else {
            // Retornar uma mensagem ou um array vazio caso não haja resultados
            return null;
        }
    }
     


function buscarImagensProduto($idProduto) {
    global $liga;

    $sql = "SELECT imagens 
            FROM imagens
            WHERE id_produtos = ?";

    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idProduto);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $imagens = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $imagens[] = $row['imagens'];
    }

    return $imagens;
}


// Função para buscar produtos por nome ou parte do nome
function buscarProdutosPorNome($liga, $query, $produtos_por_pagina, $offset) {
    $sql = "
    SELECT 
        p.id_produtos, 
        p.nome_produto, 
        p.caminho_imagem, 
        p.caminho_imagem_hover, 
        p.preco,
        COALESCE(GROUP_CONCAT(m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
    FROM 
        produtos p
    LEFT JOIN 
        produtos_marcas pm ON p.id_produtos = pm.id_produto
    LEFT JOIN 
        marcas m ON pm.id_marcas = m.id_marca
    WHERE p.nome_produto LIKE ?
    GROUP BY 
        p.id_produtos, 
        p.nome_produto, 
        p.caminho_imagem, 
        p.caminho_imagem_hover, 
        p.preco
    LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($liga, $sql);
    $searchTerm = "%$query%";
    mysqli_stmt_bind_param($stmt, "sii", $searchTerm, $produtos_por_pagina, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $produtos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $produtos[] = $row;
    }

    return $produtos;
}


// Função para listar os produtos filtrados por categoria
// Função para listar os produtos filtrados por categoria
function listarProdutosPorCategoria($categoria, $pagina_atual, $produtos_por_pagina) {
    global $liga; // Certifique-se de que a conexão está correta

    // Escapar a categoria para evitar problemas de SQL injection
    $categoria = mysqli_real_escape_string($liga, $categoria);

    $offset = ($pagina_atual - 1) * $produtos_por_pagina;

    // Consulta SQL com JOIN para incluir a marca
    $query = "SELECT p.id_produtos, p.nome_produto, p.caminho_imagem, p.caminho_imagem_hover, 
                 GROUP_CONCAT(m.nome_marca SEPARATOR ', ') AS nome_marca, p.preco
          FROM produtos p
          LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
          LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
          WHERE p.categoria = '$categoria'
          GROUP BY p.id_produtos
          LIMIT $offset, $produtos_por_pagina";




    $resultado = mysqli_query($liga, $query);

    // Verifique se a consulta foi bem-sucedida
    if (!$resultado) {
        die('Erro na consulta SQL: ' . mysqli_error($liga)); // Exibe um erro se a consulta falhar
    }

    // Retorna os produtos
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}




function buscarProdutosPorMarca($liga, $marca_id, $produtos_por_pagina, $offset) {
    $sql = "
    SELECT 
        p.id_produtos,
        p.nome_produto, 
        p.preco,
        p.caminho_imagem, 
        p.caminho_imagem_hover,
        COALESCE(GROUP_CONCAT(m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
    FROM 
        produtos p
    LEFT JOIN 
        produtos_marcas pm ON p.id_produtos = pm.id_produto
    LEFT JOIN 
        marcas m ON pm.id_marcas = m.id_marca
    WHERE pm.id_marcas = ?
    GROUP BY 
        p.id_produtos, 
        p.nome_produto, 
        p.preco, 
        p.caminho_imagem, 
        p.caminho_imagem_hover
    LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $marca_id, $produtos_por_pagina, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $produtos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $produtos[] = $row;
    }

    return $produtos;
}
