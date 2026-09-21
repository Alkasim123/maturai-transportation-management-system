<?php

session_start();

include "../database.php";

$error = "";

if (isset($_POST["login"])) {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, username, password 
         FROM users 
         WHERE username = ? 
         LIMIT 1"
    );

    $stmt->bind_param("s", $username);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        if ($password === $admin["password"]) {

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Incorrect password.";

        }

    } else {

        $error = "Admin account not found.";

    }

    $stmt->close();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | A.M Turai</title>

    <link rel="stylesheet" href="css/admin-style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;

            background:
                linear-gradient(
                    135deg,
                    #004d40,
                    #00897b
                );
        }

        .admin-login {
            width: 420px;
            max-width: 92%;

            background: white;

            padding: 40px;

            border-radius: 20px;

            box-shadow:
                0 15px 40px rgba(0,0,0,0.25);

            text-align: center;
        }

        .admin-icon {
            width: 80px;
            height: 80px;

            margin: 0 auto 20px;

            border-radius: 50%;

            background: #00695c;

            color: white;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 35px;
        }

        .admin-login h2 {
            color: #00695c;
            margin-bottom: 8px;
        }

        .admin-login .subtitle {
            color: #777;
            margin-bottom: 25px;
        }

        .input-group {
            position: relative;
            margin: 15px 0;
        }

        .input-group i {
            position: absolute;

            left: 15px;
            top: 50%;

            transform: translateY(-50%);

            color: #00695c;
        }

        .input-group input {
            width: 100%;

            padding: 14px 14px 14px 45px;

            border: 1px solid #ddd;

            border-radius: 10px;

            outline: none;
        }

        .input-group input:focus {
            border-color: #00897b;
        }

        .admin-login button {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 10px;

            background: #00695c;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        .admin-login button:hover {
            background: #004d40;
        }

        .error {
            background: #ffebee;

            color: #c62828;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 15px;
        }

        .back-home {
            display: block;

            margin-top: 20px;

            color: #00695c;

            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="admin-login">

    <div class="admin-icon">
        <i class="fa-solid fa-user-shield"></i>
    </div>

    <h2>Admin Login</h2>

    <p class="subtitle">
        A.M Turai Travel & Tours
    </p>

    <?php if ($error != ""): ?>

        <div class="error">
            <i class="fa-solid fa-circle-exclamation"></i>

            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="input-group">

            <i class="fa-solid fa-user"></i>

            <input
                type="text"
                name="username"
                placeholder="Admin Username"
                required
            >

        </div>


        <div class="input-group">

            <i class="fa-solid fa-lock"></i>

            <input
                type="password"
                name="password"
                placeholder="Admin Password"
                required
            >

        </div>


        <button type="submit" name="login">

            <i class="fa-solid fa-right-to-bracket"></i>

            Login to Admin Panel

        </button>

    </form>


    <a href="../index.php" class="back-home">

        <i class="fa-solid fa-arrow-left"></i>

        Back to Website

    </a>

</div>

</body>

</html>