<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Login do Sistema</h2>
        
        <?php if(isset($_SESSION['erro_login'])): ?>
            <p class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center"><?= $_SESSION['erro_login']; unset($_SESSION['erro_login']); ?></p>
        <?php endif; ?>

        <form action="/login-validar" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" name="email" required class="w-full mt-1 border p-3 rounded-lg outline-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Senha</label>
                <input type="password" name="senha" required class="w-full mt-1 border p-3 rounded-lg outline-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-bold hover:bg-blue-700 transition">Entrar</button>
        </form>
    </div>
</body>
</html>