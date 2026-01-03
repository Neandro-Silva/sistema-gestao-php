<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Produtos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">

    <div class="flex justify-between items-center mb-4">
        <span class="text-sm text-gray-600 italic">Logado como: <?= $_SESSION['usuario_email'] ?></span>
        <a href="/logout" class="text-red-600 font-bold hover:underline">Sair do Sistema</a>
    </div>

    <div class="max-w-4xl mx-auto">

    <?php if (isset($_SESSION['mensagem'])): ?>
        <?php $cor = $_SESSION['tipo_alerta'] ?? 'blue'; ?>
        <div id="alerta" class="bg-<?= $cor ?>-100 border-l-4 border-<?= $cor ?>-500 text-<?= $cor ?>-700 p-4 mb-6 shadow-md flex justify-between items-center animate-bounce">
            <p class="font-bold"><?= $_SESSION['mensagem'] ?></p>
            <button onclick="document.getElementById('alerta').remove()" class="text-xl">&times;</button>
        </div>
        <?php 
            unset($_SESSION['mensagem']); 
            unset($_SESSION['tipo_alerta']); 
        ?>
    <?php endif; ?>
        <h1 class="text-3xl font-bold text-gray-800 mb-8">📦 Gestão de Inventário</h1>

        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
    <h2 class="text-lg font-bold mb-4 text-gray-700 text-center">Distribuição por Categoria</h2>
    <div class="w-full max-w-xs mx-auto">
        <canvas id="meuGrafico"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('meuGrafico').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut', // Pode ser 'pie' ou 'doughnut'
        data: {
            labels: <?= $labelsJson ?>,
            datasets: [{
                data: <?= $valoresJson ?>,
                backgroundColor: [
                    '#3b82f6', // blue-500
                    '#10b981', // emerald-500
                    '#f59e0b', // amber-500
                    '#8b5cf6', // violet-500
                    '#ef4444'  // red-500
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-sm text-gray-500 uppercase font-bold">Total de Produtos</p>
                <p class="text-2xl font-black text-gray-800"><?= $stats['total'] ?? 0 ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <p class="text-sm text-gray-500 uppercase font-bold">Valor em Stock</p>
                <p class="text-2xl font-black text-gray-800">R$ <?= number_format($stats['valor_total'] ?? 0, 2, ',', '.') ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                <p class="text-sm text-gray-500 uppercase font-bold">Categorias Ativas</p>
                <div class="text-xs text-gray-600 mt-1">
                    <?php foreach($statsCategorias as $cat): ?>
                        <span class="inline-block bg-purple-50 px-2 py-1 rounded"><?= $cat['nome'] ?>: <?= $cat['qtd'] ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Novo Produto</h3>
           <form action="/produtos-web" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                <label class="block">
                    <span class="text-gray-700 text-sm">Foto do Produto</span>
                    <input type="file" name="foto" accept="image/*" 
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </label>
                <input type="text" name="nome" placeholder="Ex: Macbook M3" required
                       class="flex-1 border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                
                <input type="number" step="0.01" name="preco" placeholder="R$ 0,00" required
                       class="w-full md:w-32 border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                
                <select name="categoria_id" class="border border-gray-300 p-2 rounded-lg outline-none bg-white">
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    Adicionar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                   <th class="px-6 py-4 text-sm font-semibold text-gray-600">Categoria</th>

                    <td class="px-6 py-4 text-sm text-gray-600">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            <?= htmlspecialchars($p['categoria_nome'] ?? 'Sem Categoria') ?>
                        </span>
                    </td>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($produtos)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Nenhum produto cadastrado.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($produtos as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-500">#<?= $p['id'] ?></td>
                        <td class="px-6 py-4">
                            <?php if (!empty($p['imagem'])): ?>
                                <img src="/uploads/<?= $p['imagem'] ?>" class="w-12 h-12 object-cover rounded-lg shadow-sm">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-[10px] text-center p-1">Sem foto</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-800"><?= htmlspecialchars($p['nome']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600">R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                        <td class="px-6 py-4 text-sm">
                            <a href="/editar?id=<?= $p['id'] ?>" class="text-blue-500 hover:text-blue-700 font-medium mr-3">Editar</a>

                             <a href="/deletar-web?id=<?= $p['id'] ?>" class="text-red-500 hover:text-red-700 font-medium">Remover</a>
                        </td>
                    </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-6 flex justify-between items-center bg-white p-4 rounded-lg shadow-sm">
                        <span class="text-sm text-gray-600">
                            Página <strong><?= $paginaAtual ?></strong> de <?= $totalPaginas ?>
                        </span>
                        
                        <div class="flex gap-2">
                            <?php if ($paginaAtual > 1): ?>
                                <a href="/?pagina=<?= $paginaAtual - 1 ?>" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition">Anterior</a>
                            <?php endif; ?>

                            <?php if ($paginaAtual < $totalPaginas): ?>
                                <a href="/?pagina=<?= $paginaAtual + 1 ?>" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition">Próxima</a>
                            <?php endif; ?>
                        </div>
                    </div>
        </div>
    </div>

</body>
</html>