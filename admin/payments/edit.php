<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: view.php");
    exit();
}


/* UPDATE PAYMENT */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $booking_id = intval($_POST["booking_id"]);
    $amount = floatval($_POST["amount"]);
    $payment_status = $_POST["payment_status"];

    $stmt = $conn->prepare("
        UPDATE payments
        SET booking_id = ?,
            amount = ?,
            payment_status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "idsi",
        $booking_id,
        $amount,
        $payment_status,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    }

    $stmt->close();
}


/* GET BOOKINGS */

$bookings = $conn->query("
    SELECT
        bookings.id,
        bookings.seat_number,
        passengers.name AS passenger_name,
        passengers.phone,
        routes.departure,
        routes.destination,
        trips.trip_date,
        trips.trip_time,
        routes.fare

    FROM bookings

    INNER JOIN passengers
        ON bookings.passenger_id = passengers.id

    INNER JOIN trips
        ON bookings.trip_id = trips.id

    INNER JOIN routes
        ON trips.route_id = routes.id

    ORDER BY trips.trip_date ASC,
             trips.trip_time ASC
");


/* GET PAYMENT */

$stmt = $conn->prepare("
    SELECT *
    FROM payments
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    header("Location: view.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Payment - A.M Turai</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet"
href="../css/admin-style.css">

</head>

<body>

<div class="admin-layout">

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


<main class="admin-main">


<div class="admin-topbar">

<div>

<h2>Edit Payment</h2>

<p>Update payment information</p>

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


<div class="form-card">

<h3>

<i class="fa-solid fa-pen"></i>

Edit Payment

</h3>


<form method="POST">


<div class="form-group">

<label>Booking</label>

<select name="booking_id" required>

<?php while ($booking = $bookings->fetch_assoc()): ?>

<option
value="<?php echo $booking["id"]; ?>"
<?php
if ($booking["id"] == $payment["booking_id"])
    echo "selected";
?>
>

<?php echo htmlspecialchars($booking["passenger_name"]); ?>

-

<?php echo htmlspecialchars($booking["departure"]); ?>

→

<?php echo htmlspecialchars($booking["destination"]); ?>

|

Seat
<?php echo htmlspecialchars($booking["seat_number"]); ?>

|

₦<?php echo number_format($booking["fare"], 2); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>Amount (₦)</label>

<input
type="number"
name="amount"
step="0.01"
min="0"
value="<?php echo $payment["amount"]; ?>"
required
>

</div>


<div class="form-group">

<label>Payment Status</label>

<select name="payment_status">

<option value="Pending"
<?php
if ($payment["payment_status"] == "Pending")
    echo "selected";
?>
>
Pending
</option>

<option value="Paid"
<?php
if ($payment["payment_status"] == "Paid")
    echo "selected";
?>
>
Paid
</option>

<option value="Failed"
<?php
if ($payment["payment_status"] == "Failed")
    echo "selected";
?>
>
Failed
</option>

</select>

</div>


<div class="form-actions">

<button
type="submit"
class="btn btn-success"
>

<i class="fa-solid fa-save"></i>

Update Payment

</button>


<a
href="view.php"
class="btn btn-secondary"
>

<i class="fa-solid fa-arrow-left"></i>

Cancel

</a>

</div>


</form>

</div>

</div>


</main>

</div>

</body>

</html>