<?php

$host   = "localhost";
$dbname = "dashboard";
$user   = "root";
$pass   = "";
        
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna os resultados como array associativo
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Define o código HTTP de erro de servidor
    http_response_code(500);
    echo json_encode(["error" => "Erro de conexão com o banco de dados"]);
    exit;
}