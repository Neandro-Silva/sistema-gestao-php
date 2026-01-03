<?php
try {
    $path = __DIR__ . '/banco.sqlite';
    $pdo = new PDO("sqlite:$path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // No seu database.php
$pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    senha TEXT NOT NULL
)");

    // Vamos criar um usuário padrão para você testar (admin@teste.com / 123456)
    $checkUser = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    if ($checkUser == 0) {
        $senhaHash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (email, senha) VALUES (?, ?)");
        $stmt->execute(['admin@teste.com', $senhaHash]);
    }

    // 1. Tabela de Categorias
    $pdo->exec("CREATE TABLE IF NOT EXISTS categorias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL
    )");

    // 2. Tabela de Produtos (com categoria_id)
    $pdo->exec("CREATE TABLE IF NOT EXISTS produtos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        imagem TEXT,
        nome TEXT NOT NULL,
        preco REAL NOT NULL,
        categoria_id INTEGER,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    )");

    // Inserir algumas categorias iniciais se estiver vazio
    $check = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO categorias (nome) VALUES ('Hardware'), ('Periféricos'), ('Escritório')");
    }

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}