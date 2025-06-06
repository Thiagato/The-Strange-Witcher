<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once '../Model/conexao.php';

$usuario_id = $_SESSION['usuario_id'];

$sql = "
    SELECT p.*, u.nickname,
        (SELECT COUNT(*) FROM curtidas WHERE id_publicacao = p.id) AS total_curtidas,
        (SELECT COUNT(*) FROM comentarios WHERE id_publicacao = p.id) AS total_comentarios
    FROM publicacoes p
    JOIN usuarios u ON p.id_usuario = u.id
    ORDER BY p.data_publicacao DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Feed</title>
    <style>
        .btn-curtir-post {
            cursor: pointer;
            background-color: transparent;
            border: none;
            color: #888;
            font-weight: bold;
        }
        .btn-curtir-post.curtido {
            color: #e0245e;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h2>Feed</h2>

    <?php if (count($posts) === 0): ?>
        <p>Nenhuma publicação encontrada.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php
                $stmtCurtiu = $pdo->prepare("SELECT id FROM curtidas WHERE id_usuario = ? AND id_publicacao = ?");
                $stmtCurtiu->execute([$usuario_id, $post['id']]);
                $jaCurtiu = $stmtCurtiu->fetch() ? true : false;
                $curtiuClass = $jaCurtiu ? 'curtido' : '';
            ?>
            <div class="post">
                <p class="nickname">@<?= htmlspecialchars($post['nickname']) ?></p>
                <p class="texto"><?= nl2br(htmlspecialchars($post['texto'])) ?></p>
                <p class="data"><?= date('d/m/Y H:i', strtotime($post['data_publicacao'])) ?></p>
                <p>
                    <button class="btn-curtir-post <?= $curtiuClass ?>" data-id="<?= $post['id'] ?>">💜 Curtir</button>
                    <span id="curtidas-post-<?= $post['id'] ?>"><?= $post['total_curtidas'] ?></span> curtida(s) |
                    💬 <?= $post['total_comentarios'] ?> comentário(s)
                </p>
                <a href="post_detalhe.php?id=<?= $post['id'] ?>">Ver mais</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
<script>
    document.querySelectorAll('.btn-curtir-post').forEach(button => {
        button.addEventListener('click', () => {
            const idPost = button.getAttribute('data-id');

            fetch('../Controller/curtir_post.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id_publicacao=' + encodeURIComponent(idPost)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contador = document.getElementById('curtidas-post-' + idPost);
                    contador.textContent = data.total;

                    if (data.curtiu) {
                        button.classList.add('curtido');
                    } else {
                        button.classList.remove('curtido');
                    }
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(() => alert('Erro ao curtir o post.'));
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
