<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Rustic Iron - Novo Utilizador</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Cadastrar Novo Funcionário / Admin</h2>
        <form action="/app/Controllers/UsuarioController.php" method="POST">
            <label>Nome Completo:</label>
            <input type="text" name="nome" required>

            <label>E-mail:</label>
            <input type="email" name="email">

            <label>CPF:</label>
            <input type="text" name="cpf" placeholder="000.000.000-00" required>

            <label>Senha Temporária:</label>
            <input type="password" name="senha" required>

            <label>Cargo:</label>
            <select name="cargo">
                <option value="diretoria">Diretoria</option>
                <option value="gerente">Gerente</option>
                <option value="professor">Professor</option>
                <option value="recepcao">Recepção</option>
            </select>

            <button type="submit">Salvar Utilizador</button>
        </form>
    </div>
</body>
</html>