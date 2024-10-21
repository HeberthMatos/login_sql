<?php
// Verificar se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Inicia a sessão se ainda não estiver ativa
    session_start();
}

// Conexão com o banco de dados
$mysqli = new mysqli("db", "HEBERTH", "BIBI7979DB", "LOGIN_DB");

// Verificar conexão
if ($mysqli->connect_error) {
    // Se houver erro na conexão, termina o script e exibe a mensagem de erro
    die("Connection failed: " . $mysqli->connect_error);
}

$erroLogin = ""; // Inicializa a variável de erro para login

// Verificar se o formulário de login foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['login_username']; // Obtém o nome de usuário (CPF) do formulário
    $password = $_POST['login_password']; // Obtém a senha do formulário

    // Consulta para selecionar o usuário com base no nome de usuário
    $sql = "SELECT * FROM users WHERE username = ?"; // SQL para buscar usuário pelo CPF
    $stmt = $mysqli->prepare($sql); // Prepara a consulta

    // Verifica se a preparação da consulta falhou
    if (!$stmt) {
        die("Query preparation failed: " . $mysqli->error); // Exibe erro se falhar
    }

    $stmt->bind_param("s", $username); // Liga o parâmetro da consulta (CPF)
    $stmt->execute(); // Executa a consulta
    $result = $stmt->get_result(); // Obtém o resultado da consulta

    // Verifica se um usuário foi encontrado
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Armazena os dados do usuário encontrado

        // Verifica se a senha fornecida é válida
        if (password_verify($password, $user['password'])) {
            // Armazena informações do usuário na sessão
            $_SESSION['username'] = $username; // Armazena o nome de usuário (CPF)
            $_SESSION['user_id'] = $user['id']; // Armazena o ID do usuário

            header("Location: resetpass.php"); // Redireciona para a página de redefinição de senha
            exit(); // Garantir que o script pare após o redirecionamento
        } else {
            // Se a senha estiver incorreta, define a mensagem de erro
            $erroLogin = "Senha inválida! Deseja redefinir? Entre em contato com o administrador.";
        }
    } else {
        // Se nenhum usuário for encontrado, define a mensagem de erro
        $erroLogin = "Nenhum usuário encontrado!";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verifica se o usuário selecionou a opção "Lembrar de mim"
        if (isset($_POST['remember_me'])) {
            // Define um cookie para lembrar o usuário (exemplo de 30 dias)
            setcookie('login_username', $_POST['login_username'], time() + (30 * 24 * 60 * 60), "/"); // 30 dias
            // Note que é importante também armazenar a senha de forma segura, geralmente você não deve armazenar senhas em cookies.
        } else {
            // Se não, apaga o cookie
            setcookie('login_username', '', time() - 3600, "/");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/css/index.css"> <!-- Link para o CSS -->
</head>

<body>
    <div class="form-container">
        <p class="title">Entrar na sua conta</p>
        <form class="form" method="post" action=""> <!-- O formulário será enviado para a mesma página -->
            <input type="hidden" name="login" value="1"> <!-- Campo oculto para identificar o envio do formulário -->
            <input type="text" class="input" id="login_username" maxlength="11" name="login_username"
                placeholder="Digite seu CPF" required> <!-- Campo para CPF -->
            <input type="password" class="input" id="login_password" name="login_password"
                placeholder="Digite sua senha" required> <!-- Campo para senha -->
            <div class="remember-me-container">
                <input type="checkbox" id="remember_me" name="remember_me"> <!-- Checkbox para "Lembrar de mim" -->
                <label class="remember-me-label" for="remember_me">Lembrar Usuário</label>
                <!-- Label associado ao checkbox -->
            </div>
            <button type="submit" class="form-btn">Login</button> <!-- Botão de envio -->
        </form>

        <!-- Exibir mensagem de erro do login -->
        <?php if (!empty($erroLogin)): ?> <!-- Se houver erro, exibe a mensagem -->
            <p class="error-message"><?php echo $erroLogin; ?></p> <!-- Mensagem de erro em vermelho -->
        <?php endif; ?>
    </div>
</body>

</html>