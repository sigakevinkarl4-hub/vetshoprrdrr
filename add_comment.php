<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$petId = isset($input['pet_id']) ? (int)$input['pet_id'] : (isset($input['petId']) ? (int)$input['petId'] : 0);
$userId = isset($input['user_id']) ? (int)$input['user_id'] : (isset($input['userId']) ? (int)$input['userId'] : 0);
$userName = isset($input['user_name']) ? trim($input['user_name']) : (isset($input['userName']) ? trim($input['userName']) : 'Client');
$comment = isset($input['comment']) ? trim($input['comment']) : '';

if (!$petId || empty($comment)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Pet ID and comment content are required."]);
    exit;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_id INT NOT NULL,
        user_id INT NOT NULL,
        user_name VARCHAR(100) NOT NULL,
        comment TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->prepare("INSERT INTO comments (pet_id, user_id, user_name, comment) VALUES (:pet_id, :user_id, :user_name, :comment)");
    $stmt->execute([
        ':pet_id' => $petId,
        ':user_id' => $userId > 0 ? $userId : 1,
        ':user_name' => $userName,
        ':comment' => $comment
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Comment posted successfully",
        "id" => (int)$pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to post comment: " . $e->getMessage()]);
}
?>