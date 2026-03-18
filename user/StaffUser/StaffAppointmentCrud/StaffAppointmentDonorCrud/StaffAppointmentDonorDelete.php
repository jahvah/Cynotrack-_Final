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
    header("Location: ../StaffAppointmentIndex.php.php");
    exit();
}

$appointment_id = intval($_GET['id']);
if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: ../StaffAppointmentIndex.php.php");
    exit();
}

// Delete the donor appointment
$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND user_type = 'donor'");
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Donor appointment deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete appointment.";
}

// Redirect back to the donor appointment index
header("Location: ../StaffAppointmentIndex.php");
exit();
?>