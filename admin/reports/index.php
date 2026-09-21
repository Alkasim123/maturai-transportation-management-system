<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";


/* =========================================
   SUMMARY COUNTS
========================================= */

$vehicles_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM vehicles
");

$total_vehicles = $vehicles_result->fetch_assoc()["total"];


$drivers_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM drivers
");

$total_drivers = $drivers_result->fetch_assoc()["total"];


$passengers_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM passengers
");

$total_passengers = $passengers_result->fetch_assoc()["total"];


$trips_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM trips
");

$total_trips = $trips_result->fetch_assoc()["total"];


$bookings_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
");

$total_bookings = $bookings_result->fetch_assoc()["total"];


$payments_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM payments
");

$total_payments = $payments_result->fetch_assoc()["total"];


/* =========================================
   PAID REVENUE
========================================= */

$revenue_result = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM payments
    WHERE payment_status = 'Paid'
");

$total_revenue = $revenue_result->fetch_assoc()["total"];


/* =========================================
   PENDING PAYMENTS
========================================= */

$pending_result = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM payments
    WHERE payment_status = 'Pending'
");

$total_pending = $pending_result->fetch_assoc()["total"];


/* =========================================
   RECENT PAYMENTS
========================================= */

$recent_payments = $conn->query("

    SELECT

        payments.id,

        payments.amount,

        payments.payment_status,

        payments.payment_date,

        passengers.name AS passenger_name,

        routes.departure,

        routes.destination

    FROM payments

    INNER JOIN bookings
        ON payments.booking_id = bookings.id

    INNER JOIN passengers
        ON bookings.passenger_id = passengers.id

    INNER JOIN trips
        ON bookings.trip_id = trips.id

    INNER JOIN routes
        ON trips.route_id = routes.id

    ORDER BY payments.id DESC

    LIMIT 10

");


/* =========================================
   RECENT TRIPS
========================================= */

$recent_trips = $conn->query("

    SELECT

        trips.id,

        trips.trip_date,

        trips.trip_time,

        trips.status,

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

    ORDER BY trips.trip_date DESC,
             trips.trip_time DESC

    LIMIT 10

");


/* =========================================
   PAYMENT STATUS SUMMARY
========================================= */

$paid_count_result = $conn->query("

    SELECT COUNT(*) AS total

    FROM payments

    WHERE payment_status = 'Paid'

");

$paid_count = $paid_count_result->fetch_assoc()["total"];


$pending_count_result = $conn->query("

    SELECT COUNT(*) AS total

    FROM payments

    WHERE payment_status = 'Pending'

");

$pending_count = $pending_count_result->fetch_assoc()["total"];


$failed_count_result = $conn->query("

    SELECT COUNT(*) AS total

    FROM payments

    WHERE payment_status = 'Failed'

");

$failed_count = $failed_count_result->fetch_assoc()["total"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Reports - A.M Turai Travel & Tours</title>


<!-- FONT AWESOME -->

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<!-- ADMIN CSS -->

<link rel="stylesheet"
href="../css/admin-style.css">


<style>

/* =====================================
   REPORT PAGE
===================================== */

.report-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

    gap: 20px;

    flex-wrap: wrap;

}


.report-header h1 {

    margin: 0 0 6px;

    font-size: 28px;

}


.report-header p {

    margin: 0;

    color: #777;

}


.report-actions {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}


/* REPORT SUMMARY */

.report-summary {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 30px;

}


.report-box {

    background: white;

    padding: 22px;

    border-radius: 15px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.08);

    display: flex;

    align-items: center;

    gap: 18px;

}


.report-box-icon {

    width: 55px;

    height: 55px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #e8f5e9;

    color: #00695c;

    font-size: 22px;

}


.report-box h3 {

    margin: 0;

    font-size: 24px;

}


.report-box p {

    margin: 5px 0 0;

    color: #777;

}


/* REVENUE */

.revenue-section {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;

    margin-bottom: 30px;

}


.revenue-card {

    background: linear-gradient(
        135deg,
        #004d40,
        #00897b
    );

    color: white;

    padding: 30px;

    border-radius: 18px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.12);

}


.revenue-card.pending {

    background: linear-gradient(
        135deg,
        #795548,
        #a1887f
    );

}


.revenue-card i {

    font-size: 28px;

    margin-bottom: 15px;

}


.revenue-card h2 {

    margin: 0;

    font-size: 30px;

}


.revenue-card p {

    margin: 8px 0 0;

    opacity: .9;

}


/* PAYMENT STATUS */

.status-summary {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 30px;

}


.status-box {

    background: white;

    padding: 25px;

    border-radius: 15px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.08);

}


.status-box h3 {

    margin: 0 0 10px;

}


.status-box .number {

    font-size: 30px;

    font-weight: bold;

}


.status-paid {

    color: #2e7d32;

}


.status-pending {

    color: #ef6c00;

}


.status-failed {

    color: #c62828;

}


/* REPORT TABLE */

.report-section {

    background: white;

    border-radius: 15px;

    padding: 25px;

    margin-bottom: 30px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.08);

}


.report-section-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

}


.report-section-header h2 {

    margin: 0;

    font-size: 20px;

}


.report-table {

    width: 100%;

    border-collapse: collapse;

}


.report-table th {

    background: #f4f7f6;

    padding: 13px;

    text-align: left;

    font-size: 13px;

}


.report-table td {

    padding: 13px;

    border-bottom: 1px solid #eee;

    font-size: 14px;

}


.report-table tr:hover {

    background: #fafafa;

}


/* PRINT */

@media print {

    .sidebar,
    .admin-topbar,
    .report-actions,
    .admin-footer {

        display: none !important;

    }

    .admin-main {

        margin-left: 0 !important;

    }

    .page-content {

        padding: 0 !important;

    }

}


/* RESPONSIVE */

@media (max-width: 1000px) {

    .report-summary {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 700px) {

    .report-summary,
    .revenue-section,
    .status-summary {

        grid-template-columns: 1fr;

    }

    .report-table {

        min-width: 700px;

    }

    .report-section {

        overflow-x: auto;

    }

}

</style>

</head>


<body>


<div class="admin-layout">


<!-- =================================
     SIDEBAR
================================= -->

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

<a href="../payments/view.php">

<i class="fa-solid fa-money-bill-wave"></i>

Payments

</a>

</li>


<li>

<a href="index.php" class="active">

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



<!-- =================================
     MAIN
================================= -->

<main class="admin-main">


<div class="admin-topbar">


<div>

<h2>Reports</h2>

<p>Transportation management reports</p>

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


<!-- =================================
     REPORT HEADER
================================= -->

<div class="report-header">


<div>

<h1>

<i class="fa-solid fa-chart-column"></i>

Management Reports

</h1>

<p>

Overview of A.M Turai Travel & Tours

transportation activities.

</p>

</div>


<div class="report-actions">


<button
onclick="window.print()"
class="btn btn-primary"
>

<i class="fa-solid fa-print"></i>

Print Report

</button>


<a
href="../dashboard.php"
class="btn btn-secondary"
>

<i class="fa-solid fa-arrow-left"></i>

Dashboard

</a>


</div>

</div>



<!-- =================================
     SUMMARY
================================= -->

<div class="report-summary">


<div class="report-box">

<div class="report-box-icon">

<i class="fa-solid fa-bus"></i>

</div>

<div>

<h3>

<?php echo $total_vehicles; ?>

</h3>

<p>Vehicles</p>

</div>

</div>



<div class="report-box">

<div class="report-box-icon">

<i class="fa-solid fa-id-card"></i>

</div>

<div>

<h3>

<?php echo $total_drivers; ?>

</h3>

<p>Drivers</p>

</div>

</div>



<div class="report-box">

<div class="report-box-icon">

<i class="fa-solid fa-users"></i>

</div>

<div>

<h3>

<?php echo $total_passengers; ?>

</h3>

<p>Passengers</p>

</div>

</div>



<div class="report-box">

<div class="report-box-icon">

<i class="fa-solid fa-road"></i>

</div>

<div>

<h3>

<?php echo $total_trips; ?>

</h3>

<p>Trips</p>

</div>

</div>



<div class="report-box">

<div class="report-box-icon">

<i class="fa-solid fa-ticket"></i>

</div>

<div>

<h3>

<?php echo $total_bookings; ?>

</h3>

<p>Bookings</p>

</div>

</div>



<div class="report-box">

<div class="report-box-icon">

<i class="fa-solid fa-money-bill"></i>

</div>

<div>

<h3>

<?php echo $total_payments; ?>

</h3>

<p>Payments</p>

</div>

</div>


</div>



<!-- =================================
     REVENUE
================================= -->

<div class="revenue-section">


<div class="revenue-card">

<i class="fa-solid fa-sack-dollar"></i>

<h2>

₦<?php echo number_format(
    $total_revenue,
    2
); ?>

</h2>

<p>

Total Paid Revenue

</p>

</div>



<div class="revenue-card pending">

<i class="fa-solid fa-clock"></i>

<h2>

₦<?php echo number_format(
    $total_pending,
    2
); ?>

</h2>

<p>

Pending Payment Amount

</p>

</div>


</div>



<!-- =================================
     PAYMENT STATUS
================================= -->

<div class="status-summary">


<div class="status-box">

<h3 class="status-paid">

<i class="fa-solid fa-circle-check"></i>

Paid Payments

</h3>

<div class="number">

<?php echo $paid_count; ?>

</div>

</div>



<div class="status-box">

<h3 class="status-pending">

<i class="fa-solid fa-clock"></i>

Pending Payments

</h3>

<div class="number">

<?php echo $pending_count; ?>

</div>

</div>



<div class="status-box">

<h3 class="status-failed">

<i class="fa-solid fa-circle-xmark"></i>

Failed Payments

</h3>

<div class="number">

<?php echo $failed_count; ?>

</div>

</div>


</div>



<!-- =================================
     RECENT PAYMENTS
================================= -->

<div class="report-section">


<div class="report-section-header">

<h2>

<i class="fa-solid fa-money-bill-wave"></i>

Recent Payments

</h2>


<a
href="../payments/view.php"
class="btn btn-secondary"
>

View All

</a>

</div>


<div style="overflow-x:auto;">

<table class="report-table">


<thead>

<tr>

<th>#</th>

<th>Passenger</th>

<th>Route</th>

<th>Amount</th>

<th>Status</th>

<th>Date</th>

</tr>

</thead>


<tbody>


<?php if ($recent_payments && $recent_payments->num_rows > 0): ?>

<?php $payment_number = 1; ?>


<?php while ($payment = $recent_payments->fetch_assoc()): ?>


<tr>


<td>

<?php echo $payment_number++; ?>

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

$status = strtolower(
    $payment["payment_status"]
);

?>


<span class="status status-<?php echo $status; ?>">

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

</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td colspan="6"
style="text-align:center;padding:30px;">

No payment records available.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>

</div>



<!-- =================================
     RECENT TRIPS
================================= -->

<div class="report-section">


<div class="report-section-header">

<h2>

<i class="fa-solid fa-road"></i>

Recent Trips

</h2>


<a
href="../trips/view.php"
class="btn btn-secondary"
>

View All

</a>

</div>


<div style="overflow-x:auto;">

<table class="report-table">


<thead>

<tr>

<th>#</th>

<th>Route</th>

<th>Vehicle</th>

<th>Driver</th>

<th>Date</th>

<th>Time</th>

<th>Status</th>

</tr>

</thead>


<tbody>


<?php if ($recent_trips && $recent_trips->num_rows > 0): ?>

<?php $trip_number = 1; ?>


<?php while ($trip = $recent_trips->fetch_assoc()): ?>


<tr>


<td>

<?php echo $trip_number++; ?>

</td>


<td>

<strong>

<?php

echo htmlspecialchars(
    $trip["departure"]
);

?>

→

<?php

echo htmlspecialchars(
    $trip["destination"]
);

?>

</strong>

</td>


<td>

<?php

echo htmlspecialchars(
    $trip["vehicle_number"]
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $trip["driver_name"]
);

?>

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

<span class="status">

<?php

echo htmlspecialchars(
    $trip["status"]
);

?>

</span>

</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td colspan="7"
style="text-align:center;padding:30px;">

No trip records available.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>

</div>



<!-- =================================
     REPORT FOOTER
================================= -->

<div class="report-section">

<h2>

<i class="fa-solid fa-circle-info"></i>

Report Information

</h2>

<p>

This report provides a summary of the current
transportation records stored in the
A.M Turai Travel & Tours Transportation
Management System.

</p>

<p>

<strong>Report Generated:</strong>

<?php echo date("d M Y, h:i A"); ?>

</p>

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