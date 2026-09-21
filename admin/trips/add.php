<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| GET ROUTES
|--------------------------------------------------------------------------
*/

$routes = $conn->query("
    SELECT *
    FROM routes
    ORDER BY departure ASC
");


/*
|--------------------------------------------------------------------------
| GET VEHICLES
|--------------------------------------------------------------------------
*/

$vehicles = $conn->query("
    SELECT *
    FROM vehicles
    ORDER BY vehicle_number ASC
");


/*
|--------------------------------------------------------------------------
| GET DRIVERS
|--------------------------------------------------------------------------
*/

$drivers = $conn->query("
    SELECT *
    FROM drivers
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| ADD TRIP
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $route_id = $_POST["route_id"];
    $vehicle_id = $_POST["vehicle_id"];
    $driver_id = $_POST["driver_id"];
    $trip_date = $_POST["trip_date"];
    $trip_time = $_POST["trip_time"];
    $status = $_POST["status"];


    if (
        empty($route_id) ||
        empty($vehicle_id) ||
        empty($driver_id) ||
        empty($trip_date) ||
        empty($trip_time)
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "danger";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO trips
            (
                route_id,
                vehicle_id,
                driver_id,
                trip_date,
                trip_time,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiisss",
            $route_id,
            $vehicle_id,
            $driver_id,
            $trip_date,
            $trip_time,
            $status
        );


        if ($stmt->execute()) {

            header("Location: view.php");
            exit();

        } else {

            $message =
                "Error adding trip: " .
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

    <title>Add Trip - A.M Turai Admin</title>

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


            <a href="../routes/view.php">

                <i class="fa-solid fa-route"></i>

                <span>Routes</span>

            </a>


            <a href="view.php"
               class="active">

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


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <h2>Add Trip</h2>

                <p>Create a new transportation trip</p>

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


        <!-- PAGE CONTENT -->

        <section class="page-content">


            <div class="page-title">

                <div>

                    <h1>

                        <i class="fa-solid fa-road"></i>

                        Add Trip

                    </h1>

                    <p>
                        Assign a route, vehicle and driver to a trip.
                    </p>

                </div>


                <a href="view.php"
                   class="btn btn-secondary">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back to Trips

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


                    <!-- ROUTE -->

                    <div class="form-group">

                        <label for="route_id">

                            <i class="fa-solid fa-route"></i>

                            Route

                        </label>


                        <select
                            id="route_id"
                            name="route_id"
                            required
                        >

                            <option value="">
                                -- Select Route --
                            </option>


                            <?php if ($routes && $routes->num_rows > 0): ?>

                                <?php while ($route = $routes->fetch_assoc()): ?>

                                    <option
                                        value="<?php echo $route["id"]; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $route["departure"]
                                        );
                                        ?>

                                        →

                                        <?php
                                        echo htmlspecialchars(
                                            $route["destination"]
                                        );
                                        ?>

                                        -
                                        ₦<?php
                                        echo number_format(
                                            $route["fare"],
                                            2
                                        );
                                        ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <!-- VEHICLE -->

                    <div class="form-group">

                        <label for="vehicle_id">

                            <i class="fa-solid fa-bus"></i>

                            Vehicle

                        </label>


                        <select
                            id="vehicle_id"
                            name="vehicle_id"
                            required
                        >

                            <option value="">
                                -- Select Vehicle --
                            </option>


                            <?php if ($vehicles && $vehicles->num_rows > 0): ?>

                                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>

                                    <option
                                        value="<?php echo $vehicle["id"]; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $vehicle["vehicle_number"]
                                        );
                                        ?>

                                        -
                                        <?php
                                        echo htmlspecialchars(
                                            $vehicle["vehicle_type"]
                                        );
                                        ?>

                                        (
                                        <?php
                                        echo $vehicle["capacity"];
                                        ?>
                                        seats)

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <!-- DRIVER -->

                    <div class="form-group">

                        <label for="driver_id">

                            <i class="fa-solid fa-user"></i>

                            Driver

                        </label>


                        <select
                            id="driver_id"
                            name="driver_id"
                            required
                        >

                            <option value="">
                                -- Select Driver --
                            </option>


                            <?php if ($drivers && $drivers->num_rows > 0): ?>

                                <?php while ($driver = $drivers->fetch_assoc()): ?>

                                    <option
                                        value="<?php echo $driver["id"]; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $driver["name"]
                                        );
                                        ?>

                                        -

                                        <?php
                                        echo htmlspecialchars(
                                            $driver["phone"]
                                        );
                                        ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <!-- DATE -->

                    <div class="form-group">

                        <label for="trip_date">

                            <i class="fa-solid fa-calendar"></i>

                            Trip Date

                        </label>


                        <input
                            type="date"
                            id="trip_date"
                            name="trip_date"
                            required
                        >

                    </div>


                    <!-- TIME -->

                    <div class="form-group">

                        <label for="trip_time">

                            <i class="fa-solid fa-clock"></i>

                            Trip Time

                        </label>


                        <input
                            type="time"
                            id="trip_time"
                            name="trip_time"
                            required
                        >

                    </div>


                    <!-- STATUS -->

                    <div class="form-group">

                        <label for="status">

                            <i class="fa-solid fa-circle-check"></i>

                            Status

                        </label>


                        <select
                            id="status"
                            name="status"
                        >

                            <option value="Scheduled">
                                Scheduled
                            </option>

                            <option value="Ongoing">
                                Ongoing
                            </option>

                            <option value="Completed">
                                Completed
                            </option>

                            <option value="Cancelled">
                                Cancelled
                            </option>

                        </select>

                    </div>


                    <!-- BUTTONS -->

                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Trip

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


        <!-- FOOTER -->

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