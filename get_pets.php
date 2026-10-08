<?php
require_once __DIR__ . '/db.php';

try {
    $species = isset($_GET['species']) ? trim($_GET['species']) : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $adopted = isset($_GET['adopted']) ? $_GET['adopted'] : null;

    $query = "SELECT id, name, species, breed, age, price, vaccinated, adopted, owner, COALESCE(owner_id, -1) AS owner_id, avatar FROM pets WHERE 1=1";
    $params = [];

    if ($species !== 'all' && !empty($species)) {
        $query .= " AND species = :species";
        $params[':species'] = $species;
    }

    if (!empty($search)) {
        $query .= " AND (name LIKE :search OR breed LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if ($adopted !== null) {
        $query .= " AND adopted = :adopted";
        $params[':adopted'] = ($adopted === 'true' || $adopted === '1') ? 1 : 0;
    }

    $query .= " ORDER BY id ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $pets = $stmt->fetchAll();

    foreach ($pets as &$pet) {
        $pet['id'] = (int)$pet['id'];
        $pet['age'] = (int)$pet['age'];
        $pet['price'] = (float)$pet['price'];
        $pet['vaccinated'] = (bool)$pet['vaccinated'];
        $pet['adopted'] = (bool)$pet['adopted'];
        $pet['owner_id'] = (int)$pet['owner_id'];
    }

    echo json_encode(["success" => true, "data" => $pets]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>