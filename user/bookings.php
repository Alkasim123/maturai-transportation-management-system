<?php

session_start();

include "..database.php";


// ==================================================
// PROTECT PAGE
// ==================================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];

$user_email = $_SESSION["user_email"];

$message = "";
$message_type = "";


// ==================================================
// GET CUSTOMER INFORMATION
// ==================================================

$stmt = $conn->prepare(
    "SELECT
        id,
        full_name,
        username,
        email,
        phone,
        address
     FROM customer_users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$user_result = $stmt->get_result();

if ($user_result->num_rows != 1) {

    session_destroy();

    header("Location: login.php");
    exit();

}

$user = $user_result->fetch_assoc();

$stmt->close();


// ==================================================
// FIND OR CREATE PASSENGER RECORD
// ==================================================

$passenger_id = 0;

$stmt = $conn->prepare(
    "SELECT id
     FROM passengers
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $user["email"]);

$stmt->execute();

$passenger_result = $stmt->get_result();

if ($passenger_result->num_rows > 0) {

    $passenger = $passenger_result->fetch_assoc();

    $passenger_id = $passenger["id"];

} else {

    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO passengers
        (name, phone, email, address)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssss",
        $user["full_name"],
        $user["phone"],
        $user["email"],
        $user["address"]
    );

    $stmt->execute();

    $passenger_id = $stmt->insert_id;
}

$stmt->close();


// ==================================================
// CANCEL BOOKING
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    &&
    isset($_POST["cancel_booking"])
) {

    $booking_id = intval($_POST["booking_id"]);


    // Make sure this booking belongs to this passenger
    $check = $conn->prepare(
        "SELECT id
         FROM bookings
         WHERE id = ?
         AND passenger_id = ?"
    );

    $check->bind_param(
        "ii",
        $booking_id,
        $passenger_id
    );

    $check->execute();

    $check_result = $check->get_result();


    if ($check_result->num_rows == 1) {

        // Delete related payments first
        $delete_payment = $conn->prepare(
            "DELETE FROM payments
             WHERE booking_id = ?"
        );

        $delete_payment->bind_param(
            "i",
            $booking_id
        );

        $delete_payment->execute();

        $delete_payment->close();


        // Delete booking
        $delete = $conn->prepare(
            "DELETE FROM bookings
             WHERE id = ?
             AND passenger_id = ?"
        );

        $delete->bind_param(
            "ii",
            $booking_id,
            $passenger_id
        );


        if ($delete->execute()) {

            $message =
                "Booking cancelled successfully.";

            $message_type = "success";

        } else {

            $message =
                "Unable to cancel booking.";

            $message_type = "error";
        }

        $delete->close();

    } else {

        $message =
            "Booking not found.";

        $message_type = "error";
    }

    $check->close();
}


// ==================================================
// CREATE BOOKING
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    &&
    isset($_POST["book_trip"])
) {

    $trip_id = intval($_POST["trip_id"]);

    $seat_number = trim($_POST["seat_number"]);


    if ($trip_id <= 0 || empty($seat_number)) {

        $message =
            "Please select a trip and enter a seat number.";

        $message_type = "error";

    } else {

        // ------------------------------------------
        // CHECK TRIP
        // ------------------------------------------

        $trip_check = $conn->prepare(
            "SELECT
                trips.id,
                trips.status,
                vehicles.capacity
             FROM trips
             INNER JOIN vehicles
                ON trips.vehicle_id = vehicles.id
             WHERE trips.id = ?
             LIMIT 1"
        );

        $trip_check->bind_param(
            "i",
            $trip_id
        );

        $trip_check->execute();

        $trip_result =
            $trip_check->get_result();


        if ($trip_result->num_rows != 1) {

            $message = "Selected trip does not exist.";
            $message_type = "error";

        } else {

            $trip = $trip_result->fetch_assoc();


            // Check trip status
            if ($trip["status"] != "Scheduled") {

                $message =
                    "This trip is not currently available for booking.";

                $message_type = "error";

            } else {

                // ------------------------------------------
                // CHECK SEAT NUMBER
                // ------------------------------------------

                $seat_number_upper =
                    strtoupper($seat_number);


                // Check if seat already booked
                $seat_check = $conn->prepare(
                    "SELECT id
                     FROM bookings
                     WHERE trip_id = ?
                     AND UPPER(seat_number) = ?
                     LIMIT 1"
                );

                $seat_check->bind_param(
                    "is",
                    $trip_id,
                    $seat_number_upper
                );

                $seat_check->execute();

                $seat_result =
                    $seat_check->get_result();


                if ($seat_result->num_rows > 0) {

                    $message =
                        "Seat " .
                        htmlspecialchars($seat_number_upper) .
                        " is already booked for this trip.";

                    $message_type = "error";

                } else {

                    // ------------------------------------------
                    // CHECK SEAT RANGE
                    // ------------------------------------------

                    $seat_number_numeric =
                        intval(
                            preg_replace(
                                "/[^0-9]/",
                                "",
                                $seat_number_upper
                            )
                        );


                    if (
                        $seat_number_numeric <= 0
                        ||
                        $seat_number_numeric >
                        intval($trip["capacity"])
                    ) {

                        $message =
                            "Invalid seat number. This vehicle has " .
                            intval($trip["capacity"]) .
                            " seats.";

                        $message_type = "error";

                    } else {

                        // ------------------------------------------
                        // INSERT BOOKING
                        // ------------------------------------------

                        $insert = $conn->prepare(
                            "INSERT INTO bookings
                            (passenger_id, trip_id, seat_number)
                            VALUES (?, ?, ?)"
                        );

                        $insert->bind_param(
                            "iis",
                            $passenger_id,
                            $trip_id,
                            $seat_number_upper
                        );


                        if ($insert->execute()) {

                            $message =
                                "Trip booked successfully! Your seat is " .
                                htmlspecialchars($seat_number_upper) .
                                ".";

                            $message_type = "success";

                        } else {

                            $message =
                                "Unable to complete booking.";

                            $message_type = "error";
                        }

                        $insert->close();
                    }
                }

                $seat_check->close();
            }
        }

        $trip_check->close();
    }
}


// ==================================================
// GET AVAILABLE TRIPS
// ==================================================

$available_trips = $conn->query(
    "SELECT
        trips.id,
        trips.trip_date,
        trips.trip_time,
        trips.status,
        routes.departure,
        routes.destination,
        routes.fare,
        vehicles.vehicle_number,
        vehicles.vehicle_type,
        vehicles.capacity,

        (
            SELECT COUNT(*)
            FROM bookings
            WHERE bookings.trip_id = trips.id
        ) AS booked_seats

     FROM trips

     INNER JOIN routes
        ON trips.route_id = routes.id

     INNER JOIN vehicles
        ON trips.vehicle_id = vehicles.id

     WHERE trips.status = 'Scheduled'

     ORDER BY
        trips.trip_date ASC,
        trips.trip_time ASC"
);


// ==================================================
// GET USER BOOKINGS
// ==================================================

$my_bookings = $conn->prepare(
    "SELECT
        bookings.id,
        bookings.seat_number,
        bookings.booking_date,

        routes.departure,
        routes.destination,
        routes.fare,

        trips.trip_date,
        trips.trip_time,
        trips.status,

        vehicles.vehicle_number

     FROM bookings

     INNER JOIN trips
        ON bookings.trip_id = trips.id

     INNER JOIN routes
        ON trips.route_id = routes.id

     INNER JOIN vehicles
        ON trips.vehicle_id = vehicles.id

     WHERE bookings.passenger_id = ?

     ORDER BY bookings.id DESC"
);

$my_bookings->bind_param(
    "i",
    $passenger_id
);

$my_bookings->execute();

$my_bookings_result =
    $my_bookings->get_result();

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
        My Bookings - A.M Turai Travel & Tours
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

            border-bottom:
                1px solid rgba(255,255,255,0.15);
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


        .page-intro {

            margin-bottom: 25px;
        }


        .page-intro h2 {

            color: #004d40;

            font-size: 24px;

            margin-bottom: 7px;
        }


        .page-intro p {

            color: #777;

            font-size: 14px;
        }


        /* MESSAGE */

        .message {

            padding: 14px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        .message.success {

            background: #dff5e8;

            color: #176b3a;
        }


        .message.error {

            background: #fde2e2;

            color: #a52828;
        }


        /* CARD */

        .card {

            background: white;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);

            margin-bottom: 25px;
        }


        .card-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;

            padding-bottom: 15px;

            border-bottom: 1px solid #eee;
        }


        .card-header h3 {

            color: #004d40;

            font-size: 19px;
        }


        .card-header i {

            color: #00695c;

            margin-right: 7px;
        }


        /* TRIP GRID */

        .trip-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        .trip-card {

            border: 1px solid #e5e5e5;

            border-radius: 12px;

            padding: 20px;

            transition: 0.3s;

            background: #fff;
        }


        .trip-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 8px 20px rgba(0,0,0,0.08);

            border-color: #00695c;
        }


        .route {

            color: #004d40;

            font-weight: bold;

            font-size: 16px;

            margin-bottom: 15px;
        }


        .route i {

            color: #00695c;

            margin: 0 5px;
        }


        .trip-detail {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 10px;

            color: #666;

            font-size: 13px;
        }


        .trip-detail i {

            color: #00695c;

            width: 17px;

            text-align: center;
        }


        .fare {

            margin-top: 15px;

            padding-top: 15px;

            border-top: 1px solid #eee;

            display: flex;

            align-items: center;

            justify-content: space-between;
        }


        .fare strong {

            color: #00695c;

            font-size: 17px;
        }


        .seats {

            font-size: 11px;

            color: #777;
        }


        .book-button {

            width: 100%;

            margin-top: 15px;

            border: none;

            padding: 11px;

            background: #00695c;

            color: white;

            border-radius: 7px;

            cursor: pointer;

            font-weight: bold;

            font-size: 13px;
        }


        .book-button:hover {

            background: #004d40;
        }


        /* BOOK FORM */

        .book-form {

            margin-top: 15px;

            padding-top: 15px;

            border-top: 1px solid #eee;

            display: none;
        }


        .book-form.show {

            display: block;
        }


        .book-form label {

            display: block;

            font-size: 12px;

            font-weight: bold;

            margin-bottom: 6px;

            color: #444;
        }


        .book-form input {

            width: 100%;

            padding: 10px;

            border: 1px solid #ddd;

            border-radius: 7px;

            outline: none;

            font-size: 13px;
        }


        .book-form input:focus {

            border-color: #00695c;
        }


        .confirm-button {

            width: 100%;

            border: none;

            padding: 10px;

            background: #00897b;

            color: white;

            border-radius: 7px;

            cursor: pointer;

            font-weight: bold;

            margin-top: 10px;
        }


        .confirm-button:hover {

            background: #00695c;
        }


        /* TABLE */

        .table-container {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }


        th {

            background: #004d40;

            color: white;

            padding: 13px;

            text-align: left;

            font-size: 12px;
        }


        td {

            padding: 13px;

            border-bottom: 1px solid #eee;

            font-size: 12px;
        }


        tr:hover td {

            background: #f8faf9;
        }


        .status {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: bold;
        }


        .status-scheduled {

            background: #dff5e8;

            color: #176b3a;
        }


        .status-other {

            background: #eee;

            color: #555;
        }


        .cancel-button {

            border: none;

            background: #c62828;

            color: white;

            padding: 7px 10px;

            border-radius: 6px;

            cursor: pointer;

            font-size: 11px;
        }


        .cancel-button:hover {

            background: #a51f1f;
        }


        .empty {

            text-align: center;

            padding: 35px;

            color: #777;
        }


        .empty i {

            font-size: 35px;

            color: #aaa;

            margin-bottom: 10px;
        }


        /* MOBILE */

        @media (max-width: 1100px) {

            .trip-grid {

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
            .menu span,
            .logout span {

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


            .logout a {

                justify-content: center;

                padding: 14px 5px;
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
        }


        @media (max-width: 550px) {

            .trip-grid {

                grid-template-columns: 1fr;
            }
        }

    </style>

</head>


<body>


<!-- ========================================== -->
<!-- SIDEBAR -->
<!-- ========================================== -->

<div class="sidebar">

    <div class="logo">

        <i class="fas fa-bus"></i>

        <h2>A.M TURAI</h2>

        <p>Travel & Tours</p>

    </div>


    <div class="menu">

        <a href="dashboard.php">

            <i class="fas fa-gauge"></i>

            <span>Dashboard</span>

        </a>


        <a href="profile.php">

            <i class="fas fa-user"></i>

            <span>My Profile</span>

        </a>


        <a href="bookings.php" class="active">

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


<!-- ========================================== -->
<!-- MAIN -->
<!-- ========================================== -->

<div class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <h1>My Bookings</h1>


        <div class="user-info">

            <div class="user-icon">

                <i class="fas fa-user"></i>

            </div>

            <span>

                <?php
                echo htmlspecialchars(
                    $user["full_name"]
                );
                ?>

            </span>

        </div>

    </div>


    <!-- CONTENT -->

    <div class="content">


        <div class="page-intro">

            <h2>

                <i class="fas fa-ticket"></i>

                Book Your Trip

            </h2>

            <p>

                Select an available trip and choose your seat.

            </p>

        </div>


        <!-- MESSAGE -->

        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- ====================================== -->
        <!-- AVAILABLE TRIPS -->
        <!-- ====================================== -->

        <div class="card">

            <div class="card-header">

                <h3>

                    <i class="fas fa-route"></i>

                    Available Trips

                </h3>

            </div>


            <div class="trip-grid">


            <?php

            if (
                $available_trips &&
                $available_trips->num_rows > 0
            ):

                while (
                    $trip =
                    $available_trips->fetch_assoc()
                ):

                    $remaining_seats =
                        intval($trip["capacity"])
                        -
                        intval($trip["booked_seats"]);

            ?>


                <div class="trip-card">


                    <div class="route">

                        <?php
                        echo htmlspecialchars(
                            $trip["departure"]
                        );
                        ?>

                        <i class="fas fa-arrow-right"></i>

                        <?php
                        echo htmlspecialchars(
                            $trip["destination"]
                        );
                        ?>

                    </div>


                    <div class="trip-detail">

                        <i class="fas fa-calendar"></i>

                        <?php
                        echo date(
                            "d M Y",
                            strtotime(
                                $trip["trip_date"]
                            )
                        );
                        ?>

                    </div>


                    <div class="trip-detail">

                        <i class="fas fa-clock"></i>

                        <?php
                        echo date(
                            "h:i A",
                            strtotime(
                                $trip["trip_time"]
                            )
                        );
                        ?>

                    </div>


                    <div class="trip-detail">

                        <i class="fas fa-bus"></i>

                        <?php
                        echo htmlspecialchars(
                            $trip["vehicle_number"]
                        );
                        ?>

                        -

                        <?php
                        echo htmlspecialchars(
                            $trip["vehicle_type"]
                        );
                        ?>

                    </div>


                    <div class="trip-detail">

                        <i class="fas fa-chair"></i>

                        <?php
                        echo $remaining_seats;
                        ?>

                        seats remaining

                    </div>


                    <div class="fare">

                        <strong>

                            ₦<?php
                            echo number_format(
                                $trip["fare"],
                                2
                            );
                            ?>

                        </strong>


                        <span class="seats">

                            <?php
                            echo $trip["booked_seats"];
                            ?>
                            /
                            <?php
                            echo $trip["capacity"];
                            ?>

                            booked

                        </span>

                    </div>


                    <?php if ($remaining_seats > 0): ?>


                        <button
                            type="button"
                            class="book-button"
                            onclick="showBookingForm(
                                <?php echo $trip['id']; ?>
                            )"
                        >

                            <i class="fas fa-ticket"></i>

                            Book This Trip

                        </button>


                        <form
                            method="POST"
                            class="book-form"
                            id="form-<?php
                            echo $trip['id'];
                            ?>"
                        >

                            <input
                                type="hidden"
                                name="trip_id"
                                value="<?php
                                echo $trip['id'];
                                ?>"
                            >


                            <label>

                                Seat Number

                            </label>


                            <input
                                type="text"
                                name="seat_number"
                                placeholder="Example: 1"
                                required
                                maxlength="10"
                            >


                            <button
                                type="submit"
                                name="book_trip"
                                class="confirm-button"
                            >

                                <i class="fas fa-check"></i>

                                Confirm Booking

                            </button>

                        </form>


                    <?php else: ?>


                        <button
                            type="button"
                            class="book-button"
                            disabled
                            style="
                                background:#aaa;
                                cursor:not-allowed;
                            "
                        >

                            Fully Booked

                        </button>


                    <?php endif; ?>


                </div>


            <?php

                endwhile;

            else:

            ?>


                <div class="empty"
                     style="grid-column:1/-1;">

                    <i class="fas fa-bus-slash"></i>

                    <br>

                    No scheduled trips are available.

                    <br><br>

                    Please check again later.

                </div>


            <?php endif; ?>


            </div>

        </div>


        <!-- ====================================== -->
        <!-- MY BOOKINGS -->
        <!-- ====================================== -->

        <div class="card">

            <div class="card-header">

                <h3>

                    <i class="fas fa-list-check"></i>

                    My Bookings

                </h3>

            </div>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>Route</th>

                            <th>Date</th>

                            <th>Time</th>

                            <th>Vehicle</th>

                            <th>Seat</th>

                            <th>Fare</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        $my_bookings_result->num_rows > 0
                    ):

                        while (
                            $booking =
                            $my_bookings_result->fetch_assoc()
                        ):

                    ?>


                        <tr>


                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $booking["departure"]
                                    );
                                    ?>

                                </strong>

                                →

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $booking["destination"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $booking["trip_date"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $booking["trip_time"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $booking["vehicle_number"]
                                );
                                ?>

                            </td>


                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $booking["seat_number"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <td>

                                ₦<?php
                                echo number_format(
                                    $booking["fare"],
                                    2
                                );
                                ?>

                            </td>


                            <td>


                                <?php

                                if (
                                    $booking["status"]
                                    == "Scheduled"
                                ):

                                ?>

                                    <span
                                        class="status status-scheduled"
                                    >

                                        Scheduled

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="status status-other"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $booking["status"]
                                        );
                                        ?>

                                    </span>

                                <?php endif; ?>


                            </td>


                            <td>

                                <form
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to cancel this booking?'
                                        );
                                    "
                                >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?php
                                        echo $booking['id'];
                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="cancel_booking"
                                        class="cancel-button"
                                    >

                                        <i class="fas fa-trash"></i>

                                        Cancel

                                    </button>

                                </form>

                            </td>


                        </tr>


                    <?php

                        endwhile;

                    else:

                    ?>


                        <tr>

                            <td
                                colspan="8"
                                class="empty"
                            >

                                <i class="fas fa-ticket"></i>

                                <br><br>

                                You have no bookings yet.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>


    </div>

</div>


<script>

function showBookingForm(tripId) {

    const form =
        document.getElementById(
            "form-" + tripId
        );


    if (form.classList.contains("show")) {

        form.classList.remove("show");

    } else {

        form.classList.add("show");

    }

}

</script>


</body>

</html>