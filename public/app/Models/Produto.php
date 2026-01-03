<?php
namespace App\Models;

class Produto {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function todos() {
        return $this->db->query("SELECT * FROM produtos")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function criar($nome, $preco) {
        $stmt = $this->db->prepare("INSERT INTO produtos (nome, preco) VALUES (:nome, :preco)");
        return $stmt->execute([':nome' => $nome, ':preco' => $preco]);
    }
}