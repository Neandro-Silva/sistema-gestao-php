<?php
namespace App\Controllers;
use App\Models\Produto;

class ProdutoController {
    private Produto $model;

    public function __construct($db) {
        $this->model = new Produto($db);
    }

    public function listar() {
        echo json_encode($this->model->todos());
    }

    public function salvar() {
        $input = json_decode(file_get_contents('php://input'), true);
        $this->model->criar($input['nome'], $input['preco']);
        echo json_encode(['status' => 'sucesso']);
    }
}