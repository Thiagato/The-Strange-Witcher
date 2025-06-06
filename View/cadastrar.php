<?php
include '../Model/conexao.php';

$erros = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $nickname = trim($_POST['nickname']);
    $senha_raw = $_POST['senha'];
    $bio = trim($_POST['bio']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido.';
    }

    if (strlen($nickname) < 3) {
        $erros[] = 'Nickname precisa ter ao menos 3 caracteres.';
    }

    if (strlen($senha_raw) < 6) {
        $erros[] = 'A senha deve ter ao menos 6 caracteres.';
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? OR nickname = ?');
    $stmt->execute([$email, $nickname]);
    if ($stmt->fetch()) {
        $erros[] = 'E-mail ou nickname já cadastrados.';
    }

    if (!$erros) {
        $senha = password_hash($senha_raw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (email, nickname, senha, bio) VALUES (?, ?, ?, ?)');
        $stmt->execute([$email, $nickname, $senha, $bio]);
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Cadastro</title>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Cadastro</h1>
    <?php if ($erros): ?>
        <div class="alert alert-danger">
            <?php foreach ($erros as $e) echo '<p>'.htmlspecialchars($e).'</p>'; ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Seu e-mail" required>
        <input type="text" name="nickname" placeholder="Seu nickname" required>
        <input type="password" name="senha" placeholder="Sua senha" required>
        <textarea name="bio" placeholder="Sua bio (opcional)"></textarea>
        <button type="submit">Cadastrar</button>
    </form>
    <p>Já tem uma conta? <a href="index.php">Faça login</a></p>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
