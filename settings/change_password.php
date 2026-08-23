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
// CHANGE PASSWORD
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password = $_POST["current_password"] ?? "";
    $new_password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // Check all fields
    if (
        empty($current_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    }


    // Check new password length
    elseif (strlen($new_password) < 6) {

        $message = "New password must be at least 6 characters.";
        $message_type = "error";

    }


    // Check passwords match
    elseif ($new_password !== $confirm_password) {

        $message = "New passwords do not match.";
        $message_type = "error";

    }


    else {

        // Get current password from database
        $stmt = $pdo->prepare("
            SELECT password
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$user_id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$user) {

            $message = "User account not found.";
            $message_type = "error";

        }


        // Verify current password
        elseif (!password_verify($current_password, $user["password"])) {

            $message = "Current password is incorrect.";
            $message_type = "error";

        }


        else {

            // Hash new password
            $new_password_hash = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


            // Update password
            $stmt = $pdo->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
            ");

            $success = $stmt->execute([
                $new_password_hash,
                $user_id
            ]);


            if ($success) {

                $message = "Password changed successfully.";
                $message_type = "success";

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

    <title>Change Password - Spendly</title>

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


            <a href="../categories/index.php">
                Categories
            </a>


            <a href="../reports/index.php">
                Reports
            </a>


            <a href="index.php" class="active">
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
                    Change Password
                </h1>

                <p>
                    Update your Spendly account password.
                </p>

            </div>

        </div>


        <!-- ========================================== -->
        <!-- PASSWORD FORM -->
        <!-- ========================================== -->

        <div class="card transaction-form-card">


            <!-- Message -->

            <?php if (!empty($message)): ?>

                <div class="form-message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- Current Password -->

                <div class="form-group">

                    <label for="current_password">
                        Current Password
                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="form-control"
                        placeholder="Enter your current password"
                        required
                    >

                </div>


                <!-- New Password -->

                <div class="form-group">

                    <label for="new_password">
                        New Password
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        class="form-control"
                        placeholder="Enter your new password"
                        minlength="6"
                        required
                    >

                </div>


                <!-- Confirm Password -->

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Confirm your new password"
                        minlength="6"
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
                        Change Password
                    </button>

                </div>


            </form>


        </div>


    </main>


</div>


</body>

</html>