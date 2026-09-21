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

    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);

    $stmt = $conn->prepare("
        UPDATE passengers
        SET name = ?,
            phone = ?,
            email = ?,
            address = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssi",
        $name,
        $phone,
        $email,
        $address,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    }

    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM passengers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$passenger = $stmt->get_result()->fetch_assoc();

if (!$passenger) {
    header("Location: view.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Passenger - A.M Turai</title>

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
<li><a href="view.php" class="active"><i class="fa-solid fa-users"></i> Passengers</a></li>
<li><a href="../routes/view.php"><i class="fa-solid fa-route"></i> Routes</a></li>
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
<h2>Edit Passenger</h2>
<p>Update passenger information</p>
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
Edit Passenger
</h3>

<form method="POST">

<div class="form-group">
<label>Name</label>

<input type="text"
name="name"
value="<?php echo htmlspecialchars($passenger["name"]); ?>"
required>
</div>

<div class="form-group">
<label>Phone</label>

<input type="text"
name="phone"
value="<?php echo htmlspecialchars($passenger["phone"]); ?>"
required>
</div>

<div class="form-group">
<label>Email</label>

<input type="email"
name="email"
value="<?php echo htmlspecialchars($passenger["email"]); ?>">
</div>

<div class="form-group">
<label>Address</label>

<textarea name="address"><?php echo htmlspecialchars($passenger["address"]); ?></textarea>

</div>

<div class="form-actions">

<button type="submit" class="btn btn-success">
<i class="fa-solid fa-save"></i>
Update Passenger
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