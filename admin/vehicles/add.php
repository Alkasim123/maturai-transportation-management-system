<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$message = "";

if (isset($_POST["add_vehicle"])) {

    $vehicle_number = trim($_POST["vehicle_number"]);
    $vehicle_type = trim($_POST["vehicle_type"]);
    $capacity = intval($_POST["capacity"]);
    $status = $_POST["status"];

    $stmt = $conn->prepare(
        "INSERT INTO vehicles
        (vehicle_number, vehicle_type, capacity, status)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssis",
        $vehicle_number,
        $vehicle_type,
        $capacity,
        $status
    );

    if ($stmt->execute()) {

        echo "<script>
                alert('Vehicle added successfully!');
                window.location='view.php';
              </script>";

        exit();

    } else {

        $message =
            "Error: " . $stmt->error;

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

    <title>Add Vehicle | MATURAI</title>

    <link rel="stylesheet"
          href="../css/admin-style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-layout">


<aside class="sidebar">

    <div class="sidebar-logo">

        <i class="fa-solid fa-bus"></i>

        <h2>A.M TURAI</h2>

        <p>Travel & Tours</p>

    </div>


    <ul class="sidebar-menu">

        <li>
            <a href="../dashboard.php">
                <i class="fa-solid fa-gauge"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="view.php" class="active">
                <i class="fa-solid fa-bus"></i>
                Vehicles
            </a>
        </li>

        <li>
            <a href="../drivers/view.php">
                <i class="fa-solid fa-user-tie"></i>
                Drivers
            </a>
        </li>

        <li>
            <a href="../passengers/view.php">
                <i class="fa-solid fa-users"></i>
                Passengers
            </a>
        </li>

        <li>
            <a href="../routes/view.php">
                <i class="fa-solid fa-route"></i>
                Routes
            </a>
        </li>

        <li>
            <a href="../trips/view.php">
                <i class="fa-solid fa-calendar-days"></i>
                Trips
            </a>
        </li>

        <li>
            <a href="../bookings/view.php">
                <i class="fa-solid fa-ticket"></i>
                Bookings
            </a>
        </li>

        <li>
            <a href="../payments/view.php">
                <i class="fa-solid fa-money-bill"></i>
                Payments
            </a>
        </li>

        <li>
            <a href="../reports/index.php">
                <i class="fa-solid fa-chart-column"></i>
                Reports
            </a>
        </li>

        <li>
            <a href="../../index.php">
                <i class="fa-solid fa-globe"></i>
                Website
            </a>
        </li>

        <li>
            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </li>

    </ul>

</aside>


<main class="admin-main">


<div class="admin-topbar">

    <button
        class="menu-toggle"
        onclick="toggleSidebar()">

        <i class="fa-solid fa-bars"></i>

    </button>

    <h2>Add Vehicle</h2>

    <div class="admin-user">

        <i class="fa-solid fa-circle-user"></i>

        <?php
        echo htmlspecialchars(
            $_SESSION["admin_username"]
        );
        ?>

    </div>

</div>


<div class="page-content">


<div class="page-title">

    <h1>Add New Vehicle</h1>

    <p>
        Register a new vehicle in the system.
    </p>

</div>


<?php if ($message != ""): ?>

<div class="alert alert-danger">

    <?php echo htmlspecialchars($message); ?>

</div>

<?php endif; ?>


<div class="form-card">


<form method="POST">


<div class="form-group">

    <label>
        Vehicle Number
    </label>

    <input
        type="text"
        name="vehicle_number"
        placeholder="e.g. ABC-123-KD"
        required
    >

</div>


<div class="form-group">

    <label>
        Vehicle Type
    </label>

    <select name="vehicle_type" required>

        <option value="">
            Select Vehicle Type
        </option>

        <option value="Bus">
            Bus
        </option>

        <option value="Mini Bus">
            Mini Bus
        </option>

        <option value="Van">
            Van
        </option>

        <option value="Car">
            Car
        </option>

    </select>

</div>


<div class="form-group">

    <label>
        Seat Capacity
    </label>

    <input
        type="number"
        name="capacity"
        min="1"
        placeholder="e.g. 30"
        required
    >

</div>


<div class="form-group">

    <label>
        Vehicle Status
    </label>

    <select name="status">

        <option value="Available">
            Available
        </option>

        <option value="Assigned">
            Assigned
        </option>

        <option value="Maintenance">
            Maintenance
        </option>

    </select>

</div>


<div class="action-buttons">

    <button
        type="submit"
        name="add_vehicle"
        class="btn btn-primary"
    >

        <i class="fa-solid fa-save"></i>

        Save Vehicle

    </button>


    <a
        href="view.php"
        class="btn btn-secondary"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back

    </a>

</div>


</form>

</div>

</div>


<div class="admin-footer">

    © 2026 A.M Turai Travel & Tours

</div>


</main>

</div>


<script>

function toggleSidebar() {

    document
        .querySelector(".sidebar")
        .classList
        .toggle("active");

}

</script>

</body>

</html>