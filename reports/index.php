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
// GET USER CURRENCY
// ==========================================

$stmt = $pdo->prepare("
    SELECT currency
    FROM users
    WHERE id = ?
");

$stmt->execute([$user_id]);

$user_currency = $stmt->fetchColumn();

if (empty($user_currency)) {
    $user_currency = "USD";
}

$currency_symbol = getCurrencySymbol($user_currency);


// ==========================================
// GET SELECTED PERIOD
// ==========================================

$period = $_GET["period"] ?? "all";

$allowed_periods = [
    "all",
    "this_month",
    "last_month",
    "this_year"
];

if (!in_array($period, $allowed_periods, true)) {
    $period = "all";
}


// ==========================================
// BUILD DATE FILTER
// ==========================================

$date_condition = "";

if ($period === "this_month") {

    $date_condition = "
        AND transaction_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        AND transaction_date < DATE_ADD(
            DATE_FORMAT(CURDATE(), '%Y-%m-01'),
            INTERVAL 1 MONTH
        )
    ";

} elseif ($period === "last_month") {

    $date_condition = "
        AND transaction_date >= DATE_FORMAT(
            DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
            '%Y-%m-01'
        )
        AND transaction_date < DATE_FORMAT(
            CURDATE(),
            '%Y-%m-01'
        )
    ";

} elseif ($period === "this_year") {

    $date_condition = "
        AND transaction_date >= DATE_FORMAT(CURDATE(), '%Y-01-01')
        AND transaction_date < DATE_ADD(
            DATE_FORMAT(CURDATE(), '%Y-01-01'),
            INTERVAL 1 YEAR
        )
    ";
}


// ==========================================
// GET TOTAL INCOME
// ==========================================

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE user_id = ?
      AND type = 'income'
      $date_condition
");

$stmt->execute([$user_id]);

$total_income = (float) $stmt->fetchColumn();


// ==========================================
// GET TOTAL EXPENSES
// ==========================================

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE user_id = ?
      AND type = 'expense'
      $date_condition
");

$stmt->execute([$user_id]);

$total_expenses = (float) $stmt->fetchColumn();


// ==========================================
// CALCULATE BALANCE
// ==========================================

$balance = $total_income - $total_expenses;


// ==========================================
// GET EXPENSES BY CATEGORY
// ==========================================

$stmt = $pdo->prepare("
    SELECT
        categories.name,
        SUM(transactions.amount) AS total
    FROM transactions

    INNER JOIN categories
        ON transactions.category_id = categories.id

    WHERE transactions.user_id = ?
      AND transactions.type = 'expense'
      $date_condition

    GROUP BY categories.id, categories.name

    ORDER BY total DESC
");

$stmt->execute([$user_id]);

$category_results = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// PREPARE CATEGORY DATA FOR CHART
// ==========================================

$category_names = [];
$category_amounts = [];

foreach ($category_results as $row) {

    $category_names[] = $row["name"];

    $category_amounts[] = (float) $row["total"];
}


// ==========================================
// PERIOD LABEL
// ==========================================

$period_labels = [

    "all" => "All Time",

    "this_month" => "This Month",

    "last_month" => "Last Month",

    "this_year" => "This Year"

];

$current_period_label = $period_labels[$period];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reports - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>


<body class="dark-mode">


<div class="dashboard-layout">


    <!-- ========================================== -->
    <!-- SIDEBAR -->
    <!-- ========================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">
            <h1>Spendly</h1>
        </div>


        <nav class="sidebar-nav">

            <a
                href="../dashboard/index.php"
                class="nav-item"
            >
                Dashboard
            </a>


            <a
                href="../transactions/index.php"
                class="nav-item"
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
                href="index.php"
                class="nav-item active"
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

    <main class="dashboard-main">


        <!-- ========================================== -->
        <!-- PAGE HEADER -->
        <!-- ========================================== -->

        <header class="dashboard-header">

            <div>

                <h2>
                    Reports
                </h2>

                <p>
                    Understand your financial activity.
                </p>

            </div>


            <!-- Period Filter -->

            <form
                method="GET"
                class="report-filter"
            >

                <label for="period">
                    Period
                </label>


                <select
                    id="period"
                    name="period"
                    onchange="this.form.submit()"
                >

                    <option
                        value="all"
                        <?php echo $period === "all" ? "selected" : ""; ?>
                    >
                        All Time
                    </option>


                    <option
                        value="this_month"
                        <?php echo $period === "this_month" ? "selected" : ""; ?>
                    >
                        This Month
                    </option>


                    <option
                        value="last_month"
                        <?php echo $period === "last_month" ? "selected" : ""; ?>
                    >
                        Last Month
                    </option>


                    <option
                        value="this_year"
                        <?php echo $period === "this_year" ? "selected" : ""; ?>
                    >
                        This Year
                    </option>

                </select>

            </form>

        </header>


        <!-- ========================================== -->
        <!-- SELECTED PERIOD -->
        <!-- ========================================== -->

        <div class="dashboard-section">

            <p>

                Showing:

                <strong>
                    <?php echo htmlspecialchars($current_period_label); ?>
                </strong>

            </p>

        </div>


        <!-- ========================================== -->
        <!-- SUMMARY CARDS -->
        <!-- ========================================== -->

        <section class="summary-cards">


            <!-- Balance -->

            <div class="summary-card">

                <span>
                    Balance
                </span>

                <h3>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($balance, 2); ?>
                </h3>

            </div>


            <!-- Income -->

            <div class="summary-card">

                <span>
                    Total Income
                </span>

                <h3>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($total_income, 2); ?>
                </h3>

            </div>


            <!-- Expenses -->

            <div class="summary-card">

                <span>
                    Total Expenses
                </span>

                <h3>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($total_expenses, 2); ?>
                </h3>

            </div>


        </section>


        <!-- ========================================== -->
        <!-- INCOME VS EXPENSES -->
        <!-- ========================================== -->

        <section class="dashboard-section">


            <div class="section-header">

                <div>

                    <h3>
                        Income vs Expenses
                    </h3>

                    <p>
                        Compare your total income and expenses.
                    </p>

                </div>

            </div>


            <div class="chart-container">

                <canvas id="incomeExpenseChart"></canvas>

            </div>

        </section>


        <!-- ========================================== -->
        <!-- EXPENSES BY CATEGORY -->
        <!-- ========================================== -->

        <section class="dashboard-section">


            <div class="section-header">

                <div>

                    <h3>
                        Expenses by Category
                    </h3>

                    <p>
                        See where your money is being spent.
                    </p>

                </div>

            </div>


            <div class="chart-container">

                <canvas id="categoryExpenseChart"></canvas>

            </div>

        </section>


        <!-- ========================================== -->
        <!-- FINANCIAL SUMMARY -->
        <!-- ========================================== -->

        <section class="dashboard-section">

            <div class="section-header">

                <div>

                    <h3>
                        Financial Summary
                    </h3>

                </div>

            </div>


            <p>

                Your total income is

                <strong>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($total_income, 2); ?>
                </strong>

                and your total expenses are

                <strong>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($total_expenses, 2); ?>
                </strong>.

            </p>


            <p>

                Your current balance is

                <strong>
                    <?php echo $currency_symbol; ?>
                    <?php echo number_format($balance, 2); ?>
                </strong>.

            </p>

        </section>


    </main>


</div>


<script>


// ==========================================
// PHP DATA
// ==========================================

const income =
    <?php echo json_encode((float) $total_income); ?>;

const expenses =
    <?php echo json_encode((float) $total_expenses); ?>;

const currencySymbol =
    <?php echo json_encode($currency_symbol); ?>;


// ==========================================
// INCOME VS EXPENSES CHART
// ==========================================

const ctx = document
    .getElementById("incomeExpenseChart")
    .getContext("2d");


new Chart(ctx, {

    type: "bar",

    data: {

        labels: [
            "Income",
            "Expenses"
        ],

        datasets: [{

            label: "Amount",

            data: [
                income,
                expenses
            ],

            backgroundColor: [
                "#22c55e",
                "#ef4444"
            ],

            borderRadius: 8

        }]

    },


    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: false
            }

        },

        scales: {

            x: {

                ticks: {
                    color: "#94a3b8"
                },

                grid: {
                    color: "rgba(148, 163, 184, 0.15)"
                }

            },

            y: {

                beginAtZero: true,

                ticks: {

                    color: "#94a3b8",

                    callback: function(value) {

                        return currencySymbol + value;

                    }

                },

                grid: {
                    color: "rgba(148, 163, 184, 0.15)"
                }

            }

        }

    }

});


// ==========================================
// CATEGORY DATA
// ==========================================

const categoryNames =
    <?php echo json_encode($category_names); ?>;

const categoryAmounts =
    <?php echo json_encode($category_amounts); ?>;


// ==========================================
// EXPENSES BY CATEGORY CHART
// ==========================================

const categoryCtx = document
    .getElementById("categoryExpenseChart")
    .getContext("2d");


new Chart(categoryCtx, {

    type: "doughnut",

    data: {

        labels: categoryNames,

        datasets: [{

            data: categoryAmounts,

            backgroundColor: [

                "#6366f1",
                "#22c55e",
                "#ef4444",
                "#f59e0b",
                "#3b82f6",
                "#8b5cf6",
                "#ec4899",
                "#14b8a6"

            ]

        }]

    },


    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {

                position: "right",

                labels: {
                    color: "#94a3b8"
                }

            }

        }

    }

});

</script>


</body>

</html>