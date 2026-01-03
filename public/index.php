<?php
session_start();

// 1. PRIMEIRO: Definimos as variáveis de ambiente e rotas
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/database.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$metodo = $_SERVER['REQUEST_METHOD'];
$usuarioLogado = $_SESSION['usuario_id'] ?? null;

// 2. SEGUNDO: Gerenciamos o Login (Rotas Públicas)
if ($uri === '/login') {
    require_once __DIR__ . '/app/views/login.php';
    exit;
}

if ($uri === '/login-validar' && $metodo === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_email'] = $user['email'];
        header('Location: /');
        exit;
    } else {
        $_SESSION['erro_login'] = "E-mail ou senha inválidos.";
        header('Location: /login');
        exit;
    }
}

// 3. TERCEIRO: Bloqueio de Segurança (O "Segurança da Balada")
// Se não está logado e não está nas rotas de login, manda embora
if (!$usuarioLogado) {
    header('Location: /login');
    exit;
}

// 4. QUARTO: Rotas Protegidas (Só chega aqui quem passou pelo segurança)
if ($uri === '/logout') {
    session_destroy();
    header('Location: /login');
    exit;
}

// ... Suas outras rotas (/, /produtos-web, /editar, etc) vêm abaixo daqui ...

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/database.php'; // Aqui o $pdo é criado

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$metodo = $_SERVER['REQUEST_METHOD'];

// --- PROCESSAMENTO (Ações que redirecionam) ---

// Salvar Produto



if ($uri === '/produtos-web' && $metodo === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $preco = $_POST['preco'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? null;
    $nome_imagem = null;

    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES['foto']['type'], $tipos_permitidos)) {
        $_SESSION['mensagem'] = "Erro: Apenas imagens JPG, PNG ou WEBP são permitidas.";
        $_SESSION['tipo_alerta'] = "red";
        header('Location: /');
        exit;
    }

    // Verificamos se a chave 'foto' existe E se um arquivo foi realmente enviado
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        
        if (in_array($_FILES['foto']['type'], $tipos_permitidos)) {
            $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nome_imagem = bin2hex(random_bytes(10)) . "." . $extensao;
            $destino = __DIR__ . "/uploads/" . $nome_imagem;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                // Upload sucesso
            } else {
                $nome_imagem = null; // Falha ao mover
            }
        }
    }

    // Inserção no banco (independente de ter foto ou não)
    $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, categoria_id, imagem) VALUES (:n, :p, :c, :i)");
    $stmt->execute([
        ':n' => $nome, 
        ':p' => $preco, 
        ':c' => $categoria_id, 
        ':i' => $nome_imagem
    ]);

    $_SESSION['mensagem'] = "Produto cadastrado com sucesso!";
    $_SESSION['tipo_alerta'] = "green";
    
    header('Location: /');
    exit;
}

// 1. Rota para abrir o formulário de edição
if ($uri === '/editar' && $metodo === 'GET') {
    $id = $_GET['id'] ?? null;
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtCat = $pdo->query("SELECT * FROM categorias");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    require_once __DIR__ . '/app/views/editar.php';
    exit;
}

// 2. Rota para processar a atualização
if ($uri === '/editar-confirmar' && $metodo === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $categoria_id = $_POST['categoria_id'];

    // Buscar imagem antiga para caso de substituição
    $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $foto_antiga = $stmt->fetchColumn();

    $nome_imagem = $foto_antiga; // Por padrão, mantém a antiga

    // Se uma NOVA foto for enviada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome_imagem = bin2hex(random_bytes(10)) . "." . $extensao;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . "/uploads/" . $nome_imagem)) {
            // Se moveu a nova com sucesso, apaga a antiga do servidor
            if ($foto_antiga && file_exists(__DIR__ . "/uploads/" . $foto_antiga)) {
                unlink(__DIR__ . "/uploads/" . $foto_antiga);
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE produtos SET nome = :n, preco = :p, categoria_id = :c, imagem = :i WHERE id = :id");
    $stmt->execute([':n' => $nome, ':p' => $preco, ':c' => $categoria_id, ':i' => $nome_imagem, ':id' => $id]);

    $_SESSION['mensagem'] = "Produto atualizado com sucesso!";
    header('Location: /');
    exit;
}

// Deletar Produto
if ($uri === '/deletar-web') {
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // 1. Procurar o nome da imagem antes de apagar o registo
        $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Se o produto existir e tiver uma imagem, apaga o ficheiro físico
        if ($produto && $produto['imagem']) {
            $caminho_foto = __DIR__ . "/uploads/" . $produto['imagem'];
            if (file_exists($caminho_foto)) {
                unlink($caminho_foto); // Esta é a função que apaga o ficheiro
            }
        }

        // 3. Agora sim, apaga o registo no banco de dados
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $_SESSION['mensagem'] = "Produto e imagem removidos!";
        $_SESSION['tipo_alerta'] = "red";
    }
    
    header('Location: /');
    exit;
}

// --- EXIBIÇÃO (A rota principal "/") ---

if ($uri === '/') {
    // 1. Configurações de Paginação
    $porPagina = 5;
    $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($paginaAtual < 1) $paginaAtual = 1;
    $offset = ($paginaAtual - 1) * $porPagina;

    // 2. Contar total de produtos para saber quantas páginas existem
    $totalProdutos = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    $totalPaginas = ceil($totalProdutos / $porPagina);

    // 3. Buscar os produtos com LIMIT e OFFSET
    $sql = "SELECT produtos.*, categorias.nome AS categoria_nome 
            FROM produtos 
            LEFT JOIN categorias ON categorias.id = produtos.categoria_id 
            ORDER BY produtos.id DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reutilizando as queries de estatísticas que já tínhamos
    $stats = $pdo->query("SELECT COUNT(*) as total, SUM(preco) as valor_total FROM produtos")->fetch(PDO::FETCH_ASSOC);
    $statsCategorias = $pdo->query("SELECT categorias.nome, COUNT(produtos.id) as qtd FROM categorias LEFT JOIN produtos ON categorias.id = produtos.categoria_id GROUP BY categorias.id")->fetchAll(PDO::FETCH_ASSOC);
    $stmtCat = $pdo->query("SELECT * FROM categorias");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    // Preparamos os dados para o Gráfico
    $labels = [];
    $valores = [];

    foreach ($statsCategorias as $cat) {
        $labels[] = $cat['nome'];
        $valores[] = $cat['qtd'];
    }

    // Convertemos para JSON para o JavaScript ler
    $labelsJson = json_encode($labels);
    $valoresJson = json_encode($valores);

    require_once __DIR__ . '/app/views/lista.php';
    exit;
}