<?php
include '../Model/conexao.php';
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

$stmtUser = $pdo->prepare("SELECT nickname, bio FROM usuarios WHERE id = ?");
$stmtUser->execute([$id]);
$usuario = $stmtUser->fetch();

$stmtPosts = $pdo->prepare("SELECT texto, data_publicacao FROM publicacoes WHERE id_usuario = ? ORDER BY data_publicacao DESC");
$stmtPosts->execute([$id]);
$posts = $stmtPosts->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Perfil de Usuário</title>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h2>@<?= htmlspecialchars($usuario['nickname']) ?></h2>
    <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($usuario['bio'])) ?: 'Sem bio no momento.' ?></p>

    <h3>Publicações:</h3>
    <?php if (empty($posts)): ?>
        <p><em>Não tem publicações aqui.</em></p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><?= nl2br(htmlspecialchars($post['texto'])) ?></p>
                <small><?= $post['data_publicacao'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
