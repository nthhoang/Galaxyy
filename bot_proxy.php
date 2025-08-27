<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$message = $data['message'] ?? '';

$client = new Client();
try {
    $response = $client->post('http://127.0.0.1:5001/chat', [
        'json' => ['message' => $message]
    ]);
    echo $response->getBody();
} catch (Exception $e) {
    echo json_encode(['reply' => 'Lỗi kết nối chatbot: ' . $e->getMessage()]);
}
?>
