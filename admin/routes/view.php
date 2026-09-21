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

    $stmt = $conn->prepare("
        SELECT * FROM routes
        WHERE departure LIKE ?
        OR destination LIKE ?
        OR fare LIKE ?
        ORDER BY id DESC
    ");

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $routes = $stmt->get_result();

} else {

    $routes = $conn->query("
        SELECT * FROM routes
        ORDER BY id DESC
    ");

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Routes - A.M Turai Admin</title>

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

            <a href="view.php" class="active">
                <i class="fa-solid fa-route"></i>
                <span>Routes</span>
            </a>

            <a href="../trips/view.php">
                <i class="fa-solid fa-road"></i>
                <span>Trips</span>
            </a>

            <a href="../bookings/view.php">
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

        <header class="admin-topbar">

            <div>

                <h2>Routes</h2>

                <p>Manage transportation routes</p>

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


        <section class="page-content">

            <div class="page-title">

                <div>

                    <h1>
                        <i class="fa-solid fa-route"></i>
                        Routes
                    </h1>

                    <p>
                        Add, edit, view and manage travel routes.
                    </p>

                </div>

                <a href="add.php"
                   class="btn btn-primary">

                    <i class="fa-solid fa-plus"></i>

                    Add Route

                </a>

            </div>


            <!-- SEARCH -->

            <div class="content-card">

                <form method="GET"
                      class="search-box">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search departure, destination or fare..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-magnifying-glass"></i>

                        Search

                    </button>

                    <?php if ($search != ""): ?>

                        <a href="view.php"
                           class="btn btn-secondary">

                            <i class="fa-solid fa-rotate-left"></i>

                            Clear

                        </a>

                    <?php endif; ?>

                </form>

            </div>


            <!-- ROUTES TABLE -->

            <div class="content-card">

                <div class="table-container">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Departure</th>

                                <th>Destination</th>

                                <th>Fare</th>

                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($routes && $routes->num_rows > 0): ?>

                            <?php $count = 1; ?>

                            <?php while ($route = $routes->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>

                                    <td>

                                        <strong>

                                            <i class="fa-solid fa-location-dot"></i>

                                            <?php
                                            echo htmlspecialchars(
                                                $route["departure"]
                                            );
                                            ?>

                                        </strong>

                                    </td>

                                    <td>

                                        <i class="fa-solid fa-flag-checkered"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $route["destination"]
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <strong>

                                            ₦<?php
                                            echo number_format(
                                                $route["fare"],
                                                2
                                            );
                                            ?>

                                        </strong>

                                    </td>

                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="edit.php?id=<?php echo $route["id"]; ?>"
                                                class="btn edit-btn"
                                            >

                                                <i class="fa-solid fa-pen"></i>

                                                Edit

                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $route["id"]; ?>"
                                                class="btn delete-btn"
                                                onclick="return confirm('Are you sure you want to delete this route?');"
                                            >

                                                <i class="fa-solid fa-trash"></i>

                                                Delete

                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="5"
                                    class="empty-data">

                                    <i class="fa-solid fa-route"></i>

                                    <p>No routes found.</p>

                                    <a href="add.php"
                                       class="btn btn-primary">

                                        <i class="fa-solid fa-plus"></i>

                                        Add First Route

                                    </a>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>


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