<?php

session_start();

require_once "../config.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}


// ==========================================
// CHECK CATEGORY ID
// ==========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];


// ==========================================
// CHECK IF CATEGORY EXISTS
// ==========================================

$stmt = $pdo->prepare("
    SELECT id
    FROM categories
    WHERE id = ?
");

$stmt->execute([$id]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: index.php");
    exit;
}


// ==========================================
// CHECK IF CATEGORY IS USED
// ==========================================

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM transactions
    WHERE category_id = ?
");

$stmt->execute([$id]);

$transaction_count = (int) $stmt->fetchColumn();


// ==========================================
// DELETE CATEGORY
// ==========================================

if ($transaction_count > 0) {

    // Category is being used by transactions.
    // Do not delete it.

    header("Location: index.php");
    exit;
}


// Delete category
$stmt = $pdo->prepare("
    DELETE FROM categories
    WHERE id = ?
");

$stmt->execute([$id]);


// Return to categories page
header("Location: index.php");
exit;

?>