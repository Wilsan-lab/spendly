<?php

session_start();

require_once "../config.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Check that a transaction ID was provided
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$transaction_id = (int) $_GET["id"];

// Delete only a transaction belonging to the logged-in user
$stmt = $pdo->prepare("
    DELETE FROM transactions
    WHERE id = ? AND user_id = ?
");

$stmt->execute([
    $transaction_id,
    $user_id
]);

// Return to transactions page
header("Location: index.php");
exit;