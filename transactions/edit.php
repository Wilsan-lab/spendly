<?php

session_start();

require_once "../config.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Get transaction ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$transaction_id = (int) $_GET["id"];

$message = "";
$message_type = "";


// ==========================================
// GET USER CURRENCY
// ==========================================

$user_stmt = $pdo->prepare("
    SELECT currency
    FROM users
    WHERE id = ?
");

$user_stmt->execute([$user_id]);

$user_currency = $user_stmt->fetchColumn();

if (!$user_currency) {
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
// GET TRANSACTION
// ==========================================

$stmt = $pdo->prepare("
    SELECT *
    FROM transactions
    WHERE id = ? AND user_id = ?
");

$stmt->execute([
    $transaction_id,
    $user_id
]);

$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header("Location: index.php");
    exit;
}


// ==========================================
// GET CATEGORIES
// ==========================================

$category_stmt = $pdo->prepare("
    SELECT id, name, type
    FROM categories
    ORDER BY name ASC
");

$category_stmt->execute();

$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// HANDLE UPDATE
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $type = $_POST["type"] ?? "";
    $category_id = $_POST["category_id"] ?? "";
    $amount = $_POST["amount"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $transaction_date = $_POST["transaction_date"] ?? "";


    // ==========================================
    // VALIDATION
    // ==========================================

    if (
        empty($type) ||
        empty($category_id) ||
        empty($amount) ||
        empty($transaction_date)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } elseif (!in_array($type, ["income", "expense"])) {

        $message = "Invalid transaction type.";
        $message_type = "error";

    } elseif (!is_numeric($amount) || $amount <= 0) {

        $message = "Please enter a valid amount.";
        $message_type = "error";

    } else {

        // ==========================================
        // CHECK CATEGORY
        // ==========================================

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

            // ==========================================
            // UPDATE TRANSACTION
            // ==========================================

            $update = $pdo->prepare("
                UPDATE transactions
                SET
                    category_id = ?,
                    amount = ?,
                    type = ?,
                    description = ?,
                    transaction_date = ?
                WHERE id = ? AND user_id = ?
            ");

            $success = $update->execute([
                $category_id,
                $amount,
                $type,
                $description,
                $transaction_date,
                $transaction_id,
                $user_id
            ]);


            if ($success) {

                header("Location: index.php");
                exit;

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }
        }
    }


    // Keep entered values if validation fails
    $transaction["type"] = $type;
    $transaction["category_id"] = $category_id;
    $transaction["amount"] = $amount;
    $transaction["description"] = $description;
    $transaction["transaction_date"] = $transaction_date;
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

    <title>Edit Transaction - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>

<div class="app-layout">


    <!-- ==========================================
         SIDEBAR
    ========================================== -->

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


    <!-- ==========================================
         MAIN CONTENT
    ========================================== -->

    <main class="main-content">


        <div class="page-header">

            <div>

                <h1>Edit Transaction</h1>

                <p>
                    Update your transaction details.
                </p>

            </div>

        </div>


        <div class="card transaction-form-card">


            <!-- Currency information -->

            <div class="currency-display">

                Currency:
                <strong>
                    <?php echo htmlspecialchars($user_currency); ?>
                    (<?php echo htmlspecialchars($currency_symbol); ?>)
                </strong>

            </div>


            <!-- Error message -->

            <?php if (!empty($message)): ?>

                <div class="form-message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- ==========================================
                     TRANSACTION TYPE
                ========================================== -->

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
                            echo $transaction["type"] === "income"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Income
                        </option>

                        <option
                            value="expense"
                            <?php
                            echo $transaction["type"] === "expense"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Expense
                        </option>

                    </select>

                </div>


                <!-- ==========================================
                     CATEGORY
                ========================================== -->

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
                                echo $transaction["category_id"] == $category["id"]
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


                <!-- ==========================================
                     AMOUNT
                ========================================== -->

                <div class="form-group">

                    <label for="amount">
                        Amount
                        (<?php echo htmlspecialchars($currency_symbol); ?>)
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        id="amount"
                        name="amount"
                        class="form-control"
                        placeholder="Enter amount"
                        value="<?php echo htmlspecialchars($transaction["amount"]); ?>"
                        required
                    >

                </div>


                <!-- ==========================================
                     DESCRIPTION
                ========================================== -->

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
                        value="<?php echo htmlspecialchars($transaction["description"]); ?>"
                    >

                </div>


                <!-- ==========================================
                     DATE
                ========================================== -->

                <div class="form-group">

                    <label for="transaction_date">
                        Date
                    </label>

                    <input
                        type="date"
                        id="transaction_date"
                        name="transaction_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($transaction["transaction_date"]); ?>"
                        required
                    >

                </div>


                <!-- ==========================================
                     BUTTONS
                ========================================== -->

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
                        Save Changes
                    </button>

                </div>


            </form>

        </div>

    </main>

</div>


<!-- ==========================================
     CATEGORY FILTER
========================================== -->

<script>

const typeSelect = document.getElementById("type");
const categorySelect = document.getElementById("category_id");

function filterCategories() {

    const selectedType = typeSelect.value;

    const options = categorySelect.querySelectorAll("option");

    options.forEach(function(option) {

        if (option.value === "") {
            option.style.display = "";
            return;
        }

        const categoryType = option.getAttribute("data-type");

        if (selectedType === "" || categoryType === selectedType) {

            option.style.display = "";

        } else {

            option.style.display = "none";

            if (option.selected) {
                categorySelect.value = "";
            }

        }

    });
}

typeSelect.addEventListener("change", filterCategories);

filterCategories();

</script>


</body>

</html>