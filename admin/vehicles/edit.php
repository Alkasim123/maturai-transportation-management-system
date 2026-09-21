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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $vehicle_number = trim($_POST["vehicle_number"]);
    $vehicle_type = trim($_POST["vehicle_type"]);
    $capacity = intval($_POST["capacity"]);
    $status = $_POST["status"];

    $stmt = $conn->prepare("
        UPDATE vehicles
        SET vehicle_number = ?,
            vehicle_type = ?,
            capacity = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssisi",
        $vehicle_number,
        $vehicle_type,
        $capacity,
        $status,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    }

    $message = "Error updating vehicle: " . $conn->error;

    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();

if (!$vehicle) {
    header("Location: view.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Vehicle - A.M Turai</title>

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

<li><a href="../dashboard.php">
<i class="fa-solid fa-gauge"></i> Dashboard
</a></li>

<li><a href="view.php" class="active">
<i class="fa-solid fa-bus"></i> Vehicles
</a></li>

<li><a href="../drivers/view.php">
<i class="fa-solid fa-id-card"></i> Drivers
</a></li>

<li><a href="../passengers/view.php">
<i class="fa-solid fa-users"></i> Passengers
</a></li>

<li><a href="../routes/view.php">
<i class="fa-solid fa-route"></i> Routes
</a></li>

<li><a href="../trips/view.php">
<i class="fa-solid fa-road"></i> Trips
</a></li>

<li><a href="../bookings/view.php">
<i class="fa-solid fa-ticket"></i> Bookings
</a></li>

<li><a href="../payments/view.php">
<i class="fa-solid fa-money-bill-wave"></i> Payments
</a></li>

<li><a href="../reports/index.php">
<i class="fa-solid fa-chart-column"></i> Reports
</a></li>

<li><a href="../../index.php">
<i class="fa-solid fa-globe"></i> View Website
</a></li>

<li><a href="../logout.php">
<i class="fa-solid fa-right-from-bracket"></i> Logout
</a></li>

</ul>
</aside>

<main class="admin-main">

<div class="admin-topbar">

<div>
<h2>Edit Vehicle</h2>
<p>Update vehicle information</p>
</div>

<div class="admin-user">
<i class="fa-solid fa-user-circle"></i>
<?php echo htmlspecialchars($_SESSION["admin_username"]); ?>
</div>

</div>

<div class="page-content">

<?php if ($message != ""): ?>

<div class="alert error">
<?php echo htmlspecialchars($message); ?>
</div>

<?php endif; ?>

<div class="form-card">

<h3>
<i class="fa-solid fa-pen"></i>
Edit Vehicle
</h3>

<form method="POST">

<div class="form-group">
<label>Vehicle Number</label>

<input type="text"
name="vehicle_number"
value="<?php echo htmlspecialchars($vehicle["vehicle_number"]); ?>"
required>
</div>

<div class="form-group">
<label>Vehicle Type</label>

<input type="text"
name="vehicle_type"
value="<?php echo htmlspecialchars($vehicle["vehicle_type"]); ?>"
required>
</div>

<div class="form-group">
<label>Capacity</label>

<input type="number"
name="capacity"
value="<?php echo $vehicle["capacity"]; ?>"
required>
</div>

<div class="form-group">
<label>Status</label>

<select name="status">

<option value="Available"
<?php if ($vehicle["status"] == "Available") echo "selected"; ?>>
Available
</option>

<option value="Assigned"
<?php if ($vehicle["status"] == "Assigned") echo "selected"; ?>>
Assigned
</option>

<option value="Unavailable"
<?php if ($vehicle["status"] == "Unavailable") echo "selected"; ?>>
Unavailable
</option>

</select>
</div>

<div class="form-actions">

<button type="submit" class="btn btn-success">
<i class="fa-solid fa-save"></i>
Update Vehicle
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