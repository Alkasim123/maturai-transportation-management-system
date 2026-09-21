<?php

session_start();

include "../database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {

        $message = "Please enter your username and password.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, full_name, username, email, password
             FROM customer_users
             WHERE username = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                // Create user session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["full_name"];
                $_SESSION["user_username"] = $user["username"];
                $_SESSION["user_email"] = $user["email"];

                // Redirect to user dashboard
                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Incorrect username or password.";
                $message_type = "error";
            }

        } else {

            $message = "Incorrect username or password.";
            $message_type = "error";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>User Login - A.M Turai Travel & Tours</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            font-family: Arial, sans-serif;

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;

            background:
                linear-gradient(
                    rgba(0, 77, 64, 0.90),
                    rgba(0, 105, 92, 0.90)
                );
        }

        .login-container {

            width: 100%;

            max-width: 430px;

            background: #ffffff;

            padding: 40px 35px;

            border-radius: 18px;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.25);
        }

        .logo {

            text-align: center;

            margin-bottom: 15px;
        }

        .logo i {

            font-size: 55px;

            color: #00695c;
        }

        h1 {

            text-align: center;

            color: #004d40;

            font-size: 28px;

            margin-bottom: 8px;
        }

        .subtitle {

            text-align: center;

            color: #777;

            font-size: 14px;

            margin-bottom: 28px;
        }

        .form-group {

            margin-bottom: 20px;
        }

        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-weight: bold;

            color: #333;

            font-size: 14px;
        }

        .input-box {

            position: relative;
        }

        .input-box i {

            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            color: #00695c;
        }

        .input-box input {

            width: 100%;

            padding: 14px 15px 14px 43px;

            border: 1px solid #ddd;

            border-radius: 9px;

            outline: none;

            font-size: 15px;

            transition: 0.3s;
        }

        .input-box input:focus {

            border-color: #00695c;

            box-shadow:
                0 0 0 3px rgba(0, 105, 92, 0.10);
        }

        .password-wrapper {

            position: relative;
        }

        .password-wrapper input {

            padding-right: 45px;
        }

        .toggle-password {

            position: absolute;

            right: 14px;

            top: 50%;

            transform: translateY(-50%);

            color: #777;

            cursor: pointer;

        }

        .btn-login {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 9px;

            background: #00695c;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;

            margin-top: 5px;
        }

        .btn-login:hover {

            background: #004d40;

            transform: translateY(-1px);
        }

        .message {

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 20px;

            text-align: center;

            font-size: 14px;
        }

        .message.error {

            background: #fde2e2;

            color: #a52828;

        }

        .register-link {

            text-align: center;

            margin-top: 22px;

            font-size: 14px;

            color: #555;
        }

        .register-link a {

            color: #00695c;

            text-decoration: none;

            font-weight: bold;
        }

        .register-link a:hover {

            text-decoration: underline;
        }

        .home-link {

            text-align: center;

            margin-top: 15px;
        }

        .home-link a {

            color: #777;

            text-decoration: none;

            font-size: 13px;
        }

        .home-link a:hover {

            color: #00695c;
        }

        .admin-link {

            text-align: center;

            margin-top: 20px;

            padding-top: 18px;

            border-top: 1px solid #eee;

            font-size: 13px;
        }

        .admin-link a {

            color: #444;

            text-decoration: none;

        }

        .admin-link a:hover {

            color: #00695c;

        }

        @media (max-width: 480px) {

            .login-container {

                padding: 30px 22px;

            }

            h1 {

                font-size: 24px;

            }

        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="logo">

        <i class="fas fa-bus"></i>

    </div>

    <h1>User Login</h1>

    <p class="subtitle">

        A.M Turai Travel & Tours

    </p>


    <?php if (!empty($message)): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="form-group">

            <label for="username">
                Username
            </label>

            <div class="input-box">

                <i class="fas fa-user"></i>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($username ?? ''); ?>"
                    required
                >

            </div>

        </div>


        <div class="form-group">

            <label for="password">
                Password
            </label>

            <div class="password-wrapper">

                <div class="input-box">

                    <i class="fas fa-lock"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

                <i
                    class="fas fa-eye toggle-password"
                    id="togglePassword"
                ></i>

            </div>

        </div>


        <button type="submit" class="btn-login">

            <i class="fas fa-right-to-bracket"></i>

            Login

        </button>

    </form>


    <div class="register-link">

        Don't have an account?

        <a href="register.php">
            Create Account
        </a>

    </div>


    <div class="home-link">

        <a href="../index.php">

            <i class="fas fa-arrow-left"></i>

            Back to Home

        </a>

    </div>


    <div class="admin-link">

        <a href="../admin/login.php">

            <i class="fas fa-user-shield"></i>

            Admin Login

        </a>

    </div>

</div>


<script>

    const togglePassword =
        document.getElementById("togglePassword");

    const password =
        document.getElementById("password");


    togglePassword.addEventListener("click", function () {

        if (password.type === "password") {

            password.type = "text";

            this.classList.remove("fa-eye");

            this.classList.add("fa-eye-slash");

        } else {

            password.type = "password";

            this.classList.remove("fa-eye-slash");

            this.classList.add("fa-eye");

        }

    });

</script>

</body>

</html>