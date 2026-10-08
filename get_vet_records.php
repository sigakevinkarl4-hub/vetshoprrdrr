<?php
require_once __DIR__ . '/db.php';

try {
    $petId = isset($_GET['petId']) ? (int)$_GET['petId'] : (isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : null);

    $query = "SELECT id, pet_id AS petId, pet_name AS petName, diagnosis, treatment, vet, COALESCE(record_date, date) AS date FROM vet_records";
    $params = [];

    if ($petId) {
        $query .= " WHERE pet_id = :petId";
        $params[':petId'] = $petId;
    }

    $query .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    foreach ($records as &$rec) {
        $rec['id'] = (int)$rec['id'];
        $rec['petId'] = (int)$rec['petId'];
    }

    echo json_encode(["success" => true, "data" => $records]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>