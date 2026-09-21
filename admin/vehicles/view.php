<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search != "") {

    $stmt = $conn->prepare(
        "SELECT * FROM vehicles
         WHERE vehicle_number LIKE ?
         OR vehicle_type LIKE ?
         OR status LIKE ?
         ORDER BY id DESC"
    );

    $searchValue = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $searchValue,
        $searchValue,
        $searchValue
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query(
        "SELECT * FROM vehicles ORDER BY id DESC"
    );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vehicles | MATURAI Admin</title>

    <link rel="stylesheet"
          href="../css/admin-style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-layout">


<!-- SIDEBAR -->

<aside class="sidebar" id="sidebar">

    <div class="sidebar-logo">

        <i class="fa-solid fa-bus"></i>

        <h2>A.M TURAI</h2>

        <p>Travel & Tours</p>

    </div>


    <ul class="sidebar-menu">

        <li>
            <a href="../dashboard.php">
                <i class="fa-solid fa-gauge"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="view.php" class="active">
                <i class="fa-solid fa-bus"></i>
                Vehicles
            </a>
        </li>

        <li>
            <a href="../drivers/view.php">
                <i class="fa-solid fa-user-tie"></i>
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
                <i class="fa-solid fa-calendar-days"></i>
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
                <i class="fa-solid fa-money-bill"></i>
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


<!-- MAIN CONTENT -->

<main class="admin-main">


    <!-- TOP BAR -->

    <div class="admin-topbar">

        <button
            class="menu-toggle"
            onclick="toggleSidebar()">

            <i class="fa-solid fa-bars"></i>

        </button>

        <h2>Vehicle Management</h2>

        <div class="admin-user">

            <i class="fa-solid fa-circle-user"></i>

            <?php
            echo htmlspecialchars(
                $_SESSION["admin_username"]
            );
            ?>

        </div>

    </div>


    <!-- PAGE -->

    <div class="page-content">


        <div class="page-title">

            <h1>
                Vehicles
            </h1>

            <p>
                Manage all transportation vehicles.
            </p>

        </div>


        <!-- ACTION BAR -->

        <div class="content-card">

            <div
                style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                gap:15px;
                flex-wrap:wrap;
                "
            >

                <form
                    method="GET"
                    class="search-box"
                    style="margin:0; flex:1;"
                >

                    <input
                        type="text"
                        name="search"
                        placeholder="Search vehicle..."
                        value="<?php
                        echo htmlspecialchars($search);
                        ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-search"></i>

                        Search

                    </button>

                </form>


                <a
                    href="add.php"
                    class="btn btn-success"
                >

                    <i class="fa-solid fa-plus"></i>

                    Add Vehicle

                </a>

            </div>

        </div>


        <!-- VEHICLE TABLE -->

        <div class="content-card">

            <h2>
                Vehicle List
            </h2>


            <div class="table-container">

                <table class="admin-table">

                    <thead>

                    <tr>

                        <th>#</th>

                        <th>Vehicle Number</th>

                        <th>Vehicle Type</th>

                        <th>Capacity</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                    </thead>


                    <tbody>

                    <?php

                    if ($result->num_rows > 0):

                        $number = 1;

                        while ($row = $result->fetch_assoc()):

                    ?>

                    <tr>

                        <td>
                            <?php echo $number++; ?>
                        </td>

                        <td>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $row["vehicle_number"]
                                );
                                ?>
                            </strong>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["vehicle_type"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo $row["capacity"];
                            ?> Seats
                        </td>

                        <td>

                            <?php

                            $statusClass = strtolower(
                                $row["status"]
                            );

                            $statusClass =
                                str_replace(
                                    " ",
                                    "-",
                                    $statusClass
                                );

                            ?>

                            <span
                                class="status status-<?php
                                echo $statusClass;
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $row["status"]
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <div class="action-buttons">

                                <a
                                    href="edit.php?id=<?php
                                    echo $row["id"];
                                    ?>"
                                    class="edit-btn"
                                >

                                    <i class="fa-solid fa-pen"></i>

                                    Edit

                                </a>


                                <a
                                    href="delete.php?id=<?php
                                    echo $row["id"];
                                    ?>"
                                    class="delete-btn"
                                    onclick="
                                    return confirm(
                                    'Are you sure you want to delete this vehicle?'
                                    );
                                    "
                                >

                                    <i class="fa-solid fa-trash"></i>

                                    Delete

                                </a>

                            </div>

                        </td>

                    </tr>

                    <?php

                        endwhile;

                    else:

                    ?>

                    <tr>

                        <td
                            colspan="6"
                            class="empty-data"
                        >

                            <i class="fa-solid fa-bus"></i>

                            <br>

                            No vehicles found.

                        </td>

                    </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


    </div>


    <div class="admin-footer">

        © 2026 A.M Turai Travel & Tours

    </div>


</main>

</div>


<script>

function toggleSidebar() {

    document
        .getElementById("sidebar")
        .classList
        .toggle("active");

}

</script>

</body>

</html>