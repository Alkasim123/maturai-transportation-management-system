<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "../database.php";

$user_id = $_SESSION["user_id"];
$user_email = $_SESSION["user_email"];

$message = "";
$message_type = "";

/* Get customer information */
$user_sql = "SELECT * FROM customer_users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/* Find passenger connected to this customer */
$passenger_sql = "SELECT * FROM passengers WHERE email = ? LIMIT 1";
$passenger_stmt = $conn->prepare($passenger_sql);
$passenger_stmt->bind_param("s", $user_email);
$passenger_stmt->execute();
$passenger_result = $passenger_stmt->get_result();
$passenger = $passenger_result->fetch_assoc();

$passenger_id = $passenger ? $passenger["id"] : 0;

/* Get payment records */
$payments = [];

if ($passenger_id > 0) {

    $payment_sql = "
        SELECT
            b.id AS booking_id,
            b.seat_number,
            b.booking_date,

            t.trip_date,
            t.trip_time,

            r.departure,
            r.destination,
            r.fare,

            v.vehicle_number,

            p.id AS payment_id,
            p.amount,
            p.payment_status,
            p.payment_date

        FROM bookings b

        INNER JOIN trips t
            ON b.trip_id = t.id

        INNER JOIN routes r
            ON t.route_id = r.id

        INNER JOIN vehicles v
            ON t.vehicle_id = v.id

        LEFT JOIN payments p
            ON b.id = p.booking_id

        WHERE b.passenger_id = ?

        ORDER BY b.booking_date DESC
    ";

    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param("i", $passenger_id);
    $payment_stmt->execute();

    $payment_result = $payment_stmt->get_result();

    while ($row = $payment_result->fetch_assoc()) {
        $payments[] = $row;
    }
}

/* Calculate totals */
$total_paid = 0;
$total_pending = 0;
$total_bookings = count($payments);

foreach ($payments as $payment) {

    $amount = floatval($payment["amount"] ?? 0);

    if (strtolower($payment["payment_status"] ?? "") === "paid") {
        $total_paid += $amount;
    } else {
        $total_pending += $amount;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Payments - A.M Turai Travel & Tours</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f7f5;
            color: #243b37;
        }

        .topbar {
            height: 70px;
            background: #00695c;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.15);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: bold;
        }

        .brand i {
            font-size: 26px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            color: #00695c;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .sidebar {
            width: 240px;
            background: #004d40;
            color: white;
            padding: 25px 15px;
        }

        .sidebar h3 {
            font-size: 13px;
            text-transform: uppercase;
            margin: 0 10px 15px;
            color: #b2dfdb;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 13px;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            border-radius: 10px;
            margin-bottom: 7px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #00796b;
        }

        .sidebar a i {
            width: 20px;
            text-align: center;
        }

        .logout {

            position: relative;

            bottom: 25px;

            left: 1px;

            right: 1px;

            margin-top:170px
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

        .main {
            flex: 1;
            padding: 30px;
        }

        .page-title {
            margin-bottom: 25px;
        }

        .page-title h1 {
            font-size: 28px;
            color: #004d40;
            margin-bottom: 7px;
        }

        .page-title p {
            color: #6b7d78;
        }

        /* Summary cards */

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            padding: 22px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 17px;
        }

        .summary-icon {
            width: 55px;
            height: 55px;
            border-radius: 13px;
            background: #e0f2f1;
            color: #00695c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .summary-card h3 {
            font-size: 13px;
            color: #71827d;
            margin-bottom: 6px;
        }

        .summary-card strong {
            font-size: 22px;
            color: #004d40;
        }

        /* Payment table */

        .payment-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .payment-header {
            padding: 22px;
            border-bottom: 1px solid #e5eeee;
        }

        .payment-header h2 {
            color: #004d40;
            font-size: 20px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th {
            background: #00695c;
            color: white;
            padding: 14px;
            text-align: left;
            font-size: 13px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #edf2f1;
            font-size: 14px;
        }

        tr:hover td {
            background: #f6fbfa;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .paid {
            background: #d9f7e9;
            color: #087443;
        }

        .pending {
            background: #fff2cc;
            color: #946200;
        }

        .failed {
            background: #ffe0e0;
            color: #b42318;
        }

        .no-payment {
            background: #eeeeee;
            color: #666666;
        }

        .empty {
            text-align: center;
            padding: 60px 20px;
        }

        .empty i {
            font-size: 55px;
            color: #b2dfdb;
            margin-bottom: 15px;
        }

        .empty h3 {
            color: #004d40;
            margin-bottom: 8px;
        }

        .empty p {
            color: #71827d;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 11px 18px;
            background: #00695c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .back-btn:hover {
            background: #004d40;
        }

        @media (max-width: 900px) {

            .sidebar {
                width: 200px;
            }

            .summary {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {

            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .sidebar a {
                display: inline-flex;
                margin-right: 5px;
            }

            .main {
                padding: 20px;
            }

            .topbar {
                padding: 0 15px;
            }

            .brand span {
                display: none;
            }
        }

    </style>

</head>

<body>

<!-- TOP BAR -->

<div class="topbar">

    <div class="brand">
        <i class="fa-solid fa-bus"></i>
        <span>A.M Turai Travel & Tours</span>
    </div>

    <div class="user-info">

        <div>
            <?php echo htmlspecialchars($user["full_name"]); ?>
        </div>

        <div class="user-icon">
            <i class="fa-solid fa-user"></i>
        </div>

    </div>

</div>


<div class="layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h3>Customer Menu</h3>

        <a href="dashboard.php">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>

        <a href="bookings.php">
            <i class="fa-solid fa-ticket"></i>
            My Bookings
        </a>

        <a href="payments.php" class="active">
            <i class="fa-solid fa-credit-card"></i>
            My Payments
        </a>

        <a href="profile.php">
            <i class="fa-solid fa-user-gear"></i>
            My Profile
        </a>

        <a href="../index.php">
            <i class="fa-solid fa-globe"></i>
            Home
        </a>

       <div class="logout">

            <a href="logout.php">

               <i class="fas fa-right-from-bracket"></i>

               <span>Logout</span>

            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main">

        <div class="page-title">

            <h1>
                <i class="fa-solid fa-credit-card"></i>
                My Payments
            </h1>

            <p>
                View your booking payments and payment status.
            </p>

        </div>


        <!-- SUMMARY -->

        <div class="summary">

            <div class="summary-card">

                <div class="summary-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>

                <div>

                    <h3>Total Bookings</h3>

                    <strong>
                        <?php echo $total_bookings; ?>
                    </strong>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>

                <div>

                    <h3>Total Paid</h3>

                    <strong>
                        ₦<?php echo number_format($total_paid, 2); ?>
                    </strong>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div>

                    <h3>Pending Amount</h3>

                    <strong>
                        ₦<?php echo number_format($total_pending, 2); ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- PAYMENTS -->

        <div class="payment-box">

            <div class="payment-header">

                <h2>
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Payment History
                </h2>

            </div>


            <?php if (count($payments) > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>Booking ID</th>

                                <th>Route</th>

                                <th>Trip Date</th>

                                <th>Time</th>

                                <th>Seat</th>

                                <th>Fare</th>

                                <th>Amount Paid</th>

                                <th>Status</th>

                                <th>Payment Date</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($payments as $payment): ?>

                                <?php

                                $status = $payment["payment_status"];

                                if (!$status) {
                                    $status = "No Payment";
                                }

                                $status_class = strtolower(str_replace(" ", "-", $status));

                                if ($status_class == "no-payment") {
                                    $status_class = "no-payment";
                                }

                                ?>

                                <tr>

                                    <td>
                                        #<?php echo $payment["booking_id"]; ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $payment["departure"] .
                                            " → " .
                                            $payment["destination"]
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime($payment["trip_date"])
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            "h:i A",
                                            strtotime($payment["trip_time"])
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($payment["seat_number"]); ?>
                                    </td>

                                    <td>
                                        ₦<?php echo number_format($payment["fare"], 2); ?>
                                    </td>

                                    <td>

                                        <?php if ($payment["amount"] !== null): ?>

                                            ₦<?php
                                            echo number_format(
                                                $payment["amount"],
                                                2
                                            );
                                            ?>

                                        <?php else: ?>

                                            ₦0.00

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if ($status_class == "paid"): ?>

                                            <span class="status paid">
                                                <i class="fa-solid fa-circle-check"></i>
                                                Paid
                                            </span>

                                        <?php elseif ($status_class == "pending"): ?>

                                            <span class="status pending">
                                                <i class="fa-solid fa-clock"></i>
                                                Pending
                                            </span>

                                        <?php elseif ($status_class == "failed"): ?>

                                            <span class="status failed">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                                Failed
                                            </span>

                                        <?php else: ?>

                                            <span class="status no-payment">
                                                <i class="fa-solid fa-minus"></i>
                                                No Payment
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if ($payment["payment_date"]): ?>

                                            <?php
                                            echo date(
                                                "d M Y h:i A",
                                                strtotime($payment["payment_date"])
                                            );
                                            ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty">

                    <i class="fa-solid fa-receipt"></i>

                    <h3>No Payment Records Yet</h3>

                    <p>
                        You do not have any bookings or payment records yet.
                    </p>

                    <a href="bookings.php" class="back-btn">
                        <i class="fa-solid fa-ticket"></i>
                        View Available Trips
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>
</html>