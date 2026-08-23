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
// GET USER INFORMATION + CURRENCY + THEME
// ==========================================

$stmt = $pdo->prepare(
    "SELECT name, email, currency, theme FROM users WHERE id = ?"
);

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}


// Get currency symbol
$user_currency = $user["currency"] ?? "USD";
$currency_symbol = getCurrencySymbol($user_currency);


// Get theme
$current_theme = $user["theme"] ?? "light";


// ==========================================
// FINANCIAL SUMMARY
// ==========================================

// Total Income
$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0)
     FROM transactions
     WHERE user_id = ? AND type = 'income'"
);

$stmt->execute([$user_id]);

$total_income = (float) $stmt->fetchColumn();


// Total Expenses
$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0)
     FROM transactions
     WHERE user_id = ? AND type = 'expense'"
);

$stmt->execute([$user_id]);

$total_expenses = (float) $stmt->fetchColumn();


// Total Balance
$total_balance = $total_income - $total_expenses;


// ==========================================
// RECENT TRANSACTIONS
// ==========================================

$stmt = $pdo->prepare(
    "SELECT 
        transactions.id,
        transactions.amount,
        transactions.type,
        transactions.description,
        transactions.transaction_date,
        categories.name AS category_name
     FROM transactions
     LEFT JOIN categories
        ON transactions.category_id = categories.id
     WHERE transactions.user_id = ?
     ORDER BY transactions.transaction_date DESC,
              transactions.id DESC
     LIMIT 5"
);

$stmt->execute([$user_id]);

$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Spendly</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">

<div class="dashboard-layout">


    <!-- ========================================== -->
    <!-- SIDEBAR -->
    <!-- ========================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">
            <h1>Spendly</h1>
        </div>

        <nav class="sidebar-nav">

            <a href="index.php" class="nav-item active">
                Dashboard
            </a>

            <a href="../transactions/index.php" class="nav-item">
                Transactions
            </a>

            <a href="../categories/index.php" class="nav-item">
                Categories
            </a>

            <a href="../reports/index.php" class="nav-item">
                Reports
            </a>

            <a href="../settings/index.php" class="nav-item">
                Settings
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="../auth/logout.php" class="nav-item">
                Logout
            </a>

        </div>

    </aside>


    <!-- ========================================== -->
    <!-- MAIN CONTENT -->
    <!-- ========================================== -->

    <main class="dashboard-main">


        <!-- Header -->

        <header class="dashboard-header">

            <div>

                <h2>
                    Welcome back,
                    <?php echo htmlspecialchars($user["name"]); ?>
                    👋
                </h2>

                <p>
                    Here's your financial overview.
                </p>

            </div>

        </header>


        <!-- ========================================== -->
        <!-- SUMMARY CARDS -->
        <!-- ========================================== -->

        <section class="summary-cards">


            <!-- Balance -->

            <div class="summary-card">

                <span>Total Balance</span>

                <h3>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($total_balance, 2); ?>
                </h3>

            </div>


            <!-- Income -->

            <div class="summary-card">

                <span>Total Income</span>

                <h3>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($total_income, 2); ?>
                </h3>

            </div>


            <!-- Expenses -->

            <div class="summary-card">

                <span>Total Expenses</span>

                <h3>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($total_expenses, 2); ?>
                </h3>

            </div>


        </section>


        <!-- ========================================== -->
        <!-- RECENT TRANSACTIONS -->
        <!-- ========================================== -->

        <section class="dashboard-section">


            <div class="section-header">

                <h3>
                    Recent Transactions
                </h3>

                <a href="../transactions/index.php">
                    View all
                </a>

            </div>


            <?php if (empty($recent_transactions)): ?>


                <!-- No transactions -->

                <div class="empty-state">

                    <p>
                        No transactions yet.
                    </p>

                    <a
                        href="../transactions/add.php"
                        class="btn btn-primary"
                    >
                        Add your first transaction
                    </a>

                </div>


            <?php else: ?>


                <!-- Transactions -->

                <div class="transactions-list">


                    <?php foreach ($recent_transactions as $transaction): ?>


                        <div class="transaction-row">


                            <!-- Transaction information -->

                            <div class="transaction-info">

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $transaction["description"]
                                        ?: $transaction["category_name"]
                                    );

                                    ?>

                                </strong>


                                <span>

                                    <?php

                                    echo htmlspecialchars(
                                        $transaction["category_name"]
                                        ?? "Uncategorized"
                                    );

                                    ?>

                                    ·

                                    <?php

                                    echo htmlspecialchars(
                                        $transaction["transaction_date"]
                                    );

                                    ?>

                                </span>

                            </div>


                            <!-- Transaction amount -->

                            <div
                                class="transaction-amount
                                <?php

                                echo $transaction["type"] === "income"
                                    ? "income"
                                    : "expense";

                                ?>"
                            >

                                <?php

                                echo $transaction["type"] === "income"
                                    ? "+"
                                    : "-";

                                ?>

                                <?php echo $currency_symbol; ?>

                                <?php

                                echo number_format(
                                    $transaction["amount"],
                                    2
                                );

                                ?>

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


    </main>


</div>

</body>

</html>