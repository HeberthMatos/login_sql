<?php
session_start();
$mysqli = new mysqli("db", "HEBERTH", "BIBI7979DB", "LOGIN_DB");

// Verificar se o ID foi passado e carregar os dados do cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    $sql = "SELECT * FROM clientes WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_client'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];

    // Consulta para atualizar os dados do cliente
    $sql = "UPDATE clientes SET nome = ?, telefone = ?, endereco = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssi", $nome, $telefone, $endereco, $id);

    if ($stmt->execute()) {
        header("Location: register.php"); // Redireciona após a edição
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="/css/edits.css">
</head>

<body>
    <form method="post" action="editclient.php">
        <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required style="text-transform: uppercase;"
            value=" <?php echo htmlspecialchars($cliente['nome']); ?>" required>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone']); ?>"
            required>

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" required style="text-transform: uppercase;"
            value=" <?php echo htmlspecialchars($cliente['endereco']); ?>" required>

        <div class="button-container">
            <button type="submit" name="update_client" class="btn">Atualizar Cliente</button>
            <button type="button" class="btn" onclick="window.location.href='register.php';">Voltar</button>
        </div>
    </form>

    <script>
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
        });</script>
</body>

</html>