<?php

session_start();

require_once "../config.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$message_type = "";


// ==========================================
// ADD CATEGORY
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $type = $_POST["type"] ?? "";


    // Validate fields
    if (empty($name) || empty($type)) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!in_array($type, ["income", "expense"], true)) {

        $message = "Invalid category type.";
        $message_type = "error";

    } else {

        // Check if category already exists
        $check = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE name = ? AND type = ?
        ");

        $check->execute([$name, $type]);

        if ($check->fetch()) {

            $message = "This category already exists.";
            $message_type = "error";

        } else {

            // Insert category
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, type)
                VALUES (?, ?)
            ");

            if ($stmt->execute([$name, $type])) {

                header("Location: index.php");
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

    <title>Add Category - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="dark-mode">

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

            <a href="../transactions/index.php">
                Transactions
            </a>

            <a href="index.php" class="active">
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

                <h1>Add Category</h1>

                <p>
                    Create a category for your income or expenses.
                </p>

            </div>

        </div>


        <!-- ========================================== -->
        <!-- FORM CARD -->
        <!-- ========================================== -->

        <div class="card transaction-form-card">


            <!-- Message -->

            <?php if (!empty($message)): ?>

                <div class="form-message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <!-- Form -->

            <form method="POST">


                <!-- Category Name -->

                <div class="form-group">

                    <label for="name">
                        Category Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        placeholder="e.g. Rent"
                        value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                        required
                    >

                </div>


                <!-- Category Type -->

                <div class="form-group">

                    <label for="type">
                        Category Type
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
                        Add Category
                    </button>

                </div>


            </form>


        </div>


    </main>

</div>

</body>

</html>