<?php
// Inicia a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['username'])) {
    // Se o usuário não estiver logado, redireciona para a página de login
    header("Location: index.php");
    exit();
}

// Conexão com o banco de dados
$mysqli = new mysqli("db", "HEBERTH", "BIBI7979DB", "LOGIN_DB");

// Verifica se a conexão com o banco de dados foi bem-sucedida
if ($mysqli->connect_error) {
    // Se houver erro na conexão, termina o script e exibe a mensagem de erro
    die("Connection failed: " . $mysqli->connect_error);
}

// Verificar se a senha já foi redefinida
$sqlCheck = "SELECT password_reset_done FROM users WHERE username = ?"; // Consulta para verificar se a redefinição de senha foi feita
$stmtCheck = $mysqli->prepare($sqlCheck); // Prepara a consulta
$stmtCheck->bind_param("s", $_SESSION['username']); // Liga o parâmetro da consulta (nome de usuário)
$stmtCheck->execute(); // Executa a consulta
$resultCheck = $stmtCheck->get_result(); // Obtém o resultado da consulta
$userCheck = $resultCheck->fetch_assoc(); // Armazena os dados do usuário

// Verifica se a redefinição de senha foi realizada
if ($userCheck['password_reset_done'] == 1) {
    // Se a senha já foi redefinida, redireciona para a página de cadastro
    header("Location: register.php");
    exit();
}

$erroSenha = ""; // Inicializa a variável de erro para a senha

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password']; // Obtém a nova senha do formulário
    $confirm_password = $_POST['confirm_password']; // Obtém a confirmação da nova senha

    // Verifica se as senhas digitadas são iguais
    if ($new_password !== $confirm_password) {
        // Se não forem iguais, define a mensagem de erro
        $erroSenha = "As senhas não coincidem!";
    } else {
        // Criptografa a nova senha
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        // Atualizar a senha no banco de dados
        $sqlUpdate = "UPDATE users SET password = ?, password_reset_done = 1 WHERE username = ?"; // Consulta para atualizar a senha e marcar a redefinição como feita
        $stmtUpdate = $mysqli->prepare($sqlUpdate); // Prepara a consulta

        // Verifica se a preparação da consulta falhou
        if (!$stmtUpdate) {
            die("Query preparation failed: " . $mysqli->error); // Exibe erro se falhar
        }

        // Liga os parâmetros da consulta
        $stmtUpdate->bind_param("ss", $hashedPassword, $_SESSION['username']); // Liga a nova senha e o nome de usuário

        // Executa a consulta para atualizar a senha
        if ($stmtUpdate->execute()) {
            // Se a atualização for bem-sucedida, redireciona para a página de cadastro
            header("Location: register.php");
            exit();
        } else {
            // Se houver erro na execução, define a mensagem de erro
            $erroSenha = "Erro ao alterar a senha: " . $stmtUpdate->error; // Exibe erro específico
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="/css/index.css"> <!-- Link para o CSS da página -->
</head>

<body>
    <div class="form-container">
        <p class="title">Redefinir Senha</p>
        <form class="form" method="post" action=""> <!-- O formulário será enviado para a mesma página -->
            <input type="password" class="input" id="new_password" name="new_password" placeholder="Digite a nova senha"
                required> <!-- Campo para nova senha -->
            <input type="password" class="input" id="confirm_password" name="confirm_password"
                placeholder="Repita a nova senha" required> <!-- Campo para confirmação da nova senha -->
            <button type="submit" class="form-btn">Alterar Senha</button> <!-- Botão de envio -->
        </form>
        <?php if (!empty($erroSenha)): ?> <!-- Se houver erro, exibe a mensagem -->
            <p class="error-message"><?php echo $erroSenha; ?></p> <!-- Mensagem de erro em vermelho -->
        <?php endif; ?>
    </div>
</body>

</html>