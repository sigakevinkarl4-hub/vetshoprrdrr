<?php
require_once __DIR__ . '/db.php';

try {
    // 1. Ensure tables exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id VARCHAR(50) DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        full_name VARCHAR(100) DEFAULT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'client',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        species VARCHAR(30) NOT NULL,
        breed VARCHAR(50) DEFAULT NULL,
        age INT DEFAULT 0,
        avatar VARCHAR(10) DEFAULT '🐾',
        vaccinated TINYINT(1) DEFAULT 0,
        adopted TINYINT(1) DEFAULT 0,
        owner VARCHAR(100) DEFAULT '',
        owner_id INT DEFAULT -1,
        price DECIMAL(10,2) DEFAULT 0.00
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS vet_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_id INT NOT NULL,
        pet_name VARCHAR(50) NOT NULL,
        record_date VARCHAR(30) DEFAULT NULL,
        date VARCHAR(30) DEFAULT NULL,
        diagnosis VARCHAR(255) NOT NULL,
        treatment VARCHAR(255) NOT NULL,
        vet VARCHAR(100) NOT NULL
    )");

    // 2. Clear existing pets and vet records
    $pdo->exec("TRUNCATE TABLE pets");
    $pdo->exec("TRUNCATE TABLE vet_records");

    // 3. Insert pets from htdocs dashboard.html
    $pets = [
        [1, 'Buddy', 'Dog', 'Aspin', 2, '🐶', 1, 0, '', -1, 500.00],
        [2, 'Mingming', 'Cat', 'Puspin', 1, '🐱', 0, 0, '', -1, 300.00],
        [3, 'Polly', 'Bird', 'Parakeet', 1, '🦜', 1, 0, '', -1, 0.00],
        [4, 'Thumper', 'Rabbit', 'Holland Lop', 2, '🐰', 1, 1, 'John Doe', 1, 800.00],
        [5, 'Max', 'Dog', 'Golden Retriever', 3, '🐕', 1, 0, '', -1, 2500.00],
        [6, 'Luna', 'Cat', 'Siamese', 2, '🐈', 1, 0, '', -1, 1200.00]
    ];

    $stmtPet = $pdo->prepare("INSERT INTO pets (id, name, species, breed, age, avatar, vaccinated, adopted, owner, owner_id, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($pets as $p) {
        $stmtPet->execute($p);
    }

    // 4. Insert vet records from htdocs dashboard.html
    $records = [
        [1, 1, 'Buddy', '2024-06-10', '2024-06-10', 'Annual checkup', 'Vaccination', 'Dr. Cruz'],
        [2, 2, 'Mingming', '2024-07-05', '2024-07-05', 'Dental cleaning', 'Scaling', 'Dr. Reyes'],
        [3, 4, 'Thumper', '2024-08-12', '2024-08-12', 'Ear infection', 'Antibiotics', 'Dr. Santos']
    ];

    $stmtRec = $pdo->prepare("INSERT INTO vet_records (id, pet_id, pet_name, record_date, date, diagnosis, treatment, vet) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($records as $r) {
        $stmtRec->execute($r);
    }

    // 5. Insert default user if empty
    $userCheck = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCheck == 0) {
        $stmtUser = $pdo->prepare("INSERT INTO users (id, client_id, name, full_name, email, phone, password, role) VALUES (1, 'client2', 'John Doe', 'John Doe', 'client@vetshoppe.com', '+63 917 123 4567', 'password123', 'client')");
        $stmtUser->execute();
    }

    echo json_encode([
        "success" => true,
        "message" => "Database successfully seeded with all 6 pets from htdocs dashboard!",
        "pets_count" => count($pets),
        "vet_records_count" => count($records)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Seed failed: " . $e->getMessage()]);
}
?>