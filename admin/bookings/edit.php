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


/* UPDATE BOOKING */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $passenger_id = intval($_POST["passenger_id"]);
    $trip_id = intval($_POST["trip_id"]);
    $seat_number = trim($_POST["seat_number"]);

    $stmt = $conn->prepare("
        UPDATE bookings
        SET passenger_id = ?,
            trip_id = ?,
            seat_number = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "iisi",
        $passenger_id,
        $trip_id,
        $seat_number,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    }

    $stmt->close();
}


/* PASSENGERS */

$passengers = $conn->query("
    SELECT *
    FROM passengers
    ORDER BY name ASC
");


/* TRIPS */

$trips = $conn->query("
    SELECT
        trips.id,
        trips.trip_date,
        trips.trip_time,
        routes.departure,
        routes.destination,
        vehicles.vehicle_number
    FROM trips

    INNER JOIN routes
        ON trips.route_id = routes.id

    INNER JOIN vehicles
        ON trips.vehicle_id = vehicles.id

    ORDER BY trips.trip_date ASC,
             trips.trip_time ASC
");


/* BOOKING */

$stmt = $conn->prepare("
    SELECT *
    FROM bookings
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: view.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Booking - A.M Turai</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="../css/admin-style.css">

</head>

<body>

<div class="admin-layout">

<aside class="sidebar">

<div class="sidebar-logo">
<i class="fa-solid fa-bus"></i>
<span>A.M TURAI</span>
</div>

<ul class="sidebar-menu">

<li><a href="../dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
<li><a href="../vehicles/view.php"><i class="fa-solid fa-bus"></i> Vehicles</a></li>
<li><a href="../drivers/view.php"><i class="fa-solid fa-id-card"></i> Drivers</a></li>
<li><a href="../passengers/view.php"><i class="fa-solid fa-users"></i> Passengers</a></li>
<li><a href="../routes/view.php"><i class="fa-solid fa-route"></i> Routes</a></li>
<li><a href="../trips/view.php"><i class="fa-solid fa-road"></i> Trips</a></li>
<li><a href="view.php" class="active"><i class="fa-solid fa-ticket"></i> Bookings</a></li>
<li><a href="../payments/view.php"><i class="fa-solid fa-money-bill-wave"></i> Payments</a></li>
<li><a href="../reports/index.php"><i class="fa-solid fa-chart-column"></i> Reports</a></li>
<li><a href="../../index.php"><i class="fa-solid fa-globe"></i> View Website</a></li>
<li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>

</ul>

</aside>

<main class="admin-main">

<div class="admin-topbar">

<div>
<h2>Edit Booking</h2>
<p>Update passenger booking</p>
</div>

<div class="admin-user">
<i class="fa-solid fa-user-circle"></i>
<?php echo htmlspecialchars($_SESSION["admin_username"]); ?>
</div>

</div>

<div class="page-content">

<div class="form-card">

<h3>
<i class="fa-solid fa-pen"></i>
Edit Booking
</h3>

<form method="POST">

<div class="form-group">

<label>Passenger</label>

<select name="passenger_id" required>

<?php while ($passenger = $passengers->fetch_assoc()): ?>

<option
value="<?php echo $passenger["id"]; ?>"
<?php
if ($passenger["id"] == $booking["passenger_id"])
    echo "selected";
?>
>

<?php echo htmlspecialchars($passenger["name"]); ?>

-

<?php echo htmlspecialchars($passenger["phone"]); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>Trip</label>

<select name="trip_id" required>

<?php while ($trip = $trips->fetch_assoc()): ?>

<option
value="<?php echo $trip["id"]; ?>"
<?php
if ($trip["id"] == $booking["trip_id"])
    echo "selected";
?>
>

<?php echo htmlspecialchars($trip["departure"]); ?>

→

<?php echo htmlspecialchars($trip["destination"]); ?>

|

<?php echo date("d M Y", strtotime($trip["trip_date"])); ?>

|

<?php echo date("h:i A", strtotime($trip["trip_time"])); ?>

|

<?php echo htmlspecialchars($trip["vehicle_number"]); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>Seat Number</label>

<input
type="text"
name="seat_number"
value="<?php echo htmlspecialchars($booking["seat_number"]); ?>"
required
>

</div>


<div class="form-actions">

<button type="submit" class="btn btn-success">

<i class="fa-solid fa-save"></i>

Update Booking

</button>

<a href="view.php" class="btn btn-secondary">

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