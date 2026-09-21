<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../../database.php";

$id = intval($_GET["id"] ?? 0);

if ($id > 0) {

    /*
       Delete payments belonging
       to this booking first.
    */

    $stmt = $conn->prepare("
        DELETE FROM payments
        WHERE booking_id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();


    /*
       Now delete booking.
    */

    $stmt = $conn->prepare("
        DELETE FROM bookings
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

}

header("Location: view.php");
exit();

?>