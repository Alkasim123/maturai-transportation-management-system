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

    $departure = trim($_POST["departure"]);
    $destination = trim($_POST["destination"]);
    $fare = trim($_POST["fare"]);

    if ($departure == "" || $destination == "" || $fare == "") {

        $message = "Please fill in all required fields.";
        $messageType = "danger";

    } elseif (!is_numeric($fare) || $fare < 0) {

        $message = "Please enter a valid fare.";
        $messageType = "danger";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO routes
            (departure, destination, fare)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "ssd",
            $departure,
            $destination,
            $fare
        );

        if ($stmt->execute()) {

            header("Location: view.php");
            exit();

        } else {

            $message =
                "Error adding route: " .
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

    <title>Add Route - A.M Turai Admin</title>

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

            <a href="../passengers/view.php">
                <i class="fa-solid fa-users"></i>
                <span>Passengers</span>
            </a>

            <a href="view.php" class="active">
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

                <h2>Add Route</h2>

                <p>Create a new travel route</p>

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
                        <i class="fa-solid fa-route"></i>
                        Add Route
                    </h1>

                    <p>
                        Enter the route information below.
                    </p>

                </div>

                <a href="view.php"
                   class="btn btn-secondary">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back to Routes

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

                        <label for="departure">

                            <i class="fa-solid fa-location-dot"></i>

                            Departure

                        </label>

                        <input
                            type="text"
                            id="departure"
                            name="departure"
                            placeholder="Example: Bauchi"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="destination">

                            <i class="fa-solid fa-flag-checkered"></i>

                            Destination

                        </label>

                        <input
                            type="text"
                            id="destination"
                            name="destination"
                            placeholder="Example: Kano"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="fare">

                            <i class="fa-solid fa-naira-sign"></i>

                            Fare

                        </label>

                        <input
                            type="number"
                            id="fare"
                            name="fare"
                            placeholder="Example: 15000"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Route

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