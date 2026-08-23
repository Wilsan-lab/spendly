<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/currency.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_currency = "USD";

if (isset($_SESSION["user_id"])) {

    $stmt = $pdo->prepare(
        "SELECT currency FROM users WHERE id = ?"
    );

    $stmt->execute([$_SESSION["user_id"]]);

    $user_currency = $stmt->fetchColumn();

    if ($user_currency) {
        $current_currency = $user_currency;
    }
}

$currency_symbol = getCurrencySymbol($current_currency);

?>