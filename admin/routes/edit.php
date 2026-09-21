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

    $departure = trim($_POST["departure"]);
    $destination = trim($_POST["destination"]);
    $fare = floatval($_POST["fare"]);

    $stmt = $conn->prepare("
        UPDATE routes
        SET departure = ?,
            destination = ?,
            fare = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssdi",
        $departure,
        $destination,
        $fare,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    }

    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM routes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$route = $stmt->get_result()->fetch_assoc();

if (!$route) {
    header("Location: view.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Route - A.M Turai</title>

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
<li><a href="view.php" class="active"><i class="fa-solid fa-route"></i> Routes</a></li>
<li><a href="../trips/view.php"><i class="fa-solid fa-road"></i> Trips</a></li>
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
<h2>Edit Route</h2>
<p>Update route information</p>
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
Edit Route
</h3>

<form method="POST">

<div class="form-group">
<label>Departure</label>

<input type="text"
name="departure"
value="<?php echo htmlspecialchars($route["departure"]); ?>"
required>
</div>

<div class="form-group">
<label>Destination</label>

<input type="text"
name="destination"
value="<?php echo htmlspecialchars($route["destination"]); ?>"
required>
</div>

<div class="form-group">
<label>Fare (₦)</label>

<input type="number"
name="fare"
step="0.01"
value="<?php echo $route["fare"]; ?>"
required>
</div>

<div class="form-actions">

<button type="submit" class="btn btn-success">
<i class="fa-solid fa-save"></i>
Update Route
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