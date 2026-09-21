<?php

session_start();

include "../database.php";

// Protect page
if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];

// Get total bookings for this user
$total_bookings = 0;

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE passenger_id IN (
         SELECT id
         FROM passengers
         WHERE email = ?
     )"
);

$stmt->bind_param("s", $_SESSION["user_email"]);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $total_bookings = $row["total"];
}

$stmt->close();


// Get total payments
$total_payments = 0;

$stmt = $conn->prepare(
    "SELECT COALESCE(SUM(p.amount), 0) AS total
     FROM payments p
     INNER JOIN bookings b ON p.booking_id = b.id
     INNER JOIN passengers ps ON b.passenger_id = ps.id
     WHERE ps.email = ?
     AND p.payment_status = 'Paid'"
);

$stmt->bind_param("s", $_SESSION["user_email"]);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $total_payments = $row["total"];
}

$stmt->close();


// Get available trips
$available_trips = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM trips
     WHERE status = 'Scheduled'"
);

if ($result && $row = $result->fetch_assoc()) {
    $available_trips = $row["total"];
}


// Get recent trips
$recent_trips = $conn->query(
    "SELECT
        trips.id,
        trips.trip_date,
        trips.trip_time,
        trips.status,
        routes.departure,
        routes.destination,
        routes.fare,
        vehicles.vehicle_number
     FROM trips
     INNER JOIN routes
        ON trips.route_id = routes.id
     INNER JOIN vehicles
        ON trips.vehicle_id = vehicles.id
     WHERE trips.status = 'Scheduled'
     ORDER BY trips.trip_date ASC,
              trips.trip_time ASC
     LIMIT 5"
);

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
        User Dashboard - A.M Turai Travel & Tours
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

            border-bottom: 1px solid rgba(255,255,255,0.15);

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

        .welcome {

            background:
                linear-gradient(
                    135deg,
                    #00695c,
                    #00897b
                );

            color: white;

            padding: 30px;

            border-radius: 15px;

            margin-bottom: 25px;

            box-shadow:
                0 8px 20px rgba(0,0,0,0.12);
        }

        .welcome h2 {

            font-size: 25px;

            margin-bottom: 8px;

        }

        .welcome p {

            opacity: 0.9;

            font-size: 14px;

        }

        /* CARDS */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }

        .card {

            background: white;

            border-radius: 13px;

            padding: 22px;

            display: flex;

            align-items: center;

            gap: 17px;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);

        }

        .card-icon {

            width: 52px;

            height: 52px;

            border-radius: 12px;

            background: #e0f2f1;

            color: #00695c;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

        }

        .card h3 {

            font-size: 23px;

            color: #004d40;

            margin-bottom: 3px;

        }

        .card p {

            color: #777;

            font-size: 13px;

        }

        /* SECTION */

        .section {

            background: white;

            border-radius: 13px;

            padding: 25px;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);

            margin-bottom: 25px;

        }

        .section-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

        }

        .section-header h2 {

            color: #004d40;

            font-size: 19px;

        }

        .view-all {

            color: #00695c;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }

        /* TABLE */

        .table-container {

            overflow-x: auto;

        }

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 650px;

        }

        th {

            background: #004d40;

            color: white;

            padding: 13px;

            text-align: left;

            font-size: 13px;

        }

        td {

            padding: 13px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

        }

        tr:hover td {

            background: #f7faf9;

        }

        .status {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: bold;

            background: #dff5e8;

            color: #176b3a;

        }

        .book-btn {

            display: inline-block;

            padding: 7px 12px;

            background: #00695c;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            font-size: 11px;

        }

        .book-btn:hover {

            background: #004d40;

        }

        .empty {

            text-align: center;

            padding: 30px;

            color: #777;

        }

        /* QUICK ACTIONS */

        .quick-actions {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

        }

        .action {

            padding: 20px;

            border: 1px solid #eee;

            border-radius: 10px;

            text-align: center;

            text-decoration: none;

            color: #333;

            transition: 0.3s;

        }

        .action:hover {

            border-color: #00695c;

            transform: translateY(-3px);

            box-shadow:
                0 5px 15px rgba(0,0,0,0.08);

        }

        .action i {

            font-size: 27px;

            color: #00695c;

            margin-bottom: 10px;

        }

        .action h3 {

            font-size: 14px;

        }

        /* MOBILE */

        @media (max-width: 1000px) {

            .cards {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }

        @media (max-width: 750px) {

            .sidebar {

                width: 70px;

                padding: 15px 8px;

            }

            .logo h2,
            .logo p,
            .menu span {

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

            .quick-actions {

                grid-template-columns: 1fr;

            }

        }

        @media (max-width: 500px) {

            .cards {

                grid-template-columns: 1fr;

            }

            .welcome {

                padding: 22px;

            }

            .welcome h2 {

                font-size: 21px;

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

        <a href="dashboard.php" class="active">

            <i class="fas fa-gauge"></i>

            <span>Dashboard</span>

        </a>

        <a href="profile.php">

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

        <h1>User Dashboard</h1>

        <div class="user-info">

            <div class="user-icon">

                <i class="fas fa-user"></i>

            </div>

            <span>
                <?php echo htmlspecialchars($user_name); ?>
            </span>

        </div>

    </div>


    <!-- CONTENT -->

    <div class="content">


        <!-- WELCOME -->

        <div class="welcome">

            <h2>

                Welcome,
                <?php echo htmlspecialchars($user_name); ?>! 👋

            </h2>

            <p>

                Manage your travel bookings,
                payments and account from your dashboard.

            </p>

        </div>


        <!-- STATISTICS -->

        <div class="cards">


            <div class="card">

                <div class="card-icon">

                    <i class="fas fa-ticket"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $total_bookings; ?>
                    </h3>

                    <p>My Bookings</p>

                </div>

            </div>


            <div class="card">

                <div class="card-icon">

                    <i class="fas fa-money-bill-wave"></i>

                </div>

                <div>

                    <h3>
                        ₦<?php echo number_format($total_payments, 2); ?>
                    </h3>

                    <p>Total Paid</p>

                </div>

            </div>


            <div class="card">

                <div class="card-icon">

                    <i class="fas fa-bus"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $available_trips; ?>
                    </h3>

                    <p>Available Trips</p>

                </div>

            </div>


            <div class="card">

                <div class="card-icon">

                    <i class="fas fa-user-check"></i>

                </div>

                <div>

                    <h3>Active</h3>

                    <p>Account Status</p>

                </div>

            </div>


        </div>


        <!-- AVAILABLE TRIPS -->

        <div class="section">

            <div class="section-header">

                <h2>

                    <i class="fas fa-route"></i>

                    Available Trips

                </h2>

                <a href="bookings.php" class="view-all">

                    My Bookings

                    <i class="fas fa-arrow-right"></i>

                </a>

            </div>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>Route</th>

                            <th>Date</th>

                            <th>Time</th>

                            <th>Vehicle</th>

                            <th>Fare</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if (
                        $recent_trips &&
                        $recent_trips->num_rows > 0
                    ):

                        while (
                            $trip =
                            $recent_trips->fetch_assoc()
                        ):

                    ?>

                        <tr>

                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $trip["departure"]
                                    );
                                    ?>
                                </strong>

                                →

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $trip["destination"]
                                    );
                                    ?>
                                </strong>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $trip["trip_date"]
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $trip["trip_time"]
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $trip["vehicle_number"]
                                );
                                ?>

                            </td>

                            <td>

                                ₦<?php
                                echo number_format(
                                    $trip["fare"],
                                    2
                                );
                                ?>

                            </td>

                            <td>

                                <span class="status">

                                    <?php
                                    echo htmlspecialchars(
                                        $trip["status"]
                                    );
                                    ?>

                                </span>

                            </td>

                            <td>

                                <a
                                    href="bookings.php?trip_id=<?php echo $trip['id']; ?>"
                                    class="book-btn"
                                >

                                    <i class="fas fa-ticket"></i>

                                    Book

                                </a>

                            </td>

                        </tr>

                    <?php

                        endwhile;

                    else:

                    ?>

                        <tr>

                            <td
                                colspan="7"
                                class="empty"
                            >

                                <i class="fas fa-bus-slash"></i>

                                <br><br>

                                No scheduled trips available at the moment.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- QUICK ACTIONS -->

        <div class="section">

            <div class="section-header">

                <h2>

                    <i class="fas fa-bolt"></i>

                    Quick Actions

                </h2>

            </div>


            <div class="quick-actions">

                <a
                    href="profile.php"
                    class="action"
                >

                    <i class="fas fa-user-edit"></i>

                    <h3>Update Profile</h3>

                </a>


                <a
                    href="bookings.php"
                    class="action"
                >

                    <i class="fas fa-ticket-alt"></i>

                    <h3>View My Bookings</h3>

                </a>


                <a
                    href="payments.php"
                    class="action"
                >

                    <i class="fas fa-receipt"></i>

                    <h3>View Payments</h3>

                </a>

            </div>

        </div>


    </div>

</div>

</body>

</html>