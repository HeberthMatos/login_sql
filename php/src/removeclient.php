<?php
session_start();
$mysqli = new mysqli("db", "HEBERTH", "BIBI7979DB", "LOGIN_DB");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Consulta para remover o cliente pelo ID
    $sql = "DELETE FROM clientes WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: register.php"); // Redireciona para a página inicial após a remoção
        exit;
    } else {
        echo "Erro ao remover o cliente.";
    }
}
?>
