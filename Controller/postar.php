<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../View/index.php");
    exit();
}

require '../Model/conexao.php';

$mensagem_erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $texto = trim($_POST['texto']);
    $id_usuario = $_SESSION['usuario_id'];

    if (!empty($texto)) {
        $sql = "INSERT INTO publicacoes (id_usuario, texto, data_publicacao) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario, $texto]);

        header("Location: ../View/feed.php");
        exit();
    } else {
        $mensagem_erro = "A publicação não pode estar vazia.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="../View/style.css">
    <title>Postar</title>
</head>
<body>
<?php include '../View/header.php'; ?>
<div class="container">
    <h2>O que está pensando?</h2>
    <?php if ($mensagem_erro): ?>
        <p style="color: red;"><?= htmlspecialchars($mensagem_erro) ?></p>
    <?php endif; ?>
    <form method="POST" action="postar.php">
        <textarea name="texto" rows="5" cols="50" placeholder="Escreva aqui..." required></textarea><br><br>
        <button type="submit">Postar</button>
    </form>
    <br>
    <a href="../View/feed.php">🔙 Voltar ao Feed</a>
</div>
<?php include '../View/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
