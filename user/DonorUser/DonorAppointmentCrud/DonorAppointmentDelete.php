<?php
session_start();
include('../../../includes/config.php');

// Donor access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get appointment ID
$appointment_id = intval($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: DonorAppointmentIndex.php");
    exit();
}

// Get donor_id from logged in account
$stmt = $conn->prepare("
    SELECT donor_id 
    FROM donors_users 
    WHERE account_id = ?
");
$stmt->bind_param("i", $_SESSION['account_id']);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

if (!$donor) {
    $_SESSION['error'] = "Donor record not found.";
    header("Location: DonorAppointmentIndex.php");
    exit();
}

$donor_id = $donor['donor_id'];

// Check appointment ownership
$stmt = $conn->prepare("
    SELECT status 
    FROM appointments
    WHERE appointment_id = ?
    AND user_type = 'donor'
    AND user_id = ?
");
$stmt->bind_param("ii", $appointment_id, $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: DonorAppointmentIndex.php");
    exit();
}

// Prevent cancelling completed
if ($appointment['status'] === 'completed') {
    $_SESSION['error'] = "Completed appointments cannot be cancelled.";
    header("Location: DonorAppointmentIndex.php");
    exit();
}

// Update status instead of deleting
$stmt = $conn->prepare("
    UPDATE appointments
    SET status = 'cancelled'
    WHERE appointment_id = ?
");
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Appointment cancelled successfully.";
} else {
    $_SESSION['error'] = "Failed to cancel appointment.";
}

header("Location: DonorAppointmentIndex.php");
exit();
?>