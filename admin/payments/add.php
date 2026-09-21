<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$message = "";
$message_type = "";


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
    ORDER BY trips.trip_date ASC, trips.trip_time ASC
");


/* ADD PAYMENT */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $booking_id = intval($_POST["booking_id"]);
    $amount = floatval($_POST["amount"]);
    $payment_status = $_POST["payment_status"];

    if ($booking_id <= 0 || $amount <= 0) {

        $message = "Please enter a valid booking and payment amount.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO payments
            (booking_id, amount, payment_status)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "ids",
            $booking_id,
            $amount,
            $payment_status
        );

        if ($stmt->execute()) {

            $message = "Payment added successfully.";
            $message_type = "success";

        } else {

            $message = "Error adding payment: " . $conn->error;
            $message_type = "error";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Payment - A.M Turai</title>

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

                <h2>Add Payment</h2>

                <p>Record a customer payment</p>

            </div>

            <div class="admin-user">

                <i class="fa-solid fa-user-circle"></i>

                <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>

            </div>

        </div>


        <div class="page-content">


            <?php if ($message != ""): ?>

                <div class="alert <?php echo $message_type; ?>">

                    <i class="fa-solid
                    <?php
                    echo ($message_type == "success")
                        ? "fa-circle-check"
                        : "fa-circle-exclamation";
                    ?>"></i>

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <div class="form-card">

                <div class="form-card-header">

                    <h3>
                        <i class="fa-solid fa-money-bill-wave"></i>
                        Payment Information
                    </h3>

                </div>


                <form method="POST">


                    <div class="form-group">

                        <label>
                            Booking
                        </label>

                        <select
                            name="booking_id"
                            required
                        >

                            <option value="">
                                -- Select Booking --
                            </option>

                            <?php while ($booking = $bookings->fetch_assoc()): ?>

                                <option
                                    value="<?php echo $booking["id"]; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $booking["passenger_name"]
                                    );
                                    ?>

                                    -
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["departure"]
                                    );
                                    ?>

                                    →
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["destination"]
                                    );
                                    ?>

                                    |
                                    Seat
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["seat_number"]
                                    );
                                    ?>

                                    |
                                    ₦
                                    <?php
                                    echo number_format(
                                        $booking["fare"],
                                        2
                                    );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Amount (₦)
                        </label>

                        <input
                            type="number"
                            name="amount"
                            step="0.01"
                            min="0"
                            placeholder="Enter payment amount"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Payment Status
                        </label>

                        <select
                            name="payment_status"
                            required
                        >

                            <option value="Pending">
                                Pending
                            </option>

                            <option value="Paid">
                                Paid
                            </option>

                            <option value="Failed">
                                Failed
                            </option>

                        </select>

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >

                            <i class="fa-solid fa-check"></i>

                            Save Payment

                        </button>


                        <a
                            href="view.php"
                            class="btn btn-secondary"
                        >

                            <i class="fa-solid fa-arrow-left"></i>

                            Back

                        </a>

                    </div>

                </form>

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