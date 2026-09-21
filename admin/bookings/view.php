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
            bookings.*,
            passengers.name AS passenger_name,
            passengers.phone AS passenger_phone,
            routes.departure,
            routes.destination,
            trips.trip_date,
            trips.trip_time,
            vehicles.vehicle_number
        FROM bookings

        INNER JOIN passengers
            ON bookings.passenger_id = passengers.id

        INNER JOIN trips
            ON bookings.trip_id = trips.id

        INNER JOIN routes
            ON trips.route_id = routes.id

        INNER JOIN vehicles
            ON trips.vehicle_id = vehicles.id

        WHERE passengers.name LIKE ?
           OR passengers.phone LIKE ?
           OR routes.departure LIKE ?
           OR routes.destination LIKE ?
           OR vehicles.vehicle_number LIKE ?
           OR bookings.seat_number LIKE ?

        ORDER BY bookings.id DESC
    ");

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ssssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $bookings = $stmt->get_result();

} else {

    $bookings = $conn->query("
        SELECT
            bookings.*,
            passengers.name AS passenger_name,
            passengers.phone AS passenger_phone,
            routes.departure,
            routes.destination,
            trips.trip_date,
            trips.trip_time,
            vehicles.vehicle_number
        FROM bookings

        INNER JOIN passengers
            ON bookings.passenger_id = passengers.id

        INNER JOIN trips
            ON bookings.trip_id = trips.id

        INNER JOIN routes
            ON trips.route_id = routes.id

        INNER JOIN vehicles
            ON trips.vehicle_id = vehicles.id

        ORDER BY bookings.id DESC
    ");

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Bookings - A.M Turai Admin</title>

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

            <a href="../trips/view.php">
                <i class="fa-solid fa-road"></i>
                <span>Trips</span>
            </a>

            <a href="view.php" class="active">
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

                <h2>Bookings</h2>

                <p>Manage passenger trip bookings</p>

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

                        <i class="fa-solid fa-ticket"></i>

                        Bookings

                    </h1>

                    <p>
                        Add, edit, view and manage passenger bookings.
                    </p>

                </div>


                <a href="add.php"
                   class="btn btn-primary">

                    <i class="fa-solid fa-plus"></i>

                    Add Booking

                </a>

            </div>


            <!-- SEARCH -->

            <div class="content-card">

                <form method="GET"
                      class="search-box">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search passenger, route, vehicle or seat..."
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


            <!-- BOOKINGS TABLE -->

            <div class="content-card">

                <div class="table-container">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Passenger</th>

                                <th>Phone</th>

                                <th>Route</th>

                                <th>Vehicle</th>

                                <th>Seat</th>

                                <th>Trip Date</th>

                                <th>Time</th>

                                <th>Booked On</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($bookings && $bookings->num_rows > 0): ?>

                            <?php $count = 1; ?>

                            <?php while ($booking = $bookings->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>


                                    <td>

                                        <strong>

                                            <i class="fa-solid fa-user"></i>

                                            <?php
                                            echo htmlspecialchars(
                                                $booking["passenger_name"]
                                            );
                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-phone"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $booking["passenger_phone"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $booking["departure"]
                                        );
                                        ?>

                                        <i class="fa-solid fa-arrow-right"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $booking["destination"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-bus"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $booking["vehicle_number"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <span class="status status-success">

                                            <i class="fa-solid fa-chair"></i>

                                            <?php
                                            echo htmlspecialchars(
                                                $booking["seat_number"]
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-calendar"></i>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $booking["trip_date"]
                                            )
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <i class="fa-solid fa-clock"></i>

                                        <?php
                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $booking["trip_time"]
                                            )
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo date(
                                            "d M Y h:i A",
                                            strtotime(
                                                $booking["booking_date"]
                                            )
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="edit.php?id=<?php echo $booking["id"]; ?>"
                                                class="btn edit-btn"
                                            >

                                                <i class="fa-solid fa-pen"></i>

                                                Edit

                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $booking["id"]; ?>"
                                                class="btn delete-btn"
                                                onclick="return confirm('Are you sure you want to delete this booking?');"
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
                                    colspan="10"
                                    class="empty-data"
                                >

                                    <i class="fa-solid fa-ticket"></i>

                                    <p>No bookings found.</p>

                                    <a
                                        href="add.php"
                                        class="btn btn-primary"
                                    >

                                        <i class="fa-solid fa-plus"></i>

                                        Add First Booking

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