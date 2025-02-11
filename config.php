<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ativar a exibição de erros (NÃO DESATIVAR)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Configuração da conexão com a base de dados
$host = 'localhost'; // Host do banco de dados
$user = 'root';  // Nome de utilizador
$password = '';  // Senha do banco de dados
$dbname = 'loja_roupa';  // Nome da base de dados

// Conectar à base de dados
$liga = mysqli_connect($host, $user, $password, $dbname);

// Verificar se a conexão foi bem-sucedida
if (!$liga) {
    die("Erro na conexão à base de dados: " . mysqli_connect_error());
} else {
    echo "Conexão bem-sucedida!";
}

// Função para logar o utilizador
function logarUtilizador($username, $password)
{
    global $liga;  // Usa a conexão estabelecida à base de dados
    $query = "SELECT * FROM utilizador WHERE username = '$username' AND password = '$password'"; // Consulta SQL para autenticação
    return mysqli_query($liga, $query); // Retorna o resultado da consulta
}

// Função para listar produtos com paginação
function listarProdutos($pagina_atual, $produtos_por_pagina) {
    global $liga; // Conexão global com o banco de dados

    $offset = ($pagina_atual - 1) * $produtos_por_pagina; // Calcula o offset para a paginação

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
            $produtos[] = $row; // Adiciona os resultados ao array
        }
    }

    return $produtos; // Retorna o array de produtos
}

// Função para buscar detalhes de um produto por ID
function buscarProdutosPorId($idProduto) {
    global $liga;

    $sql = "SELECT
                p.id_produtos,
                p.nome_produto,
                p.preco AS preco,
                p.categoria AS categorie,
                p.tamanho AS size,
                p.cor AS color,
                p.caminho_imagem AS caminho_imagem,
                p.quantidade AS quantidade,
                (SELECT GROUP_CONCAT(m.nome_marca SEPARATOR ' x ')
                 FROM produtos_marcas pm
                 JOIN marcas m ON pm.id_marcas = m.id_marca
                 WHERE pm.id_produto = p.id_produtos) AS brand
            FROM 
                produtos AS p
            WHERE 
                p.id_produtos = ?";

    $stmt = mysqli_prepare($liga, $sql);
    if (!$stmt) {
        die('Erro ao preparar a query: ' . mysqli_error($liga));
    }

    mysqli_stmt_bind_param($stmt, 'i', $idProduto);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        return null;
    }
}


// Função para buscar imagens relacionadas a um produto
function buscarImagensProduto($idProduto) {
    global $liga; // Conexão global

    // Query para buscar imagens
    $sql = "SELECT imagens 
            FROM imagens
            WHERE id_produtos = ?";

    $stmt = mysqli_prepare($liga, $sql); // Prepara a query
    mysqli_stmt_bind_param($stmt, 'i', $idProduto); // Associa o parâmetro
    mysqli_stmt_execute($stmt); // Executa a query
    $result = mysqli_stmt_get_result($stmt); // Obtém o resultado

    $imagens = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $imagens[] = $row['imagens']; // Adiciona as imagens ao array
    }

    return $imagens; // Retorna o array de imagens
}

// Função para buscar produtos por nome ou parte do nome
function buscarProdutosPorNome($liga, $query, $produtos_por_pagina, $offset) {
    // Query para buscar produtos pelo nome
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

    $stmt = mysqli_prepare($liga, $sql); // Prepara a query
    $searchTerm = "%$query%"; // Adiciona os caracteres wildcard
    mysqli_stmt_bind_param($stmt, "sii", $searchTerm, $produtos_por_pagina, $offset); // Associa os parâmetros
    mysqli_stmt_execute($stmt); // Executa a query
    $result = mysqli_stmt_get_result($stmt); // Obtém o resultado

    $produtos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $produtos[] = $row; // Adiciona os produtos ao array
    }

    return $produtos; // Retorna o array de produtos
}

// Função para listar produtos por categoria
function listarProdutosPorCategoria($categoria, $pagina_atual, $produtos_por_pagina) {
    global $liga; // Conexão global

    $categoria = mysqli_real_escape_string($liga, $categoria); // Escapa a categoria para evitar SQL injection
    $offset = ($pagina_atual - 1) * $produtos_por_pagina; // Calcula o offset

    // Query para listar produtos por categoria
    $query = "SELECT 
                p.id_produtos, 
                p.nome_produto, 
                p.caminho_imagem, 
                p.caminho_imagem_hover, 
                GROUP_CONCAT(m.nome_marca SEPARATOR ', ') AS nome_marca, 
                p.preco
              FROM produtos p
              LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
              LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
              WHERE p.categoria = '$categoria'
              GROUP BY p.id_produtos
              LIMIT $offset, $produtos_por_pagina";

    $resultado = mysqli_query($liga, $query); // Executa a query

    if (!$resultado) {
        die('Erro na consulta SQL: ' . mysqli_error($liga)); // Exibe erro caso a query falhe
    }

    return mysqli_fetch_all($resultado, MYSQLI_ASSOC); // Retorna o resultado
}




// Função para buscar produtos por marca
function buscarProdutosPorMarca($liga, $marca_id, $produtos_por_pagina, $offset) {
    // Query para buscar produtos por marca
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

    $stmt = mysqli_prepare($liga, $sql); // Prepara a query
    mysqli_stmt_bind_param($stmt, "iii", $marca_id, $produtos_por_pagina, $offset); // Associa os parâmetros
    mysqli_stmt_execute($stmt); // Executa a query
    $result = mysqli_stmt_get_result($stmt); // Obtém o resultado

    $produtos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $produtos[] = $row; // Adiciona os produtos ao array
    }

    return $produtos; // Retorna o array de produtos
}





// Função para buscar todas as marcas e contar os produtos associados
function buscarMarcasComProdutos($liga) {
    $sql = "
        SELECT 
            m.id_marca, 
            m.nome_marca, 
            m.caminho_imagem_brand,
            COUNT(pm.id_produto) AS total_produtos
        FROM 
            marcas m
        LEFT JOIN 
            produtos_marcas pm ON m.id_marca = pm.id_marcas
        GROUP BY 
            m.id_marca, m.nome_marca, m.caminho_imagem_brand
        ORDER BY 
            m.nome_marca ASC";
    
    $resultado = mysqli_query($liga, $sql);
    
    if (!$resultado) {
        die('Erro ao obter marcas: ' . mysqli_error($liga));
    }
    
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}





// Função para buscar produtos no carrinho
function buscarProdutosCarrinho($idUtilizador, $conn) {
    $sql = "SELECT 
                c.id_carrinho, 
                p.nome_produto, 
                p.preco, 
                p.tamanho, 
                p.categoria, 
                COALESCE(GROUP_CONCAT(m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marca, 
                c.quantidade 
            FROM carrinho c
            JOIN produtos p ON c.id_produtos = p.id_produtos
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE c.id_utilizador = ?
            GROUP BY c.id_carrinho, c.quantidade, p.nome_produto, p.preco, p.tamanho, p.categoria";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idUtilizador); // Associa o parâmetro
    $stmt->execute(); // Executa o statement
    $result = $stmt->get_result(); // Obtém o resultado da query

    $produtos = [];
    while ($row = $result->fetch_assoc()) { // Usa fetch_assoc para obter os resultados
        $produtos[] = $row;
    }

    return $produtos; // Retorna os produtos como um array
}



// Função para adicionar produto ao carrinho
function adicionarAoCarrinho($idUtilizador, $idProduto, $conn) {
    $sqlStock = "SELECT quantidade FROM produtos WHERE id_produtos = ?";
    $stmtStock = $conn->prepare($sqlStock);
    $stmtStock->bind_param('i', $idProduto);
    $stmtStock->execute();
    $resultadoStock = $stmtStock->get_result();
    $produto = $resultadoStock->fetch_assoc();

    if ($produto && $produto['quantidade'] > 0) {
        $sqlInsert = "INSERT INTO carrinho (id_utilizador, id_produtos, quantidade, data_adicionado) VALUES (?, ?, 1, NOW())";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param('ii', $idUtilizador, $idProduto);
        $stmtInsert->execute();

        $sqlUpdateStock = "UPDATE produtos SET quantidade = quantidade - 1 WHERE id_produtos = ?";
        $stmtUpdateStock = $conn->prepare($sqlUpdateStock);
        $stmtUpdateStock->bind_param('i', $idProduto);
        $stmtUpdateStock->execute();

        return true;
    }
    return false;
}






function calcularTotalCarrinho($produtosCarrinho) {
    $total = 0;
    foreach ($produtosCarrinho as $produto) {
        $total += $produto['preco'] * $produto['quantidade'];
    }
    return $total;
}




// Função para remover produto do carrinho
function removerProdutoCarrinho($idCarrinho, $liga) {
    // Prepara a query para deletar o produto do carrinho
    $sql = "DELETE FROM carrinho WHERE id_carrinho = ?";
    $stmt = mysqli_prepare($liga, $sql);
    if (!$stmt) {
        die("Erro ao preparar a query: " . mysqli_error($liga));
    }

    // Associa o parâmetro e executa
    mysqli_stmt_bind_param($stmt, 'i', $idCarrinho);
    return mysqli_stmt_execute($stmt); // Executa e retorna o resultado
}

