<?php

session_start();

require_once "../config.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


// ==========================================
// GET USER THEME
// ==========================================

$stmt = $pdo->prepare(
    "SELECT theme FROM users WHERE id = ?"
);

$stmt->execute([$user_id]);

$user_theme = $stmt->fetchColumn();

// Default theme
if (empty($user_theme)) {
    $user_theme = "light";
}

// Only allow light or dark
if (!in_array($user_theme, ["light", "dark"], true)) {
    $user_theme = "light";
}


// ==========================================
// GET ALL CATEGORIES
// ==========================================

$stmt = $pdo->prepare("
    SELECT id, name, type
    FROM categories
    ORDER BY type ASC, name ASC
");

$stmt->execute();

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Categories - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="<?php echo $user_theme === 'dark' ? 'dark-mode' : ''; ?>">


<div class="app-layout">


    <!-- ========================================== -->
    <!-- SIDEBAR -->
    <!-- ========================================== -->

    <aside class="sidebar">

    <div class="sidebar-logo">
        <h1>Spendly</h1>
    </div>

    <nav class="sidebar-nav">

        <a href="../dashboard/index.php" class="nav-item">
            Dashboard
        </a>

        <a href="../transactions/index.php" class="nav-item">
            Transactions
        </a>

        <a href="index.php" class="nav-item active">
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

    <main class="main-content">


        <!-- Page Header -->

        <div class="page-header">


            <div>

                <h1>
                    Categories
                </h1>

                <p>
                    Organize your income and expenses.
                </p>

            </div>


            <a
                href="add.php"
                class="btn btn-primary"
            >
                + Add Category
            </a>


        </div>


        <!-- ========================================== -->
        <!-- CATEGORIES CARD -->
        <!-- ========================================== -->

        <div class="card">


            <?php if (count($categories) > 0): ?>


                <div class="table-container">


                    <table class="transactions-table">


                        <thead>

                            <tr>

                                <th>
                                    Category Name
                                </th>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($categories as $category): ?>


                            <tr>


                                <!-- Category Name -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $category["name"]
                                    );

                                    ?>

                                </td>


                                <!-- Type -->

                                <td>


                                    <?php if ($category["type"] === "income"): ?>


                                        <span class="transaction-income">
                                            Income
                                        </span>


                                    <?php else: ?>


                                        <span class="transaction-expense">
                                            Expense
                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- Actions -->

                                <td>


                                    <a
                                        href="edit.php?id=<?php echo $category["id"]; ?>"
                                    >
                                        Edit
                                    </a>


                                    &nbsp;


                                    <a
                                        href="delete.php?id=<?php echo $category["id"]; ?>"
                                        onclick="return confirm('Are you sure you want to delete this category?');"
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


                <!-- ========================================== -->
                <!-- NO CATEGORIES -->
                <!-- ========================================== -->

                <div class="empty-state">


                    <h3>
                        No categories yet.
                    </h3>


                    <p>
                        Create your first category to organize your transactions.
                    </p>


                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        Add Category
                    </a>


                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>