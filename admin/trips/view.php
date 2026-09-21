<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search != "") {

    $stmt = $conn->prepare("
        SELECT
            trips.*,
            routes.departure,
            routes.destination,
            vehicles.vehicle_number,
            drivers.name AS driver_name
        FROM trips
        INNER JOIN routes
            ON trips.route_id = routes.id
        INNER JOIN vehicles
            ON trips.vehicle_id = vehicles.id
        INNER JOIN drivers
            ON trips.driver_id = drivers.id
        WHERE routes.departure LIKE ?
           OR routes.destination LIKE ?
           OR vehicles.vehicle_number LIKE ?
           OR drivers.name LIKE ?
           OR trips.status LIKE ?
        ORDER BY trips.id DESC
    ");

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "sssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $trips = $stmt->get_result();

} else {

    $trips = $conn->query("
        SELECT
            trips.*,
            routes.departure,
            routes.destination,
            vehicles.vehicle_number,
            drivers.name AS driver_name
        FROM trips
        INNER JOIN routes
            ON trips.route_id = routes.id
        INNER JOIN vehicles
            ON trips.vehicle_id = vehicles.id
        INNER JOIN drivers
            ON trips.driver_id = drivers.id
        ORDER BY trips.id DESC
    ");

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Trips - A.M Turai Admin</title>

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

            <a href="view.php" class="active">
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

                <h2>Trips</h2>

                <p>Manage scheduled transportation trips</p>

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


        <!-- PAGE -->

        <section class="page-content">

            <div class="page-title">

                <div>

                    <h1>

                        <i class="fa-solid fa-road"></i>

                        Trips

                    </h1>

                    <p>
                        Manage routes, vehicles, drivers and trip schedules.
                    </p>

                </div>

                <a href="add.php"
                   class="btn btn-primary">

                    <i class="fa-solid fa-plus"></i>

                    Add Trip

                </a>

            </div>


            <!-- SEARCH -->

            <div class="content-card">

                <form method="GET"
                      class="search-box">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search route, vehicle, driver or status..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-magnifying-glass"></i>

                        Search

                    </button>

                    <?php if ($search != ""): ?>

                        <a
                            href="view.php"
                            class="btn btn-secondary"
                        >

                            <i class="fa-solid fa-rotate-left"></i>

                            Clear

                        </a>

                    <?php endif; ?>

                </form>

            </div>


            <!-- TRIPS TABLE -->

            <div class="content-card">

                <div class="table-container">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Route</th>

                                <th>Vehicle</th>

                                <th>Driver</th>

                                <th>Date</th>

                                <th>Time</th>

                                <th>Status</th>

                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($trips && $trips->num_rows > 0): ?>

                            <?php $count = 1; ?>

                            <?php while ($trip = $trips->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>


                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $trip["departure"]
                                            );
                                            ?>

                                        </strong>

                                        <i class="fa-solid fa-arrow-right"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $trip["destination"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-bus"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $trip["vehicle_number"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-user"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $trip["driver_name"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-calendar"></i>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime($trip["trip_date"])
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-clock"></i>

                                        <?php
                                        echo date(
                                            "h:i A",
                                            strtotime($trip["trip_time"])
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        $status =
                                            strtolower(
                                                $trip["status"]
                                            );

                                        if ($status == "scheduled") {

                                            echo '<span class="status status-success">
                                                    Scheduled
                                                  </span>';

                                        } elseif ($status == "completed") {

                                            echo '<span class="status status-success">
                                                    Completed
                                                  </span>';

                                        } elseif ($status == "cancelled") {

                                            echo '<span class="status status-danger">
                                                    Cancelled
                                                  </span>';

                                        } elseif ($status == "ongoing") {

                                            echo '<span class="status status-warning">
                                                    Ongoing
                                                  </span>';

                                        } else {

                                            echo '<span class="status status-secondary">'
                                                . htmlspecialchars(
                                                    $trip["status"]
                                                )
                                                . '</span>';

                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="edit.php?id=<?php echo $trip["id"]; ?>"
                                                class="btn edit-btn"
                                            >

                                                <i class="fa-solid fa-pen"></i>

                                                Edit

                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $trip["id"]; ?>"
                                                class="btn delete-btn"
                                                onclick="return confirm('Are you sure you want to delete this trip?');"
                                            >

                                                <i class="fa-solid fa-trash"></i>

                                                Delete

                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="empty-data"
                                >

                                    <i class="fa-solid fa-road"></i>

                                    <p>No trips found.</p>

                                    <a
                                        href="add.php"
                                        class="btn btn-primary"
                                    >

                                        <i class="fa-solid fa-plus"></i>

                                        Add First Trip

                                    </a>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

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