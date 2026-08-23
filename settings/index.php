<?php

require_once "../config.php";

session_start();

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


// ==========================================
// SAVE SETTINGS
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $currency = $_POST["currency"] ?? "";
    $theme = $_POST["theme"] ?? "";

    $allowed_currencies = [
        "USD",
        "EUR",
        "TRY",
        "DJF"
    ];

    $allowed_themes = [
        "light",
        "dark"
    ];


    // Validate currency
    if (!in_array($currency, $allowed_currencies, true)) {

        $message = "Invalid currency.";
        $message_type = "error";

    }

    // Validate theme
    elseif (!in_array($theme, $allowed_themes, true)) {

        $message = "Invalid theme.";
        $message_type = "error";

    }

    else {

        // Save currency and theme
        $stmt = $pdo->prepare("
            UPDATE users
            SET currency = ?, theme = ?
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $currency,
            $theme,
            $user_id
        ]);


        if ($success) {

            // Save theme in session too
            $_SESSION["theme"] = $theme;

            $message = "Settings updated successfully.";
            $message_type = "success";

        } else {

            $message = "Something went wrong. Please try again.";
            $message_type = "error";
        }
    }
}


// ==========================================
// GET CURRENT USER INFORMATION
// ==========================================

$stmt = $pdo->prepare("
    SELECT name, email, currency, theme
    FROM users
    WHERE id = ?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


// ==========================================
// DEFAULT VALUES
// ==========================================

$current_currency = $user["currency"] ?? "USD";

$current_theme = $user["theme"] ?? "light";


// Save theme in session
$_SESSION["theme"] = $current_theme;

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Settings - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="<?php echo $current_theme === "dark" ? "dark-mode" : ""; ?>">


<div class="app-layout">


    <!-- ========================================== -->
    <!-- SIDEBAR -->
    <!-- ========================================== -->

    <!-- Sidebar -->

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

        <a href="../categories/index.php" class="nav-item">
            Categories
        </a>

        <a href="../reports/index.php" class="nav-item">
            Reports
        </a>

        <a href="index.php" class="nav-item active">
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


        <!-- Message -->

        <?php if (!empty($message)): ?>

            <div class="form-message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- Page Header -->

        <div class="page-header">

            <div>

                <h1>
                    Settings
                </h1>

                <p>
                    Manage your account and preferences.
                </p>

            </div>

        </div>


        <!-- ========================================== -->
        <!-- PROFILE -->
        <!-- ========================================== -->

        <div class="settings-card">

            <h2>
                Profile
            </h2>


            <p class="settings-description">
                Your account information.
            </p>


            <div class="settings-info">


                <div>

                    <span>
                        Name
                    </span>

                    <strong>
                        <?php echo htmlspecialchars($user["name"]); ?>
                    </strong>

                </div>


                <div>

                    <span>
                        Email
                    </span>

                    <strong>
                        <?php echo htmlspecialchars($user["email"]); ?>
                    </strong>

                </div>


            </div>

        </div>


        <!-- ========================================== -->
        <!-- PREFERENCES -->
        <!-- ========================================== -->

        <div class="settings-card">

            <h2>
                Preferences
            </h2>


            <p class="settings-description">
                Customize how Spendly works for you.
            </p>


            <form
                method="POST"
            >


                <!-- Currency -->

                <div class="setting-row">

                    <div>

                        <strong>
                            Currency
                        </strong>

                        <p>
                            Choose your preferred currency.
                        </p>

                    </div>


                    <select
                        name="currency"
                        class="form-control"
                    >

                        <option
                            value="USD"
                            <?php echo $current_currency === "USD" ? "selected" : ""; ?>
                        >
                            USD ($)
                        </option>


                        <option
                            value="EUR"
                            <?php echo $current_currency === "EUR" ? "selected" : ""; ?>
                        >
                            EUR (€)
                        </option>


                        <option
                            value="TRY"
                            <?php echo $current_currency === "TRY" ? "selected" : ""; ?>
                        >
                            TRY (₺)
                        </option>


                        <option
                            value="DJF"
                            <?php echo $current_currency === "DJF" ? "selected" : ""; ?>
                        >
                            DJF (Fdj)
                        </option>

                    </select>

                </div>


                <!-- Theme -->

                <div class="setting-row">

                    <div>

                        <strong>
                            Theme
                        </strong>

                        <p>
                            Choose between light and dark mode.
                        </p>

                    </div>


                    <select
                        name="theme"
                        class="form-control"
                    >

                        <option
                            value="light"
                            <?php echo $current_theme === "light" ? "selected" : ""; ?>
                        >
                            Light
                        </option>


                        <option
                            value="dark"
                            <?php echo $current_theme === "dark" ? "selected" : ""; ?>
                        >
                            Dark
                        </option>

                    </select>

                </div>


                <!-- Save -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Settings
                    </button>

                </div>


            </form>

        </div>


        <!-- ========================================== -->
        <!-- SECURITY -->
        <!-- ========================================== -->

        <div class="settings-card">

            <h2>
                Security
            </h2>


            <p class="settings-description">
                Manage your account security.
            </p>


            <a href="change_password.php" class="btn btn-primary">
    Change Password
</a>

        </div>


        <!-- ========================================== -->
        <!-- ACCOUNT -->
        <!-- ========================================== -->

        <div class="settings-card">

            <h2>
                Account
            </h2>


            <p class="settings-description">
                Sign out of your Spendly account.
            </p>


            <a
                href="../auth/logout.php"
                class="btn btn-danger"
            >
                Logout
            </a>

        </div>


    </main>


</div>


</body>

</html>