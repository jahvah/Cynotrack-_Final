<?php
session_start();
include('../../../includes/config.php');

// Admin protection
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Verify appointment exists and belongs to a recipient
$stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id=? AND user_type='recipient'");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Not found or not a recipient appointment
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

// Delete the appointment
$stmtDelete = $conn->prepare("DELETE FROM appointments WHERE appointment_id=?");
$stmtDelete->bind_param("i", $appointment_id);

if (!$stmtDelete->execute()) {
    die("Appointment delete error: " . $stmtDelete->error);
}

header("Location: RecipientAppointmentIndex.php?success=appointment_deleted");
exit();
?>
