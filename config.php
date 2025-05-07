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
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Verificar se a conexão foi bem-sucedida
if (!$liga) {
    die("Erro na conexão à base de dados: " . mysqli_connect_error());
}

// Função para logar o utilizador
function logarUtilizador($email, $senha)
{
    global $liga; // Conexão com o banco de dados

    // Prepara a consulta para evitar SQL Injection
    $sql = "SELECT id_utilizador, nome_utilizador, senha, tipo_utilizador FROM utilizador WHERE email = ?";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
        // Verifica se a senha está correta
        if (password_verify($senha, $row['senha'])) {
            // Inicia sessão e guarda os dados do utilizador
            session_start();
            $_SESSION['id_utilizador'] = $row['id_utilizador'];
            $_SESSION['nome_utilizador'] = $row['nome_utilizador'];
            $_SESSION['tipo_utilizador'] = $row['tipo_utilizador'];

            return true; // Login bem-sucedido
        } else {
            return false; // Senha incorreta
        }
    } else {
        return false; // Email não encontrado
    }
}


// Função para listar produtos com paginação
function listarProdutos($pagina_atual, $produtos_por_pagina)
{
    global $liga; // Conexão global com o banco de dados

    $offset = ($pagina_atual - 1) * $produtos_por_pagina; // Calcula o offset para a paginação

    // Query SQL com LIMIT e OFFSET
    $sql = "SELECT 
                p.id_produtos,
                p.nome_produto, 
                p.preco,
                p.caminho_imagem, 
                p.caminho_imagem_hover,
                p.quantidade, -- Adicionando a quantidade aqui
                COALESCE(GROUP_CONCAT(m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
            FROM 
                produtos p
            LEFT JOIN 
                produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN 
                marcas m ON pm.id_marcas = m.id_marca
            GROUP BY 
                p.id_produtos, p.nome_produto, p.preco, p.caminho_imagem, p.caminho_imagem_hover, p.quantidade -- Adicionado p.quantidade ao GROUP BY
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
function buscarProdutosPorId($idProduto)
{
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
function buscarImagensProduto($idProduto)
{
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
function buscarProdutosPorNome($liga, $query, $produtos_por_pagina, $offset)
{
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
function listarProdutosPorCategoria($categoria, $pagina_atual, $produtos_por_pagina)
{
    global $liga;

    $categoria = mysqli_real_escape_string($liga, $categoria);
    $offset = ($pagina_atual - 1) * $produtos_por_pagina;

    // ✅ Query corrigida para incluir a marca corretamente
    $query = "SELECT 
                p.id_produtos, 
                p.nome_produto, 
                p.caminho_imagem, 
                p.caminho_imagem_hover, 
                p.preco,
                p.quantidade,
                COALESCE(GROUP_CONCAT(DISTINCT m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
          FROM produtos p
          LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
          LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
          WHERE p.categoria = '$categoria'
          GROUP BY p.id_produtos";



    $resultado = mysqli_query($liga, $query);

    if (!$resultado) {
        die('Erro na consulta SQL: ' . mysqli_error($liga));
    }

    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}





// Função para buscar produtos por marca
function buscarProdutosPorMarca($liga, $marca_id, $produtos_por_pagina, $offset)
{
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
function buscarMarcasComProdutos($liga)
{
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




// 🔹 Função para buscar produtos no carrinho
function adicionarAoCarrinho($idUtilizador, $idProduto, $conn)
{
    // Verifica se o produto existe e se está disponível no estoque
    $sqlStock = "SELECT quantidade FROM produtos WHERE id_produtos = ?";
    $stmtStock = $conn->prepare($sqlStock);
    $stmtStock->bind_param('i', $idProduto);
    $stmtStock->execute();
    $resultadoStock = $stmtStock->get_result();
    $produto = $resultadoStock->fetch_assoc();

    if (!$produto || $produto['quantidade'] == 0) {
        return false; // Produto esgotado
    }

    // Verifica se o produto já está no carrinho
    $sqlCheck = "SELECT COUNT(*) AS total FROM carrinho WHERE id_utilizador = ? AND id_produtos = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param('ii', $idUtilizador, $idProduto);
    $stmtCheck->execute();
    $resultadoCheck = $stmtCheck->get_result();
    $produtoNoCarrinho = $resultadoCheck->fetch_assoc();

    if ($produtoNoCarrinho['total'] > 0) {
        return false; // Produto já está no carrinho, não adiciona de novo
    }

    // Insere o produto no carrinho com quantidade 1
    $sqlInsert = "INSERT INTO carrinho (id_utilizador, id_produtos, quantidade, data_adicionado) VALUES (?, ?, 1, NOW())";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param('ii', $idUtilizador, $idProduto);

    return $stmtInsert->execute();
}


function buscarItensCarrinho($idUtilizador) {
    global $pdo;

    $sql = "SELECT 
                c.id_carrinho, 
                p.id_produtos, 
                p.nome_produto, 
                p.preco, 
                p.caminho_imagem, 
                c.quantidade, 
                COALESCE(GROUP_CONCAT(DISTINCT m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marca
            FROM carrinho c
            INNER JOIN produtos p ON c.id_produtos = p.id_produtos
            INNER JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            INNER JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE c.id_utilizador = :id_utilizador
            GROUP BY p.id_produtos"; // Agrupar pelo ID do produto para evitar duplicação

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_utilizador' => $idUtilizador]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




// 🔹 Função para contar os produtos no carrinho
function contarItensCarrinho($idUtilizador)
{
    global $pdo; // Usa a conexão global

    $sql = "SELECT COALESCE(SUM(c.quantidade), 0) AS total_itens 
            FROM carrinho c 
            INNER JOIN produtos p ON c.id_produtos = p.id_produtos 
            WHERE c.id_utilizador = :id_utilizador";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_utilizador' => $idUtilizador]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total_itens']; // Retorna o número total de itens no carrinho
}


// 🔹 Função para remover um produto do carrinho
function removerProdutoCarrinho($idCarrinho, $conn)
{
    $sql = "DELETE FROM carrinho WHERE id_carrinho = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idCarrinho);
    return $stmt->execute();
}




function getUserData($idUtilizador, $liga) {
    $sql = "SELECT nome_utilizador, email, tipo_utilizador, data_criacao FROM utilizadores WHERE id_utilizador = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param('i', $idUtilizador);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserOrders($idUtilizador, $liga) {
    $sql = "SELECT p.nome_produto, p.preco, c.quantidade, c.data_adicionado 
            FROM carrinho c
            INNER JOIN produtos p ON c.id_produtos = p.id_produtos
            WHERE c.id_utilizador = ?
            ORDER BY c.data_adicionado DESC";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param('i', $idUtilizador);
    $stmt->execute();
    return $stmt->get_result();
}




// 🔹 Buscar dados da encomenda (nome, email, total)
function buscarDadosEncomenda($id_encomenda) {
    global $liga;
    $sql = "SELECT e.id_encomenda, u.nome_utilizador, u.email, e.total
            FROM encomendas e
            INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador
            WHERE e.id_encomenda = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $id_encomenda);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// 🔹 Buscar produtos da encomenda
function buscarProdutosEncomenda($id_encomenda) {
    global $liga;
    $sql = "SELECT p.nome_produto, ep.quantidade, ep.preco_unitario
            FROM encomenda_produtos ep
            INNER JOIN produtos p ON ep.id_produtos = p.id_produtos
            WHERE ep.id_encomenda = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $id_encomenda);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
