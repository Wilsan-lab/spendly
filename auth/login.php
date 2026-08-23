<?php

require_once "../config.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Check that fields are filled
    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";
        $message_type = "error";

    } else {

        // Find user by email
        $stmt = $pdo->prepare(
            "SELECT id, name, email, password FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check user and password
        if ($user && password_verify($password, $user["password"])) {

            // Start session
            session_start();

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];

            // Go to dashboard
            header("Location: ../dashboard/index.php");
            exit;

        } else {

            $message = "Invalid email or password.";
            $message_type = "error";
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

    <title>Login - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <main class="auth-page">

        <div class="auth-container">

            <!-- Spendly Logo -->
            <div class="auth-logo">

                <h1>Spendly</h1>

            </div>

            <!-- Login Card -->
            <div class="auth-card">

                <h2>Welcome back</h2>

                <p class="auth-subtitle">
                    Log in to manage your money.
                </p>

                <?php if (!empty($message)): ?>

                    <div class="form-message <?php echo $message_type; ?>">

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </div>

                <?php endif; ?>

                <form
                    action=""
                    method="POST"
                >

                    <!-- Email -->
                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            required
                        >

                    </div>

                    <!-- Password -->
                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            required
                        >

                    </div>

                    <!-- Forgot Password -->
                    <div class="forgot-password">

                        <a href="#">
                            Forgot your password?
                        </a>

                    </div>

                    <!-- Login Button -->
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Login
                    </button>

                </form>


                <!-- Register Link -->
                <div class="auth-footer">

                    Don't have an account?

                    <a href="register.php">
                        Create an account
                    </a>

                </div>

            </div>

        </div>

    </main>

</body>

</html>