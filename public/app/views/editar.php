<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-md">
        <h1 class="text-2xl font-bold mb-6">Editar Produto</h1>
        
        <form action="/editar-confirmar" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Preço</label>
                <input type="number" step="0.01" name="preco" value="<?= $produto['preco'] ?>" class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Categoria</label>
                <select name="categoria_id" class="w-full border p-2 rounded">
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $produto['categoria_id'] ? 'selected' : '' ?>>
                            <?= $cat['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-2">Foto atual:</p>
                <?php if ($produto['imagem']): ?>
                    <img src="/uploads/<?= $produto['imagem'] ?>" class="w-20 h-20 object-cover rounded mb-2">
                <?php endif; ?>
                <input type="file" name="foto" class="text-sm">
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Salvar Alterações</button>
                <a href="/" class="bg-gray-500 text-white px-6 py-2 rounded">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>