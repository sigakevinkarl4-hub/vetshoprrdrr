<?php

header("Content-Type: application/json");
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM accounts WHERE email=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if($user = mysqli_fetch_assoc($result)){

    if(password_verify(
        $password,
        $user['password']
    )){

        echo json_encode([
            "success"=>true,
            "role"=>$user['role'],
            "name"=>$user['full_name']
        ]);

    }else{

        echo json_encode([
            "success"=>false,
            "message"=>"Wrong password"
        ]);

    }

}else{

    echo json_encode([
        "success"=>false,
        "message"=>"Email not found"
    ]);

}