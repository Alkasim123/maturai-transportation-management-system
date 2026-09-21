<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";


/* SEARCH */

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}


if ($search != "") {

    $safe_search = $conn->real_escape_string($search);

    $sql = "
        SELECT
            payments.*,
            bookings.seat_number,
            passengers.name AS passenger_name,
            passengers.phone AS passenger_phone,
            routes.departure,
            routes.destination,
            trips.trip_date,
            trips.trip_time,
            vehicles.vehicle_number
        FROM payments

        INNER JOIN bookings
            ON payments.booking_id = bookings.id

        INNER JOIN passengers
            ON bookings.passenger_id = passengers.id

        INNER JOIN trips
            ON bookings.trip_id = trips.id

        INNER JOIN routes
            ON trips.route_id = routes.id

        INNER JOIN vehicles
            ON trips.vehicle_id = vehicles.id

        WHERE
            passengers.name LIKE '%$safe_search%'
            OR passengers.phone LIKE '%$safe_search%'
            OR routes.departure LIKE '%$safe_search%'
            OR routes.destination LIKE '%$safe_search%'
            OR payments.payment_status LIKE '%$safe_search%'
            OR payments.amount LIKE '%$safe_search%'
            OR vehicles.vehicle_number LIKE '%$safe_search%'

        ORDER BY payments.id DESC
    ";

} else {

    $sql = "
        SELECT
            payments.*,
            bookings.seat_number,
            passengers.name AS passenger_name,
            passengers.phone AS passenger_phone,
            routes.departure,
            routes.destination,
            trips.trip_date,
            trips.trip_time,
            vehicles.vehicle_number
        FROM payments

        INNER JOIN bookings
            ON payments.booking_id = bookings.id

        INNER JOIN passengers
            ON bookings.passenger_id = passengers.id

        INNER JOIN trips
            ON bookings.trip_id = trips.id

        INNER JOIN routes
            ON trips.route_id = routes.id

        INNER JOIN vehicles
            ON trips.vehicle_id = vehicles.id

        ORDER BY payments.id DESC
    ";

}


$payments = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payments - A.M Turai</title>

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

            <span>A.M TURAI</span>

        </div>


        <ul class="sidebar-menu">

            <li>
                <a href="../dashboard.php">

                    <i class="fa-solid fa-gauge"></i>

                    Dashboard

                </a>
            </li>


            <li>
                <a href="../vehicles/view.php">

                    <i class="fa-solid fa-bus"></i>

                    Vehicles

                </a>
            </li>


            <li>
                <a href="../drivers/view.php">

                    <i class="fa-solid fa-id-card"></i>

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

                    <i class="fa-solid fa-road"></i>

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
                <a href="view.php" class="active">

                    <i class="fa-solid fa-money-bill-wave"></i>

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

                    View Website

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



    <!-- MAIN -->

    <main class="admin-main">


        <div class="admin-topbar">

            <div>

                <h2>Payments</h2>

                <p>Manage transportation payments</p>

            </div>


            <div class="admin-user">

                <i class="fa-solid fa-user-circle"></i>

                <?php
                echo htmlspecialchars(
                    $_SESSION["admin_username"]
                );
                ?>

            </div>

        </div>



        <div class="page-content">


            <!-- PAGE TITLE -->

            <div class="page-title">

                <div>

                    <h1>
                        <i class="fa-solid fa-money-bill-wave"></i>
                        Payment Records
                    </h1>

                    <p>
                        View and manage customer payments.
                    </p>

                </div>


                <a
                    href="add.php"
                    class="btn btn-primary"
                >

                    <i class="fa-solid fa-plus"></i>

                    Add Payment

                </a>

            </div>



            <!-- SEARCH -->

            <div class="search-box">

                <form method="GET">

                    <input
                        type="text"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search passenger, route, vehicle, amount or status..."
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-search"></i>

                        Search

                    </button>


                    <?php if ($search != ""): ?>

                        <a
                            href="view.php"
                            class="btn btn-secondary"
                        >

                            <i class="fa-solid fa-xmark"></i>

                            Clear

                        </a>

                    <?php endif; ?>

                </form>

            </div>



            <!-- TABLE -->

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

                                <th>Amount</th>

                                <th>Status</th>

                                <th>Payment Date</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($payments && $payments->num_rows > 0): ?>

                            <?php $number = 1; ?>

                            <?php while ($payment = $payments->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo $number++; ?>
                                    </td>


                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $payment["passenger_name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $payment["passenger_phone"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $payment["departure"]
                                        );
                                        ?>

                                        →

                                        <?php
                                        echo htmlspecialchars(
                                            $payment["destination"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $payment["vehicle_number"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $payment["seat_number"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <strong>

                                            ₦<?php
                                            echo number_format(
                                                $payment["amount"],
                                                2
                                            );
                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        $status =
                                            strtolower(
                                                $payment["payment_status"]
                                            );

                                        ?>

                                        <span
                                            class="status
                                            status-<?php echo $status; ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $payment["payment_status"]
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $payment["payment_date"]
                                            )
                                        );
                                        ?>

                                        <br>

                                        <small>

                                            <?php
                                            echo date(
                                                "h:i A",
                                                strtotime(
                                                    $payment["payment_date"]
                                                )
                                            );
                                            ?>

                                        </small>

                                    </td>


                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="edit.php?id=<?php echo $payment["id"]; ?>"
                                                class="btn edit-btn"
                                                title="Edit Payment"
                                            >

                                                <i class="fa-solid fa-pen"></i>

                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $payment["id"]; ?>"
                                                class="btn delete-btn"
                                                title="Delete Payment"
                                                onclick="return confirm('Are you sure you want to delete this payment?');"
                                            >

                                                <i class="fa-solid fa-trash"></i>

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

                                    <i class="fa-solid fa-money-bill-wave"></i>

                                    <p>
                                        No payment records found.
                                    </p>

                                    <a
                                        href="add.php"
                                        class="btn btn-primary"
                                    >

                                        <i class="fa-solid fa-plus"></i>

                                        Add First Payment

                                    </a>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


        </div>


        <footer class="admin-footer">

            <p>

                © <?php echo date("Y"); ?>

                A.M Turai Travel & Tours

            </p>

        </footer>


    </main>

</div>

</body>

</html>