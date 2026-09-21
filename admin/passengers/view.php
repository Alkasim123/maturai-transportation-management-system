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
        SELECT * FROM passengers
        WHERE name LIKE ?
        OR phone LIKE ?
        OR email LIKE ?
        OR address LIKE ?
        ORDER BY id DESC
    ");

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $passengers = $stmt->get_result();

} else {

    $passengers = $conn->query("
        SELECT * FROM passengers
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

    <title>Passengers - A.M Turai Admin</title>

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

            <a href="view.php" class="active">
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


    <!-- MAIN CONTENT -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h2>Passengers</h2>

                <p>Manage registered passengers</p>

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
                        <i class="fa-solid fa-users"></i>
                        Passengers
                    </h1>

                    <p>
                        Add, edit, view and manage passengers.
                    </p>

                </div>

                <a href="add.php"
                   class="btn btn-primary">

                    <i class="fa-solid fa-user-plus"></i>

                    Add Passenger

                </a>

            </div>


            <!-- SEARCH -->

            <div class="content-card">

                <form method="GET"
                      class="search-box">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search name, phone, email or address..."
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


            <!-- PASSENGERS TABLE -->

            <div class="content-card">

                <div class="table-container">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Name</th>

                                <th>Phone</th>

                                <th>Email</th>

                                <th>Address</th>

                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($passengers && $passengers->num_rows > 0): ?>

                            <?php $count = 1; ?>

                            <?php while ($passenger = $passengers->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $passenger["name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <i class="fa-solid fa-phone"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $passenger["phone"]
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php

                                        if (!empty($passenger["email"])) {

                                            echo htmlspecialchars(
                                                $passenger["email"]
                                            );

                                        } else {

                                            echo "N/A";

                                        }

                                        ?>

                                    </td>

                                    <td>

                                        <?php

                                        if (!empty($passenger["address"])) {

                                            echo htmlspecialchars(
                                                $passenger["address"]
                                            );

                                        } else {

                                            echo "N/A";

                                        }

                                        ?>

                                    </td>

                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="edit.php?id=<?php echo $passenger["id"]; ?>"
                                                class="btn edit-btn"
                                            >

                                                <i class="fa-solid fa-pen"></i>

                                                Edit

                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $passenger["id"]; ?>"
                                                class="btn delete-btn"
                                                onclick="return confirm('Are you sure you want to delete this passenger?');"
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

                                <td colspan="6"
                                    class="empty-data">

                                    <i class="fa-solid fa-user-slash"></i>

                                    <p>No passengers found.</p>

                                    <a href="add.php"
                                       class="btn btn-primary">

                                        <i class="fa-solid fa-user-plus"></i>

                                        Add First Passenger

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