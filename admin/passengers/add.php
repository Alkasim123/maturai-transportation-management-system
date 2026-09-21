<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);

    if ($name == "" || $phone == "") {

        $message = "Please enter the passenger name and phone number.";
        $messageType = "danger";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO passengers
            (name, phone, email, address)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssss",
            $name,
            $phone,
            $email,
            $address
        );

        if ($stmt->execute()) {

            header("Location: view.php");
            exit();

        } else {

            $message =
                "Error adding passenger: " .
                $conn->error;

            $messageType = "danger";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Passenger - A.M Turai Admin</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet"
          href="../css/admin-style.css">

</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-logo">

            <i class="fa-solid fa-bus"></i>

            <div>
                <strong>A.M TURAI</strong>
                <small>ADMIN PANEL</small>
            </div>

        </div>

        <nav class="sidebar-menu">

            <a href="../dashboard.php">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>

            <a href="../vehicles/view.php">
                <i class="fa-solid fa-bus"></i>
                <span>Vehicles</span>
            </a>

            <a href="../drivers/view.php">
                <i class="fa-solid fa-id-card"></i>
                <span>Drivers</span>
            </a>

            <a href="view.php" class="active">
                <i class="fa-solid fa-users"></i>
                <span>Passengers</span>
            </a>

            <a href="../routes/view.php">
                <i class="fa-solid fa-route"></i>
                <span>Routes</span>
            </a>

            <a href="../trips/view.php">
                <i class="fa-solid fa-road"></i>
                <span>Trips</span>
            </a>

            <a href="../bookings/view.php">
                <i class="fa-solid fa-ticket"></i>
                <span>Bookings</span>
            </a>

            <a href="../payments/view.php">
                <i class="fa-solid fa-money-bill"></i>
                <span>Payments</span>
            </a>

            <a href="../reports/index.php">
                <i class="fa-solid fa-chart-column"></i>
                <span>Reports</span>
            </a>

            <a href="../../index.php">
                <i class="fa-solid fa-globe"></i>
                <span>View Website</span>
            </a>

            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>

        </nav>

    </aside>


    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h2>Add Passenger</h2>

                <p>Register a new passenger</p>

            </div>

            <div class="admin-user">

                <i class="fa-solid fa-circle-user"></i>

                <span>
                    <?php
                    echo htmlspecialchars(
                        $_SESSION["admin_username"]
                    );
                    ?>
                </span>

            </div>

        </header>


        <section class="page-content">

            <div class="page-title">

                <div>

                    <h1>
                        <i class="fa-solid fa-user-plus"></i>
                        Add Passenger
                    </h1>

                    <p>
                        Enter passenger information below.
                    </p>

                </div>

                <a href="view.php"
                   class="btn btn-secondary">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back to Passengers

                </a>

            </div>


            <?php if ($message != ""): ?>

                <div class="alert alert-<?php echo $messageType; ?>">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <div class="form-card">

                <form method="POST">

                    <div class="form-group">

                        <label for="name">

                            <i class="fa-solid fa-user"></i>

                            Passenger Name

                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter passenger full name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="phone">

                            <i class="fa-solid fa-phone"></i>

                            Phone Number

                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            placeholder="Enter passenger phone number"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="email">

                            <i class="fa-solid fa-envelope"></i>

                            Email Address

                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter email address"
                        >

                    </div>


                    <div class="form-group">

                        <label for="address">

                            <i class="fa-solid fa-location-dot"></i>

                            Address

                        </label>

                        <textarea
                            id="address"
                            name="address"
                            rows="4"
                            placeholder="Enter passenger address"
                        ></textarea>

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Passenger

                        </button>


                        <a
                            href="view.php"
                            class="btn btn-secondary"
                        >

                            <i class="fa-solid fa-xmark"></i>

                            Cancel

                        </a>

                    </div>

                </form>

            </div>

        </section>


        <footer class="admin-footer">

            <p>

                &copy;
                <?php echo date("Y"); ?>

                A.M Turai Travel & Tours.
                Transportation Management System.

            </p>

        </footer>

    </main>

</div>

</body>

</html>