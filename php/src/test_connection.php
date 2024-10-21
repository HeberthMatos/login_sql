<?php
$host = 'db';
$user = 'root';  // Use o usuário correto (pode ser 'login_user' se estiver tentando com outro)
$password = 'ADMIN';  // A senha que você configurou no docker-compose.yml
$database = 'LOGIN_DB';  // Nome do banco de dados

$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
echo "Connected successfully!";
?>
