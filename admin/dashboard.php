<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../database.php";


/* Dashboard statistics */

$vehicles = $conn->query(
    "SELECT COUNT(*) AS total FROM vehicles"
)->fetch_assoc()["total"];

$drivers = $conn->query(
    "SELECT COUNT(*) AS total FROM drivers"
)->fetch_assoc()["total"];

$passengers = $conn->query(
    "SELECT COUNT(*) AS total FROM passengers"
)->fetch_assoc()["total"];

$trips = $conn->query(
    "SELECT COUNT(*) AS total FROM trips"
)->fetch_assoc()["total"];

$bookings = $conn->query(
    "SELECT COUNT(*) AS total FROM bookings"
)->fetch_assoc()["total"];

$payments = $conn->query(
    "SELECT COALESCE(SUM(amount),0) AS total
     FROM payments
     WHERE payment_status='Paid'"
)->fetch_assoc()["total"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | MATURAI</title>

    <link rel="stylesheet"
          href="css/admin-style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>


<body>


<div class="admin-layout">


<!-- SIDEBAR -->

<aside class="sidebar" id="sidebar">


    <div class="sidebar-logo">

        <i class="fa-solid fa-bus"></i>

        <h2>A.M TURAI</h2>

        <p>Travel & Tours</p>

    </div>


    <ul class="sidebar-menu">

        <li>

            <a href="dashboard.php"
               class="active">

                <i class="fa-solid fa-gauge"></i>

                Dashboard

            </a>

        </li>


        <li>

            <a href="vehicles/view.php">

                <i class="fa-solid fa-bus"></i>

                Vehicles

            </a>

        </li>


        <li>

            <a href="drivers/view.php">

                <i class="fa-solid fa-user-tie"></i>

                Drivers

            </a>

        </li>


        <li>

            <a href="passengers/view.php">

                <i class="fa-solid fa-users"></i>

                Passengers

            </a>

        </li>


        <li>

            <a href="routes/view.php">

                <i class="fa-solid fa-route"></i>

                Routes

            </a>

        </li>


        <li>

            <a href="trips/view.php">

                <i class="fa-solid fa-calendar-days"></i>

                Trips

            </a>

        </li>


        <li>

            <a href="bookings/view.php">

                <i class="fa-solid fa-ticket"></i>

                Bookings

            </a>

        </li>


        <li>

            <a href="payments/view.php">

                <i class="fa-solid fa-money-bill"></i>

                Payments

            </a>

        </li>


        <li>

            <a href="reports/index.php">

                <i class="fa-solid fa-chart-column"></i>

                Reports

            </a>

        </li>


        <li>

            <a href="../index.php">

                <i class="fa-solid fa-globe"></i>

                View Website

            </a>

        </li>


        <li>

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </li>

    </ul>


</aside>


<!-- MAIN -->

<main class="admin-main">


    <!-- TOP BAR -->

    <div class="admin-topbar">

        <div>

            <button
                class="menu-toggle"
                onclick="toggleSidebar()">

                <i class="fa-solid fa-bars"></i>

            </button>

        </div>


        <h2>
            Admin Dashboard
        </h2>


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

    </div>


    <!-- PAGE CONTENT -->

    <div class="page-content">


        <div class="page-title">

            <h1>
                Welcome Back, Admin 👋
            </h1>

            <p>
                Manage A.M Turai Travel & Tours
                from your dashboard.
            </p>

        </div>


        <!-- STATISTICS -->

        <div class="dashboard-cards">


            <div class="dashboard-card">

                <div class="card-info">

                    <h3>Vehicles</h3>

                    <p>
                        <?php echo $vehicles; ?>
                    </p>

                </div>

                <div class="card-icon">

                    <i class="fa-solid fa-bus"></i>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-info">

                    <h3>Drivers</h3>

                    <p>
                        <?php echo $drivers; ?>
                    </p>

                </div>

                <div class="card-icon">

                    <i class="fa-solid fa-user-tie"></i>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-info">

                    <h3>Passengers</h3>

                    <p>
                        <?php echo $passengers; ?>
                    </p>

                </div>

                <div class="card-icon">

                    <i class="fa-solid fa-users"></i>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-info">

                    <h3>Trips</h3>

                    <p>
                        <?php echo $trips; ?>
                    </p>

                </div>

                <div class="card-icon">

                    <i class="fa-solid fa-route"></i>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-info">

                    <h3>Bookings</h3>

                    <p>
                        <?php echo $bookings; ?>
                    </p>

                </div>

                <div class="card-icon">

                    <i class="fa-solid fa-ticket"></i>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-info">

                    <h3>Revenue</h3>

                    <p>
                        ₦<?php
                        echo number_format(
                            $payments,
                            2
                        );
                        ?>
                    </p>

                </div>

                <div class="card-icon">

                    <i class="fa-solid fa-naira-sign"></i>

                </div>

            </div>


        </div>


        <!-- QUICK ACTIONS -->

        <div class="content-card">

            <h2>
                Quick Actions
            </h2>


            <div class="action-buttons">

                <a
                    href="vehicles/add.php"
                    class="btn btn-primary">

                    <i class="fa-solid fa-plus"></i>

                    Add Vehicle

                </a>


                <a
                    href="drivers/add.php"
                    class="btn btn-success">

                    <i class="fa-solid fa-user-plus"></i>

                    Add Driver

                </a>


                <a
                    href="routes/add.php"
                    class="btn btn-warning">

                    <i class="fa-solid fa-route"></i>

                    Add Route

                </a>


                <a
                    href="trips/add.php"
                    class="btn btn-secondary">

                    <i class="fa-solid fa-calendar-plus"></i>

                    Add Trip

                </a>

            </div>

        </div>


        <div class="admin-footer">

            © 2026 A.M Turai Travel & Tours —
            Transportation Management System

        </div>


    </div>

</main>

</div>


<script>

function toggleSidebar() {

    document
        .getElementById("sidebar")
        .classList
        .toggle("active");

}

</script>


</body>

</html>