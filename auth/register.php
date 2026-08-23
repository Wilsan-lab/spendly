<?php

require_once "../config.php";

$message = "";
$message_type = "";

// Initialize form values
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // ==========================================
    // VALIDATION
    // ==========================================

    // Check that all fields are filled
    if (
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message = "Please fill in all fields.";
        $message_type = "error";


    // Check email format
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";


    // Check password length
    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";


    // Check passwords
    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";


    } else {

        // ==========================================
        // CHECK EXISTING USER
        // ==========================================

        $check = $pdo->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->execute([$email]);

        $existing_user = $check->fetch(PDO::FETCH_ASSOC);


        if ($existing_user) {

            $message = "An account with this email already exists.";
            $message_type = "error";


        } else {

            // ==========================================
            // HASH PASSWORD
            // ==========================================

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            // ==========================================
            // CREATE USER
            // ==========================================

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hashed_password
            ]);


            // Registration successful
            header("Location: login.php");
            exit;
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

    <title>Create Account - Spendly</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <main class="auth-page">

        <div class="auth-container">


            <!-- ========================================== -->
            <!-- LOGO -->
            <!-- ========================================== -->

            <div class="auth-logo">

                <h1>
                    Spendly
                </h1>

            </div>


            <!-- ========================================== -->
            <!-- REGISTRATION CARD -->
            <!-- ========================================== -->

            <div class="auth-card">

                <h2>
                    Create your account
                </h2>

                <p class="auth-subtitle">
                    Start managing your money with Spendly.
                </p>


                <!-- Error Message -->

                <?php if (!empty($message)): ?>

                    <div class="form-message <?php echo $message_type; ?>">

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </div>

                <?php endif; ?>


                <!-- ========================================== -->
                <!-- FORM -->
                <!-- ========================================== -->

                <form
                    action=""
                    method="POST"
                >


                    <!-- Full Name -->

                    <div class="form-group">

                        <label for="name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            required
                        >

                    </div>


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
                            value="<?php echo htmlspecialchars($email); ?>"
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
                            placeholder="Create a password"
                            required
                        >

                    </div>


                    <!-- Confirm Password -->

                    <div class="form-group">

                        <label for="confirm_password">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-control"
                            placeholder="Confirm your password"
                            required
                        >

                    </div>


                    <!-- Create Account -->

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Create Account
                    </button>

                </form>


                

                <!-- ========================================== -->
                <!-- LOGIN LINK -->
                <!-- ========================================== -->

                <div class="auth-footer">

                    Already have an account?

                    <a href="login.php">
                        Login
                    </a>

                </div>

            </div>

        </div>

    </main>

</body>

</html>