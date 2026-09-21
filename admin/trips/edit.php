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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $route_id = intval($_POST["route_id"]);
    $vehicle_id = intval($_POST["vehicle_id"]);
    $driver_id = intval($_POST["driver_id"]);
    $trip_date = $_POST["trip_date"];
    $trip_time = $_POST["trip_time"];
    $status = $_POST["status"];

    $stmt = $conn->prepare("
        UPDATE trips
        SET route_id = ?,
            vehicle_id = ?,
            driver_id = ?,
            trip_date = ?,
            trip_time = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "iiisssi",
        $route_id,
        $vehicle_id,
        $driver_id,
        $trip_date,
        $trip_time,
        $status,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    }

    $stmt->close();
}


/* GET ROUTES */

$routes = $conn->query("
    SELECT *
    FROM routes
    ORDER BY departure ASC
");


/* GET VEHICLES */

$vehicles = $conn->query("
    SELECT *
    FROM vehicles
    ORDER BY vehicle_number ASC
");


/* GET DRIVERS */

$drivers = $conn->query("
    SELECT *
    FROM drivers
    ORDER BY name ASC
");


/* GET TRIP */

$stmt = $conn->prepare("SELECT * FROM trips WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$trip = $stmt->get_result()->fetch_assoc();

if (!$trip) {
    header("Location: view.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Trip - A.M Turai</title>

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
<li><a href="view.php" class="active"><i class="fa-solid fa-road"></i> Trips</a></li>
<li><a href="../bookings/view.php"><i class="fa-solid fa-ticket"></i> Bookings</a></li>
<li><a href="../payments/view.php"><i class="fa-solid fa-money-bill-wave"></i> Payments</a></li>
<li><a href="../reports/index.php"><i class="fa-solid fa-chart-column"></i> Reports</a></li>
<li><a href="../../index.php"><i class="fa-solid fa-globe"></i> View Website</a></li>
<li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>

</ul>

</aside>

<main class="admin-main">

<div class="admin-topbar">

<div>
<h2>Edit Trip</h2>
<p>Update trip information</p>
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
Edit Trip
</h3>

<form method="POST">

<div class="form-group">

<label>Route</label>

<select name="route_id" required>

<?php while ($route = $routes->fetch_assoc()): ?>

<option
value="<?php echo $route["id"]; ?>"
<?php if ($route["id"] == $trip["route_id"]) echo "selected"; ?>
>

<?php echo htmlspecialchars($route["departure"]); ?>

→

<?php echo htmlspecialchars($route["destination"]); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>Vehicle</label>

<select name="vehicle_id" required>

<?php while ($vehicle = $vehicles->fetch_assoc()): ?>

<option
value="<?php echo $vehicle["id"]; ?>"
<?php if ($vehicle["id"] == $trip["vehicle_id"]) echo "selected"; ?>
>

<?php echo htmlspecialchars($vehicle["vehicle_number"]); ?>

-

<?php echo htmlspecialchars($vehicle["vehicle_type"]); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>Driver</label>

<select name="driver_id" required>

<?php while ($driver = $drivers->fetch_assoc()): ?>

<option
value="<?php echo $driver["id"]; ?>"
<?php if ($driver["id"] == $trip["driver_id"]) echo "selected"; ?>
>

<?php echo htmlspecialchars($driver["name"]); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>Trip Date</label>

<input type="date"
name="trip_date"
value="<?php echo $trip["trip_date"]; ?>"
required>

</div>


<div class="form-group">

<label>Trip Time</label>

<input type="time"
name="trip_time"
value="<?php echo $trip["trip_time"]; ?>"
required>

</div>


<div class="form-group">

<label>Status</label>

<select name="status">

<option value="Scheduled"
<?php if ($trip["status"] == "Scheduled") echo "selected"; ?>>
Scheduled
</option>

<option value="Completed"
<?php if ($trip["status"] == "Completed") echo "selected"; ?>>
Completed
</option>

<option value="Cancelled"
<?php if ($trip["status"] == "Cancelled") echo "selected"; ?>>
Cancelled
</option>

</select>

</div>


<div class="form-actions">

<button type="submit" class="btn btn-success">

<i class="fa-solid fa-save"></i>

Update Trip

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