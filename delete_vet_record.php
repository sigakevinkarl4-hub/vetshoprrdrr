<?php
require 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) { echo json_encode(['success' => false, 'error' => 'ID required']); exit; }

try {
    $stmt = $pdo->prepare("DELETE FROM vet_records WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>