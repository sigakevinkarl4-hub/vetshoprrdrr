<?php
require 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { echo json_encode(['success' => false, 'error' => 'Invalid input']); exit; }

try {
    $stmt = $pdo->prepare("
        INSERT INTO vet_records (pet_id, pet_name, diagnosis, treatment, vet, date)
        VALUES (:pet_id, :pet_name, :diagnosis, :treatment, :vet, :date)
    ");
    $stmt->execute([
        ':pet_id'    => $input['pet_id'],
        ':pet_name'  => $input['pet_name'],
        ':diagnosis' => $input['diagnosis'],
        ':treatment' => $input['treatment'],
        ':vet'       => $input['vet'],
        ':date'      => $input['date'],
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>