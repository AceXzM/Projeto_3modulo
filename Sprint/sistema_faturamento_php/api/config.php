<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

const DB_HOST = '127.0.0.1';
const DB_NAME = 'dashboard';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

function input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return $_POST;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

function jsonResponse(mixed $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function errorResponse(string $message, int $status = 400): never
{
    jsonResponse(['erro' => $message], $status);
}

function requireInt(mixed $value, string $field): int
{
    if (!filter_var($value, FILTER_VALIDATE_INT) && (string)$value !== '0') {
        errorResponse("Campo {$field} inválido.");
    }
    return (int)$value;
}

function requirePositiveInt(mixed $value, string $field): int
{
    $number = requireInt($value, $field);
    if ($number <= 0) {
        errorResponse("Campo {$field} deve ser maior que zero.");
    }
    return $number;
}
