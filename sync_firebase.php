<?php
require_once __DIR__ . '/db.php';

header("Content-Type: application/json; charset=UTF-8");

$FIREBASE_URL = "https://vetshoppe-db-default-rtdb.firebaseio.com";

function fetchFromFirebase($node) {
    global $FIREBASE_URL;
    $url = $FIREBASE_URL . "/" . $node . ".json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }
    return null;
}

try {
    $syncedPets = 0;
    $syncedRecords = 0;
    $syncedUsers = 0;

    // 1. Sync Pets from Firebase
    $petsData = fetchFromFirebase("pets");
    if ($petsData && is_array($petsData)) {
        $stmtPet = $pdo->prepare("INSERT INTO pets (id, name, species, breed, age, avatar, vaccinated, adopted, owner, owner_id, price)
            VALUES (:id, :name, :species, :breed, :age, :avatar, :vaccinated, :adopted, :owner, :owner_id, :price)
            ON DUPLICATE KEY UPDATE
            name = VALUES(name), species = VALUES(species), breed = VALUES(breed), age = VALUES(age),
            avatar = VALUES(avatar), vaccinated = VALUES(vaccinated), adopted = VALUES(adopted),
            owner = VALUES(owner), owner_id = VALUES(owner_id), price = VALUES(price)");

        foreach ($petsData as $p) {
            if (!$p || !is_array($p)) continue;
            $stmtPet->execute([
                ':id' => isset($p['id']) ? (int)$p['id'] : 0,
                ':name' => isset($p['name']) ? $p['name'] : 'Unknown',
                ':species' => isset($p['species']) ? $p['species'] : 'Dog',
                ':breed' => isset($p['breed']) ? $p['breed'] : '',
                ':age' => isset($p['age']) ? (int)$p['age'] : 1,
                ':avatar' => isset($p['avatar']) ? $p['avatar'] : '🐾',
                ':vaccinated' => !empty($p['vaccinated']) ? 1 : 0,
                ':adopted' => !empty($p['adopted']) ? 1 : 0,
                ':owner' => isset($p['owner']) ? $p['owner'] : '',
                ':owner_id' => isset($p['ownerId']) ? (int)$p['ownerId'] : (isset($p['owner_id']) ? (int)$p['owner_id'] : -1),
                ':price' => isset($p['price']) ? (float)$p['price'] : 0.00
            ]);
            $syncedPets++;
        }
    }

    // 2. Sync Vet Records from Firebase
    $recordsData = fetchFromFirebase("vet_records");
    if ($recordsData && is_array($recordsData)) {
        $stmtRec = $pdo->prepare("INSERT INTO vet_records (id, pet_id, pet_name, record_date, date, diagnosis, treatment, vet)
            VALUES (:id, :pet_id, :pet_name, :record_date, :date, :diagnosis, :treatment, :vet)
            ON DUPLICATE KEY UPDATE
            pet_id = VALUES(pet_id), pet_name = VALUES(pet_name), record_date = VALUES(record_date),
            date = VALUES(date), diagnosis = VALUES(diagnosis), treatment = VALUES(treatment), vet = VALUES(vet)");

        foreach ($recordsData as $r) {
            if (!$r || !is_array($r)) continue;
            $recDate = isset($r['date']) ? $r['date'] : (isset($r['record_date']) ? $r['record_date'] : date('Y-m-d'));
            $stmtRec->execute([
                ':id' => isset($r['id']) ? (int)$r['id'] : 0,
                ':pet_id' => isset($r['petId']) ? (int)$r['petId'] : (isset($r['pet_id']) ? (int)$r['pet_id'] : 0),
                ':pet_name' => isset($r['petName']) ? $r['petName'] : (isset($r['pet_name']) ? $r['pet_name'] : ''),
                ':record_date' => $recDate,
                ':date' => $recDate,
                ':diagnosis' => isset($r['diagnosis']) ? $r['diagnosis'] : '',
                ':treatment' => isset($r['treatment']) ? $r['treatment'] : '',
                ':vet' => isset($r['vet']) ? $r['vet'] : 'Dr. Cruz'
            ]);
            $syncedRecords++;
        }
    }

    // 3. Sync Users from Firebase
    $usersData = fetchFromFirebase("users");
    if ($usersData && is_array($usersData)) {
        $stmtUser = $pdo->prepare("INSERT INTO users (id, name, full_name, email, phone, password, role)
            VALUES (:id, :name, :full_name, :email, :phone, :password, 'client')
            ON DUPLICATE KEY UPDATE
            name = VALUES(name), full_name = VALUES(full_name), phone = VALUES(phone)");

        foreach ($usersData as $u) {
            if (!$u || !is_array($u)) continue;
            $name = isset($u['fullName']) ? $u['fullName'] : (isset($u['full_name']) ? $u['full_name'] : (isset($u['name']) ? $u['name'] : 'User'));
            $email = isset($u['email']) ? strtolower(trim($u['email'])) : '';
            if (empty($email)) continue;

            $stmtUser->execute([
                ':id' => isset($u['id']) ? (int)$u['id'] : 0,
                ':name' => $name,
                ':full_name' => $name,
                ':email' => $email,
                ':phone' => isset($u['phone']) ? $u['phone'] : '',
                ':password' => isset($u['password']) ? $u['password'] : 'password123'
            ]);
            $syncedUsers++;
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Firebase synced to MySQL (phpMyAdmin) successfully!",
        "synced" => [
            "pets" => $syncedPets,
            "vet_records" => $syncedRecords,
            "users" => $syncedUsers
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Sync error: " . $e->getMessage()]);
}
?>