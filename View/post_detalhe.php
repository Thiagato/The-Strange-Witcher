<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require '../Model/conexao.php';

$usuario_id = $_SESSION['usuario_id'];

if (!isset($_GET['id'])) {
    echo "Post não encontrado.";
    exit();
}

$post_id = $_GET['id'];

$sql_post = "
    SELECT p.*, u.nickname,
        (SELECT COUNT(*) FROM curtidas WHERE id_publicacao = p.id) AS total_curtidas,
        (SELECT COUNT(*) FROM comentarios WHERE id_publicacao = p.id) AS total_comentarios
    FROM publicacoes p
    JOIN usuarios u ON p.id_usuario = u.id
    WHERE p.id = ?
";
$stmt = $pdo->prepare($sql_post);
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "Post não encontrado.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['texto'])) {
    $texto = trim($_POST['texto']);
    $comentario_pai = isset($_POST['id_comentario_pai']) ? $_POST['id_comentario_pai'] : null;

    if (!empty($texto)) {
        $stmt = $pdo->prepare("INSERT INTO comentarios (id_usuario, id_publicacao, texto, id_comentario_pai) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $post_id, $texto, $comentario_pai]);
        header("Location: post_detalhe.php?id=$post_id");
        exit();
    }
}

function exibirComentarios($pdo, $post_id, $comentario_pai = null, $nivel = 0) {
    $usuario_id = $_SESSION['usuario_id'];

    $sql = "
        SELECT c.*, u.nickname,
            (SELECT COUNT(*) FROM curtidas_comentarios WHERE id_comentario = c.id) AS total_curtidas,
            (SELECT COUNT(*) FROM curtidas_comentarios WHERE id_comentario = c.id AND id_usuario = ?) AS curtiu_usuario
        FROM comentarios c
        JOIN usuarios u ON c.id_usuario = u.id
        WHERE c.id_publicacao = ? AND " . ($comentario_pai === null ? "c.id_comentario_pai IS NULL" : "c.id_comentario_pai = ?") . "
        ORDER BY c.data_comentario ASC
    ";

    $params = [$usuario_id, $post_id];
    if ($comentario_pai !== null) $params[] = $comentario_pai;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comentarios as $comentario) {
        $curtiuClass = $comentario['curtiu_usuario'] ? 'curtido' : '';
        echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);
        echo "<div style='border-left: 2px solid #ccc; margin: 10px 0 10px " . ($nivel * 20) . "px; padding-left: 10px;'>";
        echo "<strong>@{$comentario['nickname']}</strong> - " . date("d/m/Y H:i", strtotime($comentario['data_comentario'])) . "<br>";
        echo nl2br(htmlspecialchars($comentario['texto'])) . "<br>";
        echo "<button class='btn-curtir-comentario $curtiuClass' data-id='{$comentario['id']}'>💜 Curtir</button> ";
        echo "<span class='contador-curtidas' id='curtidas-{$comentario['id']}'>{$comentario['total_curtidas']}</span> curtida(s)";
        echo " | <a href='?id=$post_id&responder={$comentario['id']}#resposta'>Responder</a>";
        echo "</div>";
        exibirComentarios($pdo, $post_id, $comentario['id'], $nivel + 1);
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
    <title>Detalhes do Post</title>
    <style>
        .btn-curtir-comentario {
            cursor: pointer;
            background-color: transparent;
            border: none;
            color: #888;
            font-weight: bold;
        }
        .btn-curtir-comentario.curtido {
            color: #e0245e;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <a href="feed.php">⬅ Voltar para o Feed</a>
    <h2>Post de @<?= htmlspecialchars($post['nickname']) ?></h2>
    <p><strong>Publicado em:</strong> <?= date('d/m/Y H:i', strtotime($post['data_publicacao'])) ?></p>
    <p><?= nl2br(htmlspecialchars($post['texto'])) ?></p>
    <p>💜 <?= $post['total_curtidas'] ?> curtida(s) | 💬 <?= $post['total_comentarios'] ?> comentário(s)</p>

    <hr>
    <h3>Comentários</h3>
    <?php
    exibirComentarios($pdo, $post_id);
    if ($post['total_comentarios'] == 0) echo "<p>Ninguém respondeu ainda.</p>";
    ?>

    <hr>
    <h3 id="resposta">
        <?= isset($_GET['responder']) ? "Responder Comentário" : "Comentar no Post" ?>
    </h3>
    <form method="POST">
        <textarea name="texto" rows="3" cols="60" placeholder="Escreva seu comentário aqui..." required></textarea><br>
        <?php if (isset($_GET['responder'])): ?>
            <input type="hidden" name="id_comentario_pai" value="<?= intval($_GET['responder']) ?>">
        <?php endif; ?>
        <button type="submit">Enviar</button>
    </form>
</div>
<?php include 'footer.php'; ?>
<script>
document.querySelectorAll('.btn-curtir-comentario').forEach(button => {
    button.addEventListener('click', () => {
        const idComentario = button.getAttribute('data-id');

        fetch('../Controller/curtir_comentario.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id_comentario=' + encodeURIComponent(idComentario)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contador = document.getElementById('curtidas-' + idComentario);
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
        .catch(() => alert('Erro ao curtir comentário.'));
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
