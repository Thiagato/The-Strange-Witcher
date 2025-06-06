<?php
include '../Model/conexao.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nickname = $_POST['nickname'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nickname = ?");
    $stmt->execute([$nickname]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nickname'] = $usuario['nickname'];
        header("Location: feed.php");
        exit;
    } else {
        $erro = "Nickname ou senha incorretos.";
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
    <title>Login</title>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Bem-vindo à nossa rede!</h1>
    <p>Aqui você vai se divertir, criar posts e interagir.</p>

    <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>

    <form method="POST">
        <input type="text" name="nickname" placeholder="Seu nickname" required>
        <input type="password" name="senha" placeholder="Sua senha" required>
        <button type="submit">Entrar</button>
    </form>
    <p>Ainda não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a></p>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
