<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Make sure ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);
if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

// Delete the self-storage appointment
$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND user_type = 'storage'");
$stmt->bind_param("i", $appointment_id);

$stmt->execute(); // optional: you can check for success if you want
// Redirect back to the appointment index (no messages shown)
header("Location: ../StaffAppointmentIndex.php");
exit();
?>