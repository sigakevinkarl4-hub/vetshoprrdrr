<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "vetshoppe";

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$conn) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]));
}
?>