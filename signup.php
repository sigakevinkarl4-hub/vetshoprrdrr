<?php

header('Content-Type: application/json');

// Read the JSON body that Android sends
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { $data = $_POST; } // fallback

// Overwrite $_POST so the rest of your existing code works unchanged
$_POST = $data;

header("Content-Type: application/json");
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if(empty($name) || empty($email) || empty($password)){
    echo json_encode([
        "success"=>false,
        "message"=>"All fields are required."
    ]);
    exit;
}

$hash = password_hash($password,PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO accounts(full_name,email,password,role)
     VALUES(?,?,?,'client')"
);

mysqli_stmt_bind_param(
    $stmt,
    "sss",
    $name,
    $email,
    $hash
);

if(mysqli_stmt_execute($stmt)){
    echo json_encode([
        "success"=>true,
        "message"=>"Signup successful"
    ]);
}else{
    echo json_encode([
        "success"=>false,
        "message"=>mysqli_error($conn)
    ]);
}