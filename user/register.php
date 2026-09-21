<?php

session_start();

include "../database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $address = trim($_POST["address"]);

    // Check empty fields
    if (
        empty($full_name) ||
        empty($username) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    }

    // Check password
    elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    }

    // Check password length
    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    }

    else {

        // Check username
        $check_username = $conn->prepare(
            "SELECT id FROM customer_users WHERE username = ?"
        );

        $check_username->bind_param("s", $username);
        $check_username->execute();
        $username_result = $check_username->get_result();

        if ($username_result->num_rows > 0) {

            $message = "Username already exists.";
            $message_type = "error";

        } else {

            // Check email
            $check_email = $conn->prepare(
                "SELECT id FROM customer_users WHERE email = ?"
            );

            $check_email->bind_param("s", $email);
            $check_email->execute();
            $email_result = $check_email->get_result();

            if ($email_result->num_rows > 0) {

                $message = "Email address is already registered.";
                $message_type = "error";

            } else {

                // Hash password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert user
                $stmt = $conn->prepare(
                    "INSERT INTO customer_users
                    (full_name, username, email, phone, password, address)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssssss",
                    $full_name,
                    $username,
                    $email,
                    $phone,
                    $hashed_password,
                    $address
                );

                if ($stmt->execute()) {

                    $message = "Registration successful! You can now login.";
                    $message_type = "success";

                    // Clear fields
                    $full_name = "";
                    $username = "";
                    $email = "";
                    $phone = "";
                    $address = "";

                } else {

                    $message = "Registration failed. Please try again.";
                    $message_type = "error";
                }

                $stmt->close();
            }

            $check_email->close();
        }

        $check_username->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>User Registration - A.M Turai Travel & Tours</title>

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

            background:
                linear-gradient(
                    rgba(0, 77, 64, 0.88),
                    rgba(0, 105, 92, 0.88)
                );

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;
        }

        .register-container {

            width: 100%;

            max-width: 520px;

            background: #ffffff;

            border-radius: 18px;

            padding: 35px;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.25);
        }

        .logo {

            text-align: center;

            margin-bottom: 15px;
        }

        .logo i {

            font-size: 48px;

            color: #00695c;
        }

        h1 {

            text-align: center;

            color: #004d40;

            margin-bottom: 8px;

            font-size: 28px;
        }

        .subtitle {

            text-align: center;

            color: #777;

            margin-bottom: 25px;

            font-size: 14px;
        }

        .form-group {

            margin-bottom: 17px;
        }

        .form-group label {

            display: block;

            margin-bottom: 7px;

            color: #333;

            font-weight: bold;

            font-size: 14px;
        }

        .input-box {

            position: relative;
        }

        .input-box i {

            position: absolute;

            left: 14px;

            top: 50%;

            transform: translateY(-50%);

            color: #00695c;
        }

        .input-box input,
        .input-box textarea {

            width: 100%;

            padding: 13px 14px 13px 42px;

            border: 1px solid #ddd;

            border-radius: 9px;

            outline: none;

            font-size: 15px;
        }

        .input-box textarea {

            min-height: 80px;

            resize: vertical;
        }

        .input-box input:focus,
        .input-box textarea:focus {

            border-color: #00695c;

            box-shadow:
                0 0 0 3px rgba(0, 105, 92, 0.1);
        }

        .btn {

            width: 100%;

            border: none;

            padding: 14px;

            border-radius: 9px;

            background: #00695c;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;
        }

        .btn:hover {

            background: #004d40;

            transform: translateY(-1px);
        }

        .message {

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 18px;

            text-align: center;

            font-size: 14px;
        }

        .message.success {

            background: #dff5e8;

            color: #176b3a;
        }

        .message.error {

            background: #fde2e2;

            color: #a52828;
        }

        .links {

            text-align: center;

            margin-top: 20px;

            font-size: 14px;
        }

        .links a {

            color: #00695c;

            text-decoration: none;

            font-weight: bold;
        }

        .links a:hover {

            text-decoration: underline;
        }

        .back-home {

            text-align: center;

            margin-top: 12px;
        }

        .back-home a {

            color: #777;

            text-decoration: none;

            font-size: 13px;
        }

        @media (max-width: 500px) {

            .register-container {

                padding: 25px 20px;
            }

            h1 {

                font-size: 23px;
            }
        }

    </style>

</head>

<body>

<div class="register-container">

    <div class="logo">
        <i class="fas fa-bus"></i>
    </div>

    <h1>Create Account</h1>

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

            <label>Full Name</label>

            <div class="input-box">

                <i class="fas fa-user"></i>

                <input
                    type="text"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                    required
                >

            </div>

        </div>


        <div class="form-group">

            <label>Username</label>

            <div class="input-box">

                <i class="fas fa-user-tag"></i>

                <input
                    type="text"
                    name="username"
                    placeholder="Choose a username"
                    value="<?php echo htmlspecialchars($username ?? ''); ?>"
                    required
                >

            </div>

        </div>


        <div class="form-group">

            <label>Email</label>

            <div class="input-box">

                <i class="fas fa-envelope"></i>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    required
                >

            </div>

        </div>


        <div class="form-group">

            <label>Phone Number</label>

            <div class="input-box">

                <i class="fas fa-phone"></i>

                <input
                    type="text"
                    name="phone"
                    placeholder="Enter your phone number"
                    value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                    required
                >

            </div>

        </div>


        <div class="form-group">

            <label>Password</label>

            <div class="input-box">

                <i class="fas fa-lock"></i>

                <input
                    type="password"
                    name="password"
                    placeholder="Create a password"
                    required
                >

            </div>

        </div>


        <div class="form-group">

            <label>Confirm Password</label>

            <div class="input-box">

                <i class="fas fa-lock"></i>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >

            </div>

        </div>


        <div class="form-group">

            <label>Address</label>

            <div class="input-box">

                <i class="fas fa-location-dot"></i>

                <textarea
                    name="address"
                    placeholder="Enter your address"
                ><?php echo htmlspecialchars($address ?? ''); ?></textarea>

            </div>

        </div>


        <button type="submit" class="btn">

            <i class="fas fa-user-plus"></i>
            Create Account

        </button>

    </form>


    <div class="links">

        Already have an account?

        <a href="login.php">
            Login here
        </a>

    </div>


    <div class="back-home">

        <a href="../index.php">

            <i class="fas fa-arrow-left"></i>
            Back to Home

        </a>

    </div>

</div>

</body>

</html>