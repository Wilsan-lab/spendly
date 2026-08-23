<?php

session_start();

require_once "../config.php";
require_once "../includes/currency.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


// ==========================================
// GET USER CURRENCY + THEME
// ==========================================

$stmt = $pdo->prepare(
    "SELECT currency, theme FROM users WHERE id = ?"
);

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}


// Currency
$user_currency = $user["currency"] ?? "USD";

$currency_symbol = getCurrencySymbol($user_currency);


// Theme
$user_theme = $user["theme"] ?? "light";

if (!in_array($user_theme, ["light", "dark"], true)) {
    $user_theme = "light";
}


// ==========================================
// GET ALL TRANSACTIONS
// ==========================================

$stmt = $pdo->prepare("
    SELECT 
        transactions.id,
        transactions.amount,
        transactions.type,
        transactions.description,
        transactions.transaction_date,
        categories.name AS category_name
    FROM transactions
    INNER JOIN categories
        ON transactions.category_id = categories.id
    WHERE transactions.user_id = ?
    ORDER BY transactions.transaction_date DESC,
             transactions.id DESC
");

$stmt->execute([$user_id]);

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transactions - Spendly</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>


<body class="<?php echo $user_theme === 'dark' ? 'dark-mode' : ''; ?>">


<div class="app-layout">


    <!-- ========================================== -->
    <!-- SIDEBAR -->
    <!-- ========================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">
            Spendly
        </div>


        <nav class="sidebar-nav">

            <a
                href="../dashboard/index.php"
                class="nav-item"
            >
                Dashboard
            </a>


            <a
                href="index.php"
                class="nav-item active"
            >
                Transactions
            </a>


            <a
                href="../categories/index.php"
                class="nav-item"
            >
                Categories
            </a>


            <a
                href="../reports/index.php"
                class="nav-item"
            >
                Reports
            </a>


            <a
                href="../settings/index.php"
                class="nav-item"
            >
                Settings
            </a>

        </nav>


        <div class="sidebar-bottom">

            <a
                href="../auth/logout.php"
                class="nav-item"
            >
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
                    Transactions
                </h1>

                <p>
                    View and manage all your transactions.
                </p>

            </div>


            <a
                href="add.php"
                class="btn btn-primary"
            >
                + Add Transaction
            </a>

        </div>


        <!-- ========================================== -->
        <!-- TRANSACTIONS CARD -->
        <!-- ========================================== -->

        <div class="card">


            <?php if (count($transactions) > 0): ?>


                <div class="table-container">


                    <table class="transactions-table">


                        <thead>

                            <tr>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($transactions as $transaction): ?>


                            <tr>


                                <!-- Type -->

                                <td>

                                    <?php if ($transaction["type"] === "income"): ?>

                                        <span class="transaction-income">
                                            Income
                                        </span>

                                    <?php else: ?>

                                        <span class="transaction-expense">
                                            Expense
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Category -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $transaction["category_name"]
                                    );

                                    ?>

                                </td>


                                <!-- Description -->

                                <td>

                                    <?php

                                    if (!empty($transaction["description"])) {

                                        echo htmlspecialchars(
                                            $transaction["description"]
                                        );

                                    } else {

                                        echo "—";

                                    }

                                    ?>

                                </td>


                                <!-- Date -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $transaction["transaction_date"]
                                    );

                                    ?>

                                </td>


                                <!-- Amount -->

                                <td>


                                    <?php if ($transaction["type"] === "income"): ?>


                                        <span class="amount-income">

                                            +

                                            <?php echo $currency_symbol; ?>

                                            <?php

                                            echo number_format(
                                                $transaction["amount"],
                                                2
                                            );

                                            ?>

                                        </span>


                                    <?php else: ?>


                                        <span class="amount-expense">

                                            -

                                            <?php echo $currency_symbol; ?>

                                            <?php

                                            echo number_format(
                                                $transaction["amount"],
                                                2
                                            );

                                            ?>

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- Actions -->

                                <td>


                                    <a
                                        href="edit.php?id=<?php echo $transaction["id"]; ?>"
                                    >
                                        Edit
                                    </a>


                                    &nbsp;


                                    <a
                                        href="delete.php?id=<?php echo $transaction["id"]; ?>"
                                        onclick="return confirm('Are you sure you want to delete this transaction?');"
                                    >
                                        Delete
                                    </a>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <!-- No Transactions -->

                <div class="empty-state">

                    <h3>
                        No transactions yet.
                    </h3>

                    <p>
                        Start by adding your first income or expense.
                    </p>

                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        Add Transaction
                    </a>

                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>