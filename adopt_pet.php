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
$clientId = isset($input['clientId']) ? trim($input['clientId']) : '';

if (!$petId) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid Pet ID"]);
    exit;
}

try {
    $checkStmt = $pdo->prepare("SELECT id, name, price, adopted FROM pets WHERE id = :id");
    $checkStmt->execute([':id' => $petId]);
    $pet = $checkStmt->fetch();

    if (!$pet) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pet not found"]);
        exit;
    }

    $ownerName = $clientId;
    if ($userId > 0) {
        $uStmt = $pdo->prepare("SELECT full_name, name FROM users WHERE id = :id");
        $uStmt->execute([':id' => $userId]);
        $userRow = $uStmt->fetch();
        if ($userRow) {
            $ownerName = !empty($userRow['full_name']) ? $userRow['full_name'] : $userRow['name'];
        }
    }

    if (empty($ownerName)) {
        $ownerName = "Client #" . ($userId > 0 ? $userId : 1);
    }

    $updateStmt = $pdo->prepare("UPDATE pets SET adopted = 1, owner = :owner, owner_id = :owner_id WHERE id = :id");
    $updateStmt->execute([
        ':owner' => $ownerName,
        ':owner_id' => $userId > 0 ? $userId : 1,
        ':id' => $petId
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Successfully adopted " . $pet['name'],
        "data" => ["petId" => $petId, "owner" => $ownerName, "owner_id" => $userId]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Adoption failed: " . $e->getMessage()]);
}
?>