<?php
session_start();

// Conexão com o banco de dados MySQL
$mysqli = new mysqli("db", "HEBERTH", "BIBI7979DB", "LOGIN_DB");

// Verificar conexão
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redireciona para a página de login se não estiver logado
    exit;
}

// Inicializa as variáveis
$result = null;
$searchResult = null;
$openModal = false; // Variável para controlar a abertura do modal

// Processar o cadastro de cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_client'])) {
    $nome = strtoupper($_POST['nome']);
    $telefone = $_POST['telefone'];
    $endereco = strtoupper($_POST['endereco']);
    // Consulta para inserir um novo cliente
    $sql = "INSERT INTO clientes (nome, telefone, endereco) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sss", $nome, $telefone, $endereco);
    if ($stmt->execute()) {
        $_SESSION['success_client'] = "Cliente cadastrado com sucesso!";
    } else {
        $_SESSION['error_client'] = "Erro ao cadastrar cliente: " . $stmt->error;
    }

    // Redireciona para a mesma página para evitar reenvio
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Para garantir que o script pare aqui
}

// Processar o cadastro de usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Criptografa a senha
    
    // Verifica se o nome de usuário já existe
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $stmt_check = $mysqli->prepare($check_sql);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        // Se o usuário já estiver cadastrado
        $_SESSION['error_user'] = "Usuário já cadastrado!";
    } else {
        // Se o usuário não existir, faz a inserção
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        
        if ($stmt->execute()) {
            $_SESSION['success_user'] = "Usuário cadastrado com sucesso!";
        }
    }

    // Redireciona para a mesma página para evitar reenvio
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Para garantir que o script pare aqui
}


// Processar a pesquisa de clientes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_submit'])) {
    $search = $_POST['search'];
    if (!empty($search)) {
        $sql = "SELECT * FROM clientes WHERE nome LIKE ? OR telefone LIKE ? OR endereco LIKE ?";
        $stmt = $mysqli->prepare($sql);
        $searchParam = "%" . $search . "%";
        $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        $stmt->execute();
        $searchResult = $stmt->get_result(); // Armazena resultados da pesquisa
    } else {
        $searchResult = null; // Caso a pesquisa esteja vazia, define como null
    }
    $openModal = true; // Define que o modal deve abrir após a pesquisa
}

// Processar a remoção de clientes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_client'])) {
    $id = $_POST['id'];
    // Consulta para remover o cliente
    $sql = "DELETE FROM clientes WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success_client'] = "Cliente removido com sucesso!";
    } else {
        $_SESSION['error_client'] = "Erro ao remover cliente: " . $stmt->error;
    }
    $openModal = true; // Mantém o modal aberto
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Para garantir que o script pare aqui
}

// Carrega todos os clientes ao abrir a página
if (!$searchResult) {
    $sql = "SELECT * FROM clientes"; // Sem filtro
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Função para renderizar a tabela de clientes
function renderClientTable($result)
{
    ob_start(); // Inicia o buffer de saída
    ?>
    <div id="client-table-container">
        <table id="client-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Endereço</th>
                    <th>Ações</th> <!-- Nova coluna para ações -->
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($row['endereco']); ?></td>
                            <td>
                                <!-- Botão de Editar -->
                                <form method="post" action="editclient.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit">Editar</button>
                                </form>
                                <!-- Botão de Remover com confirmação -->
                                <form method="post" action="removeclient.php" style="display: inline;"
                                    onsubmit="return confirm('Tem certeza que deseja remover este cliente?');">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="remove_client">Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Nenhum cliente encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean(); // Retorna o conteúdo do buffer
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Sistema</title>
    <link rel="stylesheet" href="/css/registers.css">
</head>

<body>
    <div class="container">
        <!-- Menu lateral -->
        <div class="menu-lateral">
            <img src="/img/logo.png" alt="Logo" class="logo"> <!-- Adicione a logo aqui -->
            <a href="#" onclick="showForm('register-user'); return false;">Registro de Usuário</a>
            <a href="#" onclick="showForm('register-client'); return false;">Registro de Cliente</a>
            <!-- Link para abrir o modal -->
            <a href="#" id="open-modal">Ver Clientes</a>
            <a href="index.php"><img src="/img/homeicon.png" alt="Sair" class="home-icon"> Sair</a>
        </div>

        <!-- Formulário de Cadastro de Usuário -->
        <div id="register-user" class="form-container" style="display: none;">
            <p class="title">Cadastro de Usuário</p>
            <form method="post" action="">
                <div class="input-container">
                    <label for="username">Usuário:</label>
                    <input type="text" class="input" id="username" name="username" required>
                </div>
                <div class="input-container">
                    <label for="password">Senha:</label>
                    <input type="password" class="input" id="password" name="password" required>
                </div>
                <button type="submit" class="form-btn" name="register_user">Cadastrar Usuário</button>
                <?php
                if (isset($_SESSION['success_user'])) {
                    echo "<div class='alert alert-success'>" . $_SESSION['success_user'] . "</div>";
                    unset($_SESSION['success_user']); // Remove a mensagem após exibir
                }

                if (isset($_SESSION['error_user'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['error_user'] . "</div>";
                    unset($_SESSION['error_user']); // Remove a mensagem após exibir
                }
                ?>
            </form>
        </div>

        <!-- Formulário de Cadastro de Cliente -->
        <div id="register-client" class="form-container" style="display: none;">
            <p class="title">Cadastro de Cliente</p>
            <form method="post" action="">
                <div class="input-container">
                    <label for="nome">Nome:</label>
                    <input type="text" class="input" id="nome" name="nome" required style="text-transform: uppercase;">
                </div>
                <div class="input-container">
                    <label for="telefone">Telefone:</label>
                    <input type="text" class="input" id="telefone" name="telefone" required>
                </div>
                <div class="input-container">
                    <label for="endereco">Endereço:</label>
                    <input type="text" class="input" id="endereco" name="endereco" required
                        style="text-transform: uppercase;">
                </div>
                <button type="submit" class="form-btn" name="register_client">Cadastrar Cliente</button>
                <?php
                if (isset($_SESSION['success_client'])) {
                    echo "<div class='alert alert-success'>" . $_SESSION['success_client'] . "</div>";
                    unset($_SESSION['success_client']); // Remove a mensagem após exibir
                }

                if (isset($_SESSION['success_client'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['success_client'] . "</div>";
                    unset($_SESSION['success_client']); // Remove a mensagem após exibir
                }
                ?>
            </form>
        </div>

        <!-- Modal de Clientes -->
        <div id="client-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" id="close-modal">&times;</span>
                <form method="post" action="">
                    <input type="text" class="search-bar" placeholder="Pesquisar..." name="search">
                    <button type="submit" class="" name="search_submit">Pesquisar</button>
                </form>
                <?php
                // Exibe a tabela de clientes com os resultados
                echo renderClientTable($searchResult ?? $result);
                ?>
            </div>
        </div>
    </div>

    <script>
        // Função para mostrar os formulários
        function showForm(formId) {
            var forms = document.getElementsByClassName('form-container');
            for (var i = 0; i < forms.length; i++) {
                forms[i].style.display = 'none';
            }
            document.getElementById(formId).style.display = 'block';
        }

        // Modal de Clientes
        var modal = document.getElementById("client-modal");
        var btn = document.getElementById("open-modal");
        var span = document.getElementById("close-modal");

        btn.onclick = function () {
            modal.style.display = "block";
        }

        span.onclick = function () {
            modal.style.display = "none";
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Função para formatar o telefone
        const telefoneInput = document.getElementById('telefone');
        telefoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não for dígito
            if (value.length > 10) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3'); // Formato com 9 dígitos
            } else if (value.length > 5) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3'); // Formato com 8 dígitos
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2'); // Formato parcial
            } else {
                value = value.replace(/(\d*)/, '($1'); // Formato básico
            }
            e.target.value = value;
        });

        // Abre o modal após a pesquisa se necessário
        <?php if ($openModal): ?>
            document.addEventListener("DOMContentLoaded", function () {
                modal.style.display = "block"; // Mantém o modal aberto após a pesquisa
            });
        <?php endif; ?>
    </script>
</body>

</html>