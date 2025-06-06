<?php
// Ajusta caminhos de links e imagem quando a página atual está dentro de
// `Controller` para que o header funcione em qualquer diretório
$dir = basename(dirname($_SERVER['SCRIPT_NAME']));
$viewPrefix = $dir === 'Controller' ? '../View/' : '';
$controllerPrefix = $dir === 'Controller' ? '' : '../Controller/';
?>
<header class="bg-dark text-light mb-4">
    <div class="container d-flex align-items-center py-2">
        <img src="<?= $viewPrefix ?>logo.png" alt="Logo" class="logo">
        <nav>
            <a href="<?= $viewPrefix ?>listar_usuarios.php">🔍 Pesquisar Usuários</a> |
            <a href="<?= $viewPrefix ?>perfil.php">👤 Meu Perfil</a> |
            <a href="<?= $controllerPrefix ?>postar.php">📝 Postar</a> |
            <a href="<?= $viewPrefix ?>index.php">🚪 Sair</a>
        </nav>
    </div>
    <hr class="m-0">
</header>
