<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| GET PASSENGERS
|--------------------------------------------------------------------------
*/

$passengers = $conn->query("
    SELECT *
    FROM passengers
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| GET TRIPS
|--------------------------------------------------------------------------
*/

$trips = $conn->query("
    SELECT
        trips.id,
        trips.trip_date,
        trips.trip_time,
        trips.status,

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


/*
|--------------------------------------------------------------------------
| ADD BOOKING
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $passenger_id = $_POST["passenger_id"];
    $trip_id = $_POST["trip_id"];
    $seat_number = trim($_POST["seat_number"]);


    if (
        empty($passenger_id) ||
        empty($trip_id) ||
        $seat_number == ""
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "danger";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CHECK WHETHER SEAT IS ALREADY BOOKED
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare("
            SELECT id
            FROM bookings
            WHERE trip_id = ?
            AND seat_number = ?
        ");

        $check->bind_param(
            "is",
            $trip_id,
            $seat_number
        );

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $message =
                "This seat is already booked for the selected trip.";

            $messageType = "danger";

        } else {


            /*
            |--------------------------------------------------------------------------
            | ADD BOOKING
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                INSERT INTO bookings
                (
                    passenger_id,
                    trip_id,
                    seat_number
                )
                VALUES (?, ?, ?)
            ");

            $stmt->bind_param(
                "iis",
                $passenger_id,
                $trip_id,
                $seat_number
            );


            if ($stmt->execute()) {

                header("Location: view.php");
                exit();

            } else {

                $message =
                    "Error adding booking: " .
                    $conn->error;

                $messageType = "danger";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Booking - A.M Turai Admin</title>

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

            <div>

                <strong>A.M TURAI</strong>

                <small>ADMIN PANEL</small>

            </div>

        </div>


        <nav class="sidebar-menu">

            <a href="../dashboard.php">

                <i class="fa-solid fa-gauge"></i>

                <span>Dashboard</span>

            </a>


            <a href="../vehicles/view.php">

                <i class="fa-solid fa-bus"></i>

                <span>Vehicles</span>

            </a>


            <a href="../drivers/view.php">

                <i class="fa-solid fa-id-card"></i>

                <span>Drivers</span>

            </a>


            <a href="../passengers/view.php">

                <i class="fa-solid fa-users"></i>

                <span>Passengers</span>

            </a>


            <a href="../routes/view.php">

                <i class="fa-solid fa-route"></i>

                <span>Routes</span>

            </a>


            <a href="../trips/view.php">

                <i class="fa-solid fa-road"></i>

                <span>Trips</span>

            </a>


            <a href="view.php"
               class="active">

                <i class="fa-solid fa-ticket"></i>

                <span>Bookings</span>

            </a>


            <a href="../payments/view.php">

                <i class="fa-solid fa-money-bill"></i>

                <span>Payments</span>

            </a>


            <a href="../reports/index.php">

                <i class="fa-solid fa-chart-column"></i>

                <span>Reports</span>

            </a>


            <a href="../../index.php">

                <i class="fa-solid fa-globe"></i>

                <span>View Website</span>

            </a>


            <a href="../logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </nav>

    </aside>


    <!-- MAIN -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <h2>Add Booking</h2>

                <p>Create a passenger trip booking</p>

            </div>


            <div class="admin-user">

                <i class="fa-solid fa-circle-user"></i>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["admin_username"]
                    );

                    ?>

                </span>

            </div>

        </header>


        <!-- PAGE -->

        <section class="page-content">


            <div class="page-title">

                <div>

                    <h1>

                        <i class="fa-solid fa-ticket"></i>

                        Add Booking

                    </h1>

                    <p>
                        Select a passenger, trip and seat number.
                    </p>

                </div>


                <a href="view.php"
                   class="btn btn-secondary">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back to Bookings

                </a>

            </div>


            <?php if ($message != ""): ?>

                <div class="alert alert-<?php echo $messageType; ?>">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <div class="form-card">

                <form method="POST">


                    <!-- PASSENGER -->

                    <div class="form-group">

                        <label for="passenger_id">

                            <i class="fa-solid fa-user"></i>

                            Passenger

                        </label>


                        <select
                            id="passenger_id"
                            name="passenger_id"
                            required
                        >

                            <option value="">
                                -- Select Passenger --
                            </option>


                            <?php if ($passengers && $passengers->num_rows > 0): ?>

                                <?php while ($passenger = $passengers->fetch_assoc()): ?>

                                    <option
                                        value="<?php echo $passenger["id"]; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $passenger["name"]
                                        );
                                        ?>

                                        -

                                        <?php
                                        echo htmlspecialchars(
                                            $passenger["phone"]
                                        );
                                        ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <!-- TRIP -->

                    <div class="form-group">

                        <label for="trip_id">

                            <i class="fa-solid fa-road"></i>

                            Trip

                        </label>


                        <select
                            id="trip_id"
                            name="trip_id"
                            required
                        >

                            <option value="">
                                -- Select Trip --
                            </option>


                            <?php if ($trips && $trips->num_rows > 0): ?>

                                <?php while ($trip = $trips->fetch_assoc()): ?>

                                    <option
                                        value="<?php echo $trip["id"]; ?>"
                                    >

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

                                        |

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $trip["trip_date"]
                                            )
                                        );
                                        ?>

                                        |

                                        <?php
                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $trip["trip_time"]
                                            )
                                        );
                                        ?>

                                        |

                                        <?php
                                        echo htmlspecialchars(
                                            $trip["vehicle_number"]
                                        );
                                        ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <!-- SEAT -->

                    <div class="form-group">

                        <label for="seat_number">

                            <i class="fa-solid fa-chair"></i>

                            Seat Number

                        </label>


                        <input
                            type="text"
                            id="seat_number"
                            name="seat_number"
                            placeholder="Example: 1, 2, A1"
                            required
                        >

                    </div>


                    <!-- BUTTONS -->

                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Booking

                        </button>


                        <a
                            href="view.php"
                            class="btn btn-secondary"
                        >

                            <i class="fa-solid fa-xmark"></i>

                            Cancel

                        </a>

                    </div>


                </form>

            </div>

        </section>


        <!-- FOOTER -->

        <footer class="admin-footer">

            <p>

                &copy;
                <?php echo date("Y"); ?>

                A.M Turai Travel & Tours.
                Transportation Management System.

            </p>

        </footer>


    </main>

</div>

</body>

</html>