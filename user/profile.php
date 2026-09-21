<?php

session_start();

include "../database.php";

// Protect page
if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


// ========================================
// GET CURRENT USER INFORMATION
// ========================================

$stmt = $conn->prepare(
    "SELECT
        id,
        full_name,
        username,
        email,
        phone,
        address
     FROM customer_users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows != 1) {

    session_destroy();

    header("Location: login.php");
    exit();

}

$user = $result->fetch_assoc();

$stmt->close();


// ========================================
// UPDATE PROFILE
// ========================================

if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    &&
    isset($_POST["update_profile"])
) {

    $full_name = trim($_POST["full_name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);


    if (
        empty($full_name) ||
        empty($username) ||
        empty($email) ||
        empty($phone)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        // Check username
        $check_username = $conn->prepare(
            "SELECT id
             FROM customer_users
             WHERE username = ?
             AND id != ?"
        );

        $check_username->bind_param(
            "si",
            $username,
            $user_id
        );

        $check_username->execute();

        $username_result =
            $check_username->get_result();


        // Check email
        $check_email = $conn->prepare(
            "SELECT id
             FROM customer_users
             WHERE email = ?
             AND id != ?"
        );

        $check_email->bind_param(
            "si",
            $email,
            $user_id
        );

        $check_email->execute();

        $email_result =
            $check_email->get_result();


        if ($username_result->num_rows > 0) {

            $message = "Username is already being used.";
            $message_type = "error";

        } elseif ($email_result->num_rows > 0) {

            $message = "Email is already being used.";
            $message_type = "error";

        } else {

            $update = $conn->prepare(
                "UPDATE customer_users
                 SET
                    full_name = ?,
                    username = ?,
                    email = ?,
                    phone = ?,
                    address = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "sssssi",
                $full_name,
                $username,
                $email,
                $phone,
                $address,
                $user_id
            );


            if ($update->execute()) {

                $message =
                    "Profile updated successfully.";

                $message_type = "success";


                // Update session information
                $_SESSION["user_name"] = $full_name;
                $_SESSION["user_username"] = $username;
                $_SESSION["user_email"] = $email;


                // Update displayed information
                $user["full_name"] = $full_name;
                $user["username"] = $username;
                $user["email"] = $email;
                $user["phone"] = $phone;
                $user["address"] = $address;

            } else {

                $message =
                    "Unable to update profile.";

                $message_type = "error";
            }

            $update->close();
        }

        $check_username->close();
        $check_email->close();
    }
}


// ========================================
// CHANGE PASSWORD
// ========================================

if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    &&
    isset($_POST["change_password"])
) {

    $current_password =
        $_POST["current_password"];

    $new_password =
        $_POST["new_password"];

    $confirm_password =
        $_POST["confirm_password"];


    if (
        empty($current_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $message =
            "Please fill in all password fields.";

        $message_type = "error";

    } elseif ($new_password !== $confirm_password) {

        $message =
            "New passwords do not match.";

        $message_type = "error";

    } elseif (strlen($new_password) < 6) {

        $message =
            "New password must be at least 6 characters.";

        $message_type = "error";

    } else {

        // Get current password
        $password_stmt = $conn->prepare(
            "SELECT password
             FROM customer_users
             WHERE id = ?
             LIMIT 1"
        );

        $password_stmt->bind_param(
            "i",
            $user_id
        );

        $password_stmt->execute();

        $password_result =
            $password_stmt->get_result();

        $password_data =
            $password_result->fetch_assoc();


        if (
            password_verify(
                $current_password,
                $password_data["password"]
            )
        ) {

            $new_hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            $update_password = $conn->prepare(
                "UPDATE customer_users
                 SET password = ?
                 WHERE id = ?"
            );

            $update_password->bind_param(
                "si",
                $new_hashed_password,
                $user_id
            );


            if ($update_password->execute()) {

                $message =
                    "Password changed successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Unable to change password.";

                $message_type = "error";
            }

            $update_password->close();

        } else {

            $message =
                "Current password is incorrect.";

            $message_type = "error";
        }

        $password_stmt->close();
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

    <title>
        My Profile - A.M Turai Travel & Tours
    </title>


    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >


    <style>

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;
        }


        body {

            font-family: Arial, sans-serif;

            background: #f4f7f6;

            color: #333;
        }


        /* SIDEBAR */

        .sidebar {

            position: fixed;

            left: 0;

            top: 0;

            width: 250px;

            height: 100vh;

            background: #004d40;

            color: white;

            padding: 25px 15px;

            z-index: 1000;
        }


        .logo {

            text-align: center;

            padding-bottom: 25px;

            border-bottom:
                1px solid rgba(255,255,255,0.15);
        }


        .logo i {

            font-size: 40px;

            margin-bottom: 10px;
        }


        .logo h2 {

            font-size: 19px;
        }


        .logo p {

            font-size: 11px;

            opacity: 0.8;

            margin-top: 5px;
        }


        .menu {

            margin-top: 25px;
        }


        .menu a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 13px 15px;

            margin-bottom: 7px;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            transition: 0.3s;

            font-size: 14px;
        }


        .menu a:hover,
        .menu a.active {

            background: #00695c;
        }


        .menu a i {

            width: 20px;

            text-align: center;
        }


        .logout {

            position: absolute;

            bottom: 25px;

            left: 15px;

            right: 15px;
        }


        .logout a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 13px 15px;

            background: #c62828;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            font-size: 14px;
        }


        /* MAIN */

        .main {

            margin-left: 250px;

            min-height: 100vh;
        }


        /* TOPBAR */

        .topbar {

            height: 75px;

            background: white;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 30px;

            box-shadow:
                0 2px 10px rgba(0,0,0,0.06);
        }


        .topbar h1 {

            font-size: 22px;

            color: #004d40;
        }


        .user-info {

            display: flex;

            align-items: center;

            gap: 12px;
        }


        .user-icon {

            width: 42px;

            height: 42px;

            border-radius: 50%;

            background: #00695c;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .user-info span {

            font-weight: bold;

            color: #444;
        }


        /* CONTENT */

        .content {

            padding: 30px;
        }


        .page-intro {

            margin-bottom: 25px;
        }


        .page-intro h2 {

            color: #004d40;

            font-size: 24px;

            margin-bottom: 7px;
        }


        .page-intro p {

            color: #777;

            font-size: 14px;
        }


        /* MESSAGE */

        .message {

            padding: 14px;

            border-radius: 9px;

            margin-bottom: 20px;

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


        /* GRID */

        .profile-grid {

            display: grid;

            grid-template-columns:
                1.5fr 1fr;

            gap: 25px;
        }


        .card {

            background: white;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);
        }


        .card-header {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 22px;

            padding-bottom: 15px;

            border-bottom: 1px solid #eee;
        }


        .card-header i {

            color: #00695c;

            font-size: 21px;
        }


        .card-header h3 {

            color: #004d40;

            font-size: 18px;
        }


        /* FORM */

        .form-group {

            margin-bottom: 17px;
        }


        .form-group label {

            display: block;

            font-size: 13px;

            font-weight: bold;

            color: #444;

            margin-bottom: 7px;
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


        .input-box textarea + i {

            top: 20px;
        }


        input,
        textarea {

            width: 100%;

            padding: 13px 14px 13px 42px;

            border: 1px solid #ddd;

            border-radius: 8px;

            outline: none;

            font-size: 14px;

            font-family: Arial, sans-serif;
        }


        textarea {

            min-height: 90px;

            resize: vertical;
        }


        input:focus,
        textarea:focus {

            border-color: #00695c;

            box-shadow:
                0 0 0 3px rgba(0,105,92,0.08);
        }


        .btn {

            border: none;

            padding: 12px 18px;

            border-radius: 8px;

            background: #00695c;

            color: white;

            font-weight: bold;

            cursor: pointer;

            font-size: 14px;
        }


        .btn:hover {

            background: #004d40;
        }


        /* PROFILE INFO */

        .profile-avatar {

            width: 90px;

            height: 90px;

            margin: 5px auto 18px;

            border-radius: 50%;

            background: #00695c;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 36px;
        }


        .profile-name {

            text-align: center;

            color: #004d40;

            font-size: 20px;

            font-weight: bold;

            margin-bottom: 5px;
        }


        .profile-username {

            text-align: center;

            color: #888;

            font-size: 13px;

            margin-bottom: 25px;
        }


        .info-item {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 14px 0;

            border-bottom: 1px solid #eee;
        }


        .info-item:last-child {

            border-bottom: none;
        }


        .info-icon {

            width: 36px;

            height: 36px;

            border-radius: 8px;

            background: #e0f2f1;

            color: #00695c;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .info-item small {

            display: block;

            color: #999;

            font-size: 11px;

            margin-bottom: 3px;
        }


        .info-item strong {

            font-size: 13px;

            color: #444;

            word-break: break-word;
        }


        /* PASSWORD */

        .password-note {

            background: #f4f7f6;

            padding: 12px;

            border-radius: 8px;

            font-size: 12px;

            color: #777;

            margin-bottom: 18px;
        }


        /* MOBILE */

        @media (max-width: 900px) {

            .profile-grid {

                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 750px) {

            .sidebar {

                width: 70px;

                padding: 15px 8px;
            }


            .logo h2,
            .logo p,
            .menu span,
            .logout span {

                display: none;
            }


            .logo {

                border: none;
            }


            .logo i {

                font-size: 30px;
            }


            .menu a {

                justify-content: center;

                padding: 14px 5px;
            }


            .logout {

                left: 8px;

                right: 8px;
            }


            .logout a {

                justify-content: center;

                padding: 14px 5px;
            }


            .main {

                margin-left: 70px;
            }


            .topbar {

                padding: 0 18px;
            }


            .topbar h1 {

                font-size: 18px;
            }


            .user-info span {

                display: none;
            }


            .content {

                padding: 18px;
            }
        }

    </style>

</head>


<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">

        <i class="fas fa-bus"></i>

        <h2>A.M TURAI</h2>

        <p>Travel & Tours</p>

    </div>


    <div class="menu">

        <a href="dashboard.php">

            <i class="fas fa-gauge"></i>

            <span>Dashboard</span>

        </a>


        <a href="profile.php" class="active">

            <i class="fas fa-user"></i>

            <span>My Profile</span>

        </a>


        <a href="bookings.php">

            <i class="fas fa-ticket"></i>

            <span>My Bookings</span>

        </a>


        <a href="payments.php">

            <i class="fas fa-credit-card"></i>

            <span>My Payments</span>

        </a>


        <a href="../index.php">

            <i class="fas fa-home"></i>

            <span>Home</span>

        </a>

    </div>


    <div class="logout">

        <a href="logout.php">

            <i class="fas fa-right-from-bracket"></i>

            <span>Logout</span>

        </a>

    </div>

</div>


<!-- MAIN -->

<div class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <h1>My Profile</h1>


        <div class="user-info">

            <div class="user-icon">

                <i class="fas fa-user"></i>

            </div>

            <span>

                <?php
                echo htmlspecialchars(
                    $user["full_name"]
                );
                ?>

            </span>

        </div>

    </div>


    <!-- CONTENT -->

    <div class="content">


        <div class="page-intro">

            <h2>

                Account Settings

            </h2>

            <p>

                View and manage your personal information.

            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <div class="profile-grid">


            <!-- PROFILE INFORMATION -->

            <div class="card">

                <div class="card-header">

                    <i class="fas fa-user-pen"></i>

                    <h3>Personal Information</h3>

                </div>


                <form method="POST">

                    <div class="form-group">

                        <label>Full Name</label>

                        <div class="input-box">

                            <i class="fas fa-user"></i>

                            <input
                                type="text"
                                name="full_name"
                                value="<?php
                                echo htmlspecialchars(
                                    $user["full_name"]
                                );
                                ?>"
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
                                value="<?php
                                echo htmlspecialchars(
                                    $user["username"]
                                );
                                ?>"
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
                                value="<?php
                                echo htmlspecialchars(
                                    $user["email"]
                                );
                                ?>"
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
                                value="<?php
                                echo htmlspecialchars(
                                    $user["phone"]
                                );
                                ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label>Address</label>

                        <div class="input-box">

                            <textarea
                                name="address"
                                placeholder="Enter your address"
                            ><?php
                            echo htmlspecialchars(
                                $user["address"] ?? ""
                            );
                            ?></textarea>

                            <i class="fas fa-location-dot"></i>

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="update_profile"
                        class="btn"
                    >

                        <i class="fas fa-save"></i>

                        Save Changes

                    </button>

                </form>

            </div>


            <!-- ACCOUNT INFORMATION -->

            <div class="card">

                <div class="card-header">

                    <i class="fas fa-id-card"></i>

                    <h3>Account Information</h3>

                </div>


                <div class="profile-avatar">

                    <i class="fas fa-user"></i>

                </div>


                <div class="profile-name">

                    <?php
                    echo htmlspecialchars(
                        $user["full_name"]
                    );
                    ?>

                </div>


                <div class="profile-username">

                    @<?php
                    echo htmlspecialchars(
                        $user["username"]
                    );
                    ?>

                </div>


                <div class="info-item">

                    <div class="info-icon">

                        <i class="fas fa-envelope"></i>

                    </div>

                    <div>

                        <small>Email</small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $user["email"]
                            );
                            ?>

                        </strong>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-icon">

                        <i class="fas fa-phone"></i>

                    </div>

                    <div>

                        <small>Phone</small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $user["phone"]
                            );
                            ?>

                        </strong>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-icon">

                        <i class="fas fa-location-dot"></i>

                    </div>

                    <div>

                        <small>Address</small>

                        <strong>

                            <?php

                            echo !empty($user["address"])
                                ? htmlspecialchars(
                                    $user["address"]
                                )
                                : "No address added";

                            ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- CHANGE PASSWORD -->

            <div class="card">

                <div class="card-header">

                    <i class="fas fa-lock"></i>

                    <h3>Change Password</h3>

                </div>


                <div class="password-note">

                    <i class="fas fa-circle-info"></i>

                    Your password must contain at least
                    6 characters.

                </div>


                <form method="POST">

                    <div class="form-group">

                        <label>Current Password</label>

                        <div class="input-box">

                            <i class="fas fa-lock"></i>

                            <input
                                type="password"
                                name="current_password"
                                placeholder="Enter current password"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label>New Password</label>

                        <div class="input-box">

                            <i class="fas fa-key"></i>

                            <input
                                type="password"
                                name="new_password"
                                placeholder="Enter new password"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label>Confirm New Password</label>

                        <div class="input-box">

                            <i class="fas fa-key"></i>

                            <input
                                type="password"
                                name="confirm_password"
                                placeholder="Confirm new password"
                                required
                            >

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="change_password"
                        class="btn"
                    >

                        <i class="fas fa-shield-halved"></i>

                        Change Password

                    </button>

                </form>

            </div>


        </div>

    </div>

</div>


</body>

</html>