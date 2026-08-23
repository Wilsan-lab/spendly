<?php

session_start();

require_once "../config.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


// ==========================================
// GET USER CURRENCY
// ==========================================

$stmt = $pdo->prepare(
    "SELECT currency FROM users WHERE id = ?"
);

$stmt->execute([$user_id]);

$user_currency = $stmt->fetchColumn();

// Default currency
if (empty($user_currency)) {
    $user_currency = "USD";
}


// Currency symbols
$currency_symbols = [
    "USD" => "$",
    "EUR" => "€",
    "TRY" => "₺",
    "DJF" => "Fdj"
];

$currency_symbol = $currency_symbols[$user_currency] ?? "$";


// ==========================================
// GET CATEGORIES
// ==========================================

$stmt = $pdo->prepare("
    SELECT id, name, type
    FROM categories
    ORDER BY name ASC
");

$stmt->execute();

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// ADD TRANSACTION
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $type = $_POST["type"] ?? "";
    $category_id = $_POST["category_id"] ?? "";
    $amount = $_POST["amount"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $transaction_date = $_POST["transaction_date"] ?? "";


    // Check required fields
    if (
        empty($type) ||
        empty($category_id) ||
        empty($amount) ||
        empty($transaction_date)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    }

    // Check transaction type
    elseif (!in_array($type, ["income", "expense"], true)) {

        $message = "Invalid transaction type.";
        $message_type = "error";

    }

    // Check amount
    elseif (!is_numeric($amount) || $amount <= 0) {

        $message = "Please enter a valid amount.";
        $message_type = "error";

    }

    else {

        // Check that the selected category exists
        $category_check = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = ? AND type = ?
        ");

        $category_check->execute([
            $category_id,
            $type
        ]);

        $category = $category_check->fetch(PDO::FETCH_ASSOC);


        if (!$category) {

            $message = "Please select a valid category.";
            $message_type = "error";

        } else {

            // Insert transaction
            $stmt = $pdo->prepare("
                INSERT INTO transactions
                (
                    user_id,
                    category_id,
                    amount,
                    type,
                    description,
                    transaction_date
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $success = $stmt->execute([
                $user_id,
                $category_id,
                $amount,
                $type,
                $description,
                $transaction_date
            ]);


            if ($success) {

                header("Location: ../dashboard/index.php");
                exit;

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Transaction - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>

<div class="app-layout">


    <!-- ========================================== -->
    <!-- SIDEBAR -->
    <!-- ========================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">
            Spendly
        </div>


        <nav class="sidebar-nav">

            <a href="../dashboard/index.php">
                Dashboard
            </a>

            <a href="index.php" class="active">
                Transactions
            </a>

            <a href="../categories/index.php">
                Categories
            </a>

            <a href="../reports/index.php">
                Reports
            </a>

            <a href="../settings/index.php">
                Settings
            </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="../auth/logout.php">
                Logout
            </a>

        </div>

    </aside>


    <!-- ========================================== -->
    <!-- MAIN CONTENT -->
    <!-- ========================================== -->

    <main class="main-content">


        <!-- Page Header -->

        <div class="page-header">

            <div>

                <h1>
                    Add Transaction
                </h1>

                <p>
                    Record your income or expenses.
                </p>

            </div>

        </div>


        <!-- ========================================== -->
        <!-- FORM CARD -->
        <!-- ========================================== -->

        <div class="card transaction-form-card">


            <?php if (!empty($message)): ?>

                <div class="form-message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- Transaction Type -->

                <div class="form-group">

                    <label for="type">
                        Transaction Type
                    </label>

                    <select
                        id="type"
                        name="type"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select type
                        </option>

                        <option
                            value="income"
                            <?php
                            echo (($_POST["type"] ?? "") === "income")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Income
                        </option>

                        <option
                            value="expense"
                            <?php
                            echo (($_POST["type"] ?? "") === "expense")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Expense
                        </option>

                    </select>

                </div>


                <!-- Category -->

                <div class="form-group">

                    <label for="category_id">
                        Category
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select category
                        </option>


                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php echo $category["id"]; ?>"
                                data-type="<?php echo htmlspecialchars($category["type"]); ?>"
                                <?php
                                echo (
                                    ($_POST["category_id"] ?? "") == $category["id"]
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >

                                <?php
                                echo htmlspecialchars($category["name"]);
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Amount -->

                <div class="form-group">

                    <label for="amount">
                        Amount (<?php echo htmlspecialchars($user_currency); ?>)
                    </label>

                    <div class="amount-input-wrapper">

                        <span class="currency-symbol">
                            <?php echo htmlspecialchars($currency_symbol); ?>
                        </span>

                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            class="form-control amount-input"
                            placeholder="Enter amount"
                            step="0.01"
                            min="0.01"
                            value="<?php echo htmlspecialchars($_POST["amount"] ?? ""); ?>"
                            required
                        >

                    </div>

                </div>


                <!-- Description -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <input
                        type="text"
                        id="description"
                        name="description"
                        class="form-control"
                        placeholder="What was this transaction?"
                        value="<?php echo htmlspecialchars($_POST["description"] ?? ""); ?>"
                    >

                </div>


                <!-- Date -->

                <div class="form-group">

                    <label for="transaction_date">
                        Date
                    </label>

                    <input
                        type="date"
                        id="transaction_date"
                        name="transaction_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($_POST["transaction_date"] ?? date("Y-m-d")); ?>"
                        required
                    >

                </div>


                <!-- Buttons -->

                <div class="form-actions">

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add Transaction
                    </button>

                </div>


            </form>

        </div>


    </main>

</div>

</body>

</html>