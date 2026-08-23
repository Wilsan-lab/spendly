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
// GET CATEGORY ID
// ==========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];


// ==========================================
// GET EXISTING CATEGORY
// ==========================================

$stmt = $pdo->prepare("
    SELECT id, name, type
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
// UPDATE CATEGORY
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

        // Check if another category already has the same name and type
        $check = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE name = ?
            AND type = ?
            AND id != ?
        ");

        $check->execute([
            $name,
            $type,
            $id
        ]);

        if ($check->fetch()) {

            $message = "This category already exists.";
            $message_type = "error";

        } else {

            // Update category
            $stmt = $pdo->prepare("
                UPDATE categories
                SET name = ?, type = ?
                WHERE id = ?
            ");

            if ($stmt->execute([
                $name,
                $type,
                $id
            ])) {

                header("Location: index.php");
                exit;

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }
        }
    }


    // Keep entered values if validation fails
    $category["name"] = $name;
    $category["type"] = $type;
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

    <title>Edit Category - Spendly</title>

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

                <h1>Edit Category</h1>

                <p>
                    Update your category information.
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
                        value="<?php echo htmlspecialchars($category["name"]); ?>"
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

                        <option
                            value="income"
                            <?php
                            echo $category["type"] === "income"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Income
                        </option>

                        <option
                            value="expense"
                            <?php
                            echo $category["type"] === "expense"
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
                        Save Changes
                    </button>

                </div>


            </form>


        </div>


    </main>

</div>

</body>

</html>