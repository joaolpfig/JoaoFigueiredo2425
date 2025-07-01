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
function buscarProdutosPorNomeComContagem($liga, $query, $produtos_por_pagina, $offset)
{
    $produtos = [];
    $total_produtos = 0;

    // Prepara o termo para pesquisa FULLTEXT
    $fulltextSearch = "+" . implode(" +", explode(" ", $query));

    // 1. Tenta com FULLTEXT
    $sql = "
        SELECT 
            SQL_CALC_FOUND_ROWS
            p.id_produtos, 
            p.nome_produto, 
            p.caminho_imagem, 
            p.caminho_imagem_hover, 
            p.preco,
            p.quantidade,
            COALESCE(GROUP_CONCAT(m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
        FROM 
            produtos p
        LEFT JOIN 
            produtos_marcas pm ON p.id_produtos = pm.id_produto
        LEFT JOIN 
            marcas m ON pm.id_marcas = m.id_marca
        WHERE MATCH(p.nome_produto, p.keywords) AGAINST (? IN BOOLEAN MODE)
        GROUP BY 
            p.id_produtos
        LIMIT ? OFFSET ?
    ";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $fulltextSearch, $produtos_por_pagina, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $produtos[] = $row;
    }

    // Conta total de resultados da pesquisa FULLTEXT
    $result_total = mysqli_query($liga, "SELECT FOUND_ROWS() AS total");
    $row_total = mysqli_fetch_assoc($result_total);
    $total_produtos = (int)($row_total['total'] ?? 0);

    // 2. Se FULLTEXT falhar, tenta com LIKE
    if ($total_produtos === 0) {
        $produtos = []; // Limpa, só por segurança
        $likeSearch = "%" . $query . "%";
        $sql = "
            SELECT 
                SQL_CALC_FOUND_ROWS
                p.id_produtos, 
                p.nome_produto, 
                p.caminho_imagem, 
                p.caminho_imagem_hover, 
                p.preco,
                p.quantidade,
                COALESCE(GROUP_CONCAT(m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
            FROM 
                produtos p
            LEFT JOIN 
                produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN 
                marcas m ON pm.id_marcas = m.id_marca
            WHERE p.nome_produto LIKE ? OR p.keywords LIKE ?
            GROUP BY 
                p.id_produtos
            LIMIT ? OFFSET ?
        ";
        $stmt = mysqli_prepare($liga, $sql);
        mysqli_stmt_bind_param($stmt, "ssii", $likeSearch, $likeSearch, $produtos_por_pagina, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $produtos[] = $row;
        }

        // Conta total com LIKE
        $result_total = mysqli_query($liga, "SELECT FOUND_ROWS() AS total");
        $row_total = mysqli_fetch_assoc($result_total);
        $total_produtos = (int)($row_total['total'] ?? 0);
    }

    return [
        'produtos' => $produtos,
        'total' => $total_produtos
    ];
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



function buscarProdutosPorMarca($liga, $marca_id, $produtos_por_pagina, $offset)
{
    $sql = "
    SELECT 
        p.id_produtos,
        p.nome_produto, 
        p.preco,
        p.caminho_imagem, 
        p.caminho_imagem_hover,
        p.quantidade,
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
        p.caminho_imagem_hover,
        p.quantidade
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
            m.nome_marca ASC";  // Ordenando alfabeticamente pela coluna nome_marca

    $resultado = mysqli_query($liga, $sql);

    if (!$resultado) {
        die('Erro ao obter marcas: ' . mysqli_error($liga));
    }

    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}





// 🔹 Função para buscar produtos no carrinho
function adicionarAoCarrinho($idUtilizador, $idProduto) {
    global $liga;

    // Verifica stock
    $sqlStock = "SELECT quantidade FROM produtos WHERE id_produtos = ?";
    $stmtStock = mysqli_prepare($liga, $sqlStock);
    mysqli_stmt_bind_param($stmtStock, 'i', $idProduto);
    mysqli_stmt_execute($stmtStock);
    $resStock = mysqli_stmt_get_result($stmtStock);
    $produto = mysqli_fetch_assoc($resStock);

    if (!$produto || $produto['quantidade'] == 0) {
        return false;
    }

    // Verifica duplicado
    $sqlCheck = "SELECT COUNT(*) AS total FROM carrinho WHERE id_utilizador = ? AND id_produtos = ?";
    $stmtCheck = mysqli_prepare($liga, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ii", $idUtilizador, $idProduto);
    mysqli_stmt_execute($stmtCheck);
    $resCheck = mysqli_stmt_get_result($stmtCheck);
    $rowCheck = mysqli_fetch_assoc($resCheck);

    if ($rowCheck['total'] > 0) {
        return false;
    }

    // Insere no carrinho
    $sqlInsert = "INSERT INTO carrinho (id_utilizador, id_produtos, quantidade, data_adicionado) VALUES (?, ?, 1, NOW())";
    $stmtInsert = mysqli_prepare($liga, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, "ii", $idUtilizador, $idProduto);
    return mysqli_stmt_execute($stmtInsert);
}


function buscarItensCarrinho($idUtilizador) {
    global $liga;

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
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE c.id_utilizador = ?
            GROUP BY c.id_carrinho, p.id_produtos";

    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idUtilizador);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $itens = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $itens[] = $row;
    }

    return $itens;
}




// 🔹 Função para contar os produtos no carrinho
function contarItensCarrinho($idUtilizador) {
    global $liga;

    $sql = "SELECT COALESCE(SUM(c.quantidade), 0) AS total_itens 
            FROM carrinho c 
            INNER JOIN produtos p ON c.id_produtos = p.id_produtos 
            WHERE c.id_utilizador = ?";

    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idUtilizador);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row['total_itens'];
}


// 🔹 Função para remover um produto do carrinho
function removerProdutoCarrinho($idCarrinho) {
    global $liga;
    $sql = "DELETE FROM carrinho WHERE id_carrinho = ?";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idCarrinho);
    return mysqli_stmt_execute($stmt);
}





function getUserData($idUtilizador, $liga) {
    $sql = "SELECT nome_utilizador, email, tipo_utilizador, data_criacao FROM utilizadores WHERE id_utilizador = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param('i', $idUtilizador);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserOrders($idUtilizador, $liga) {
    $sql = "SELECT 
                e.id_encomenda, 
                e.data_encomenda, 
                e.estado, 
                e.total, 
                p.nome_produto, 
                ep.quantidade, 
                ep.preco_unitario
            FROM encomendas e
            JOIN encomenda_produtos ep ON e.id_encomenda = ep.id_encomenda
            JOIN produtos p ON ep.id_produtos = p.id_produtos
            WHERE e.id_utilizador = ?
            ORDER BY e.data_encomenda DESC";

    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $idUtilizador);
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





// 1. Obter todos os tamanhos disponíveis
function obterTamanhosDisponiveis($liga, $categoria = null) {
    if ($categoria) {
        $sql = "SELECT DISTINCT tamanho FROM produtos WHERE categoria = ? AND tamanho IS NOT NULL ORDER BY tamanho ASC";
        $stmt = $liga->prepare($sql);
        $stmt->bind_param("s", $categoria);
    } else {
        $sql = "SELECT DISTINCT tamanho FROM produtos WHERE tamanho IS NOT NULL ORDER BY tamanho ASC";
        $stmt = $liga->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $tamanhos = [];
    while ($row = $result->fetch_assoc()) {
        $tamanhos[] = $row['tamanho'];
    }
    return $tamanhos;
}




// Função para listar produtos no listar.php (SEM preco_max, igual antes)
function listarProdutosComFiltros($liga, $pagina, $limite, $tamanhos, $stock, $ordenar, $preco_max) {
    $offset = ($pagina - 1) * $limite;

    if (!is_array($tamanhos)) {
        $tamanhos = $tamanhos !== '' ? [$tamanhos] : [];
    }
    $tamanhos = array_filter($tamanhos, fn($t) => trim($t) !== "");

    $sql = "SELECT p.*, 
                   COALESCE(GROUP_CONCAT(DISTINCT m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE p.preco <= ?";
    $params = [$preco_max];
    $types = "d";

    if (!empty($tamanhos)) {
        $placeholders = implode(",", array_fill(0, count($tamanhos), "?"));
        $sql .= " AND p.tamanho IN ($placeholders)";
        $types .= str_repeat("s", count($tamanhos));
        $params = array_merge($params, $tamanhos);
    }

    if ($stock !== '') {
        $sql .= ($stock === "In Stock") ? " AND p.quantidade > 0" : " AND p.quantidade = 0";
    }

    $sql .= " GROUP BY p.id_produtos";

    switch ($ordenar) {
        case 'preco_asc': $sql .= " ORDER BY p.preco ASC"; break;
        case 'preco_desc': $sql .= " ORDER BY p.preco DESC"; break;
        default: $sql .= " ORDER BY p.id_produtos DESC";
    }

    $sql .= " LIMIT ?, ?";
    $types .= "ii";
    $params[] = $offset;
    $params[] = $limite;

    $stmt = $liga->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    return $produtos;
}


function contarProdutosComFiltros($liga, $tamanhos, $stock) {
    if (!is_array($tamanhos)) {
        $tamanhos = $tamanhos !== '' ? [$tamanhos] : [];
    }
    $tamanhos = array_filter($tamanhos, fn($t) => trim($t) !== "");

    $sql = "SELECT COUNT(DISTINCT p.id_produtos) AS total
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($tamanhos)) {
        $placeholders = implode(",", array_fill(0, count($tamanhos), "?"));
        $sql .= " AND p.tamanho IN ($placeholders)";
        $types .= str_repeat("s", count($tamanhos));
        $params = array_merge($params, $tamanhos);
    }

    if ($stock !== '') {
        $sql .= ($stock === "In Stock") ? " AND p.quantidade > 0" : " AND p.quantidade = 0";
    }

    $stmt = $liga->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc()['total'] ?? 0;
}




// Função listar produtos com pesquisa (resultados.php)
function listarProdutosAjax($liga, $query = '', $tamanho = '', $stock = '', $ordem = 'standard') {
    $sql = "
        SELECT 
            produtos.id_produto,
            produtos.nome AS nome,
            produtos.imagem AS imagem,
            marcas.nome AS marca,
            produtos.preco,
            produtos.stock
        FROM produtos
        LEFT JOIN marcas ON produtos.id_marca = marcas.id_marca
        WHERE 1
    ";

    $parametros = [];
    $tipos = "";

    // Filtro por pesquisa (query)
    if (!empty($query)) {
        $sql .= " AND produtos.nome LIKE ?";
        $parametros[] = '%' . $query . '%';
        $tipos .= "s";
    }

    // Filtro por tamanho
    if (!empty($tamanho)) {
        $sql .= " AND produtos.tamanho = ?";
        $parametros[] = $tamanho;
        $tipos .= "s";
    }

    // Filtro por stock (available/all)
    if ($stock === 'available') {
        $sql .= " AND produtos.stock > 0";
    }

    // Ordenação
    switch ($ordem) {
        case 'price_asc':
            $sql .= " ORDER BY produtos.preco ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY produtos.preco DESC";
            break;
        default:
            $sql .= " ORDER BY produtos.id_produto DESC";
            break;
    }

    $stmt = $liga->prepare($sql);
    if (!empty($tipos)) {
        $stmt->bind_param($tipos, ...$parametros);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    $produtos = [];
    while ($linha = $resultado->fetch_assoc()) {
        $produtos[] = $linha;
    }

    return $produtos;
}




function listarProdutosPorCategoriaComFiltros($liga, $categoria, $pagina, $limite, $tamanhos, $stock, $ordenar, $preco_max) {
    $offset = ($pagina - 1) * $limite;

    if (!is_array($tamanhos)) {
        $tamanhos = $tamanhos !== '' ? [$tamanhos] : [];
    }
    $tamanhos = array_filter($tamanhos, fn($t) => trim($t) !== "");

    $sql = "SELECT p.*,
                   COALESCE(GROUP_CONCAT(DISTINCT m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE p.categoria = ?
              AND p.preco <= ?";
    $params = [$categoria, $preco_max];
    $types = "sd";

    if (!empty($tamanhos)) {
        $placeholders = implode(",", array_fill(0, count($tamanhos), "?"));
        $sql .= " AND p.tamanho IN ($placeholders)";
        $types .= str_repeat("s", count($tamanhos));
        $params = array_merge($params, $tamanhos);
    }

    if ($stock !== '') {
        $sql .= ($stock === "In Stock") ? " AND p.quantidade > 0" : " AND p.quantidade = 0";
    }

    $sql .= " GROUP BY p.id_produtos";

    switch ($ordenar) {
        case 'preco_asc': $sql .= " ORDER BY p.preco ASC"; break;
        case 'preco_desc': $sql .= " ORDER BY p.preco DESC"; break;
        default: $sql .= " ORDER BY p.id_produtos DESC";
    }

    $sql .= " LIMIT ?, ?";
    $types .= "ii";
    $params[] = $offset;
    $params[] = $limite;

    $stmt = $liga->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    return $produtos;
}


function contarProdutosPorCategoriaComFiltros($liga, $categoria, $tamanhos, $stock, $preco_max) {
    if (!is_array($tamanhos)) {
        $tamanhos = $tamanhos !== '' ? [$tamanhos] : [];
    }
    $tamanhos = array_filter($tamanhos, fn($t) => trim($t) !== "");

    $sql = "SELECT COUNT(DISTINCT p.id_produtos) AS total
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE p.categoria = ?
              AND p.preco <= ?";
    $params = [$categoria, $preco_max];
    $types = "sd";

    if (!empty($tamanhos)) {
        $placeholders = implode(",", array_fill(0, count($tamanhos), "?"));
        $sql .= " AND p.tamanho IN ($placeholders)";
        $types .= str_repeat("s", count($tamanhos));
        $params = array_merge($params, $tamanhos);
    }

    if ($stock !== '') {
        $sql .= ($stock === "In Stock") ? " AND p.quantidade > 0" : " AND p.quantidade = 0";
    }

    $stmt = $liga->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc()['total'] ?? 0;
}


// 5. Contar produtos no AJAX
function contarProdutosAjax($liga, $tamanhos, $stock, $preco_max, $categoria = null, $pesquisa = null) {
    $sql = "SELECT COUNT(DISTINCT p.id_produtos) AS total 
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE 1=1";

    $params = [];
    $types = "";

    if ($preco_max != 9999) {
        $sql .= " AND p.preco <= ?";
        $types .= "d";
        $params[] = $preco_max;
    }

    if (!empty($categoria)) {
        $sql .= " AND p.categoria = ?";
        $types .= "s";
        $params[] = $categoria;
    }

    if (!empty($pesquisa)) {
    // Divide a pesquisa em palavras
    $palavras = explode(" ", $pesquisa);
    foreach ($palavras as $palavra) {
        $sql .= " AND p.nome_produto LIKE ?";
        $types .= "s";
        $params[] = "%" . $palavra . "%";
    }
}


    if (!empty($tamanhos)) {
        $placeholders = implode(",", array_fill(0, count($tamanhos), "?"));
        $sql .= " AND p.tamanho IN ($placeholders)";
        $types .= str_repeat("s", count($tamanhos));
        $params = array_merge($params, $tamanhos);
    }

    if ($stock !== '') {
        $sql .= ($stock === "In Stock") ? " AND p.quantidade > 0" : " AND p.quantidade = 0";
    }

    $stmt = $liga->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc()['total'] ?? 0;
}




function listarProdutosPorPesquisaComFiltros($liga, $query, $pagina, $limite, $tamanhos, $stock, $ordenar, $preco_max = 9999) {
    $offset = ($pagina - 1) * $limite;
    if (!is_array($tamanhos)) {
        $tamanhos = $tamanhos !== '' ? [$tamanhos] : [];
    }
    $tamanhos = array_filter($tamanhos, fn($t) => trim($t) !== "");

    $sql = "SELECT p.*, 
                   COALESCE(GROUP_CONCAT(DISTINCT m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE p.preco <= ?";  // Preço máximo
    $params = [$preco_max];
    $types = "d";

    // Verifica se há uma query de pesquisa (nome do produto ou keywords)
    if (!empty($query)) {
        $sql .= " AND (p.nome_produto LIKE ? OR p.keywords LIKE ?)";
        $params[] = "%$query%";  // Adiciona a pesquisa no nome do produto
        $params[] = "%$query%";  // Adiciona a pesquisa nas palavras-chave
        $types .= "ss";
    }

    // Filtro de tamanhos
    if (!empty($tamanhos)) {
        $placeholders = implode(",", array_fill(0, count($tamanhos), "?"));
        $sql .= " AND p.tamanho IN ($placeholders)";
        $types .= str_repeat("s", count($tamanhos));
        $params = array_merge($params, $tamanhos);
    }

    // Filtro de stock
    if ($stock === "In Stock") {
        $sql .= " AND p.quantidade > 0";
    } elseif ($stock === "Sold Out") {
        $sql .= " AND p.quantidade = 0";
    }

    // Ordenação
    switch ($ordenar) {
        case 'preco_asc': $sql .= " ORDER BY p.preco ASC"; break;
        case 'preco_desc': $sql .= " ORDER BY p.preco DESC"; break;
        default: $sql .= " ORDER BY p.id_produtos DESC";
    }

    // Limitação de resultados
    $sql .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limite;
    $types .= "ii";

    // Preparar e executar a consulta
    $stmt = $liga->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    return $produtos;
}



function contarProdutosPorPesquisaComFiltros($liga, $query, $tamanhos, $stock, $preco_max = 9999) {
    if (!is_array($tamanhos)) {
        $tamanhos = $tamanhos !== '' ? [$tamanhos] : [];
    }
    $tamanhos = array_filter($tamanhos, fn($t) => trim($t) !== "");

    $sql = "SELECT COUNT(DISTINCT p.id_produtos) AS total
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            WHERE p.preco <= ?";
    $params = [$preco_max];
    $types = "d";

    if (!empty($query)) {
        $sql .= " AND (p.nome_produto LIKE ? OR p.keywords LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
        $types .= "ss";
    }

    if (!empty($tamanhos)) {
        $placeholders = implode(",", array_fill(0, count($tamanhos), "?"));
        $sql .= " AND p.tamanho IN ($placeholders)";
        $types .= str_repeat("s", count($tamanhos));
        $params = array_merge($params, $tamanhos);
    }

    if ($stock === "In Stock") {
        $sql .= " AND p.quantidade > 0";
    } elseif ($stock === "Sold Out") {
        $sql .= " AND p.quantidade = 0";
    }

    $stmt = $liga->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc()['total'] ?? 0;
}



// Função para contar encomendas
function contarTotalEncomendas() {
    global $liga;
    $sql = "SELECT COUNT(*) as total FROM encomendas";
    $resultado = mysqli_query($liga, $sql);
    return mysqli_fetch_assoc($resultado)['total'];
}

// Função para contar clientes
function contarTotalClientes() {
    global $liga;
    $sql = "SELECT COUNT(*) as total FROM utilizadores WHERE LOWER(tipo_utilizador) = 'user'";
    $resultado = mysqli_query($liga, $sql);
    return mysqli_fetch_assoc($resultado)['total'];
}


// Função para contar encomendas por estado
function contarEncomendasPorEstado($estado) {
    global $liga;
    $sql = "SELECT COUNT(*) as total FROM encomendas WHERE estado = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("s", $estado);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc()['total'];
}

// Função para somar total da receita
function somarTotalReceita() {
    global $liga;
    $sql = "SELECT SUM(total) AS total_receita FROM encomendas WHERE estado = 'Paid'";
    $result = mysqli_query($liga, $sql);
    $row = mysqli_fetch_assoc($result);
    return (float) $row['total_receita'];
}



// Função para buscar últimas encomendas
function buscarUltimasEncomendas($limite = 10) {
    global $liga;
    $sql = "SELECT e.id_encomenda, u.nome_utilizador, e.total, e.estado, e.data_encomenda
            FROM encomendas e
            JOIN utilizadores u ON e.id_utilizador = u.id_utilizador
            ORDER BY e.data_encomenda DESC
            LIMIT ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Função para listar utilizadores logados (opcional)
function listarContasAtivas() {
    global $liga;
    $sql = "SELECT id_utilizador, nome_utilizador, email, tipo_utilizador, data_criacao FROM utilizadores";
    $resultado = mysqli_query($liga, $sql);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}


// Função para buscar todas as marcas disponíveis
function buscarMarcasDisponiveis() {
    global $liga;
    $sql = "SELECT id_marca, nome_marca FROM marcas ORDER BY nome_marca ASC";  // Adicionada a ordenação alfabética
    $resultado = mysqli_query($liga, $sql);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}



function inserirMarca($nome, $imagem) {
    global $liga;
    $stmt = mysqli_prepare($liga, "INSERT INTO marcas (nome_marca, caminho_imagem_brand) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $nome, $imagem);
    return mysqli_stmt_execute($stmt);
}


function inserirProduto($dados) {
    global $liga;
    $stmt = mysqli_prepare($liga, "INSERT INTO produtos (nome_produto, categoria, tamanho, cor, preco, caminho_imagem, caminho_imagem_hover, quantidade, keywords, data_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "sssssssis", $dados['nome'], $dados['categoria'], $dados['tamanho'], $dados['cor'], $dados['preco'], $dados['imagem'], $dados['hover'], $dados['quantidade'], $dados['keywords']);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($liga);
}



function associarMarcaAoProduto($id_produto, $id_marca) {
    global $liga;
    $stmt = mysqli_prepare($liga, "INSERT INTO produtos_marcas (id_produto, id_marcas) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ii", $id_produto, $id_marca);
    return mysqli_stmt_execute($stmt);
}


function listarMarcasPaginadas($pagina, $limite) {
    global $liga;

    $offset = ($pagina - 1) * $limite;

    $sql = "
        SELECT m.id_marca, m.nome_marca, m.caminho_imagem_brand,
               COUNT(pm.id_produto) AS total_produtos
        FROM marcas m
        LEFT JOIN produtos_marcas pm ON m.id_marca = pm.id_marcas
        GROUP BY m.id_marca
        ORDER BY m.nome_marca ASC
        LIMIT ? OFFSET ?
    ";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $limite, $offset);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

function contarTotalMarcas() {
    global $liga;
    $sql = "SELECT COUNT(*) AS total FROM marcas";
    $resultado = mysqli_query($liga, $sql);
    $row = mysqli_fetch_assoc($resultado);
    return (int) $row['total'];
}




function listarProdutosPaginados($pagina, $limite) {
    global $liga;

    $offset = ($pagina - 1) * $limite;

    $sql = "SELECT 
                p.id_produtos, 
                p.nome_produto, 
                p.preco, 
                p.quantidade, 
                p.caminho_imagem,
                p.caminho_imagem_hover,
                p.categoria,
                COALESCE(GROUP_CONCAT(DISTINCT m.nome_marca SEPARATOR ' x '), 'Sem Marca') AS nome_marcas
            FROM produtos p
            LEFT JOIN produtos_marcas pm ON p.id_produtos = pm.id_produto
            LEFT JOIN marcas m ON pm.id_marcas = m.id_marca
            GROUP BY p.id_produtos
            ORDER BY p.id_produtos DESC
            LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $limite, $offset);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

function contarTotalProdutos() {
    global $liga;
    $sql = "SELECT COUNT(*) as total FROM produtos";
    $res = mysqli_query($liga, $sql);
    $row = mysqli_fetch_assoc($res);
    return (int)$row['total'];
}



function buscarCategoriasDistintas() {
    global $liga;
    $sql = "SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
    $res = mysqli_query($liga, $sql);
    $categorias = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $categorias[] = $row['categoria'];
    }
    return $categorias;
}



function buscarMarcasDoProduto($id_produto) {
    global $liga;
    $sql = "SELECT id_marcas FROM produtos_marcas WHERE id_produto = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $id_produto);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = $row['id_marcas'];
    }
    return $ids;
}





function listarEncomendas($estado = '', $pagina = 1, $limite = 10) {
    global $liga;

    $offset = ($pagina - 1) * $limite;
    $sql = "SELECT e.id_encomenda, e.data_encomenda, e.total, e.estado, u.nome_utilizador
            FROM encomendas e
            INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador";

    $params = [];
    $types = "";

    if (!empty($estado)) {
        $sql .= " WHERE e.estado = ?";
        $estado = strtolower($estado);  // 🔧 AQUI está a correção para resolver o problema dos filtros
        $params[] = $estado;
        $types .= "s";
    }

    $sql .= " ORDER BY e.data_encomenda DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limite;
    $types .= "ii";

    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

// 🔸 Contar total de encomendas com ou sem filtro de estado
function contarEncomendas($estado = '') {
    global $liga;

    if (!empty($estado)) {
        $estado = strtolower($estado);
        $stmt = $liga->prepare("SELECT COUNT(*) AS total FROM encomendas WHERE estado = ?");
        $stmt->bind_param("s", $estado);
    } else {
        $stmt = $liga->prepare("SELECT COUNT(*) AS total FROM encomendas");
    }
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc()['total'] ?? 0;
}

// 🔸 Alterar estado da encomenda (apenas admin pode usar)
function alterarEstadoEncomenda($id_encomenda, $novo_estado) {
    global $liga;
    $stmt = $liga->prepare("UPDATE encomendas SET estado = ? WHERE id_encomenda = ?");
    $stmt->bind_param("si", $novo_estado, $id_encomenda);
    return $stmt->execute();
}
// 🔸 Cancelar encomenda (ex: marcar como 'cancelada' ou apagar — aqui só altera estado)
function cancelarEncomenda($id_encomenda) {
    return alterarEstadoEncomenda($id_encomenda, 'canceled');
}

// 🔸 Obter detalhes completos da encomenda
function obterDetalhesEncomenda($id_encomenda) {
    global $liga;

    $sql = "SELECT e.*, u.nome_utilizador, u.email
            FROM encomendas e
            INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador
            WHERE e.id_encomenda = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $id_encomenda);
    $stmt->execute();
    $detalhes = $stmt->get_result()->fetch_assoc();

    $sql2 = "SELECT ep.id_produtos, p.nome_produto, ep.quantidade, ep.preco_unitario,
                p.caminho_imagem, p.tamanho, p.cor
         FROM encomenda_produtos ep
         INNER JOIN produtos p ON ep.id_produtos = p.id_produtos
         WHERE ep.id_encomenda = ?";

    $stmt2 = $liga->prepare($sql2);
    $stmt2->bind_param("i", $id_encomenda);
    $stmt2->execute();
    $detalhes['produtos'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    return $detalhes;
}

// Adicionar imagem secundária
function adicionarImagemSecundaria($id_produto, $caminho_imagem) {
    global $liga;

    $stmt = $liga->prepare("INSERT INTO imagens (imagens, id_produtos) VALUES (?, ?)");
    $stmt->bind_param("si", $caminho_imagem, $id_produto);
    return $stmt->execute();
}



// Eliminar imagem secundária
function eliminarImagemSecundaria($id_imagem, $id_produto) {
    global $liga;

    $stmt = $liga->prepare("SELECT imagens FROM imagens WHERE id_imagem = ? AND id_produtos = ?");
    $stmt->bind_param("ii", $id_imagem, $id_produto);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $ficheiro = "img/imagens_produtos/" . $res['imagens'];
        if (file_exists($ficheiro)) {
            unlink($ficheiro);
        }
        $stmt = $liga->prepare("DELETE FROM imagens WHERE id_imagem = ?");
        $stmt->bind_param("i", $id_imagem);
        return $stmt->execute();
    }
    return false;
}



function buscarImagensProdutoCompleto($idProduto) {
    global $liga;
    $stmt = $liga->prepare("SELECT id_imagem, imagens FROM imagens WHERE id_produtos = ?");
    $stmt->bind_param("i", $idProduto);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
