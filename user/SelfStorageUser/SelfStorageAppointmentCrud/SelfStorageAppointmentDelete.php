<?php
session_start();
include('../../../includes/config.php');

// SELF-STORAGE access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-storage') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get appointment ID
$appointment_id = intval($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}

// Get storage_user_id from logged in account
$stmt = $conn->prepare("
    SELECT storage_user_id 
    FROM self_storage_users 
    WHERE account_id = ?
");
$stmt->bind_param("i", $_SESSION['account_id']);
$stmt->execute();
$result = $stmt->get_result();
$storage = $result->fetch_assoc();

if (!$storage) {
    $_SESSION['error'] = "Storage user record not found.";
    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}

$storage_user_id = $storage['storage_user_id'];

// Check appointment ownership
$stmt = $conn->prepare("
    SELECT status 
    FROM appointments
    WHERE appointment_id = ?
    AND user_type = 'storage'
    AND user_id = ?
");
$stmt->bind_param("ii", $appointment_id, $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}

// Prevent cancelling completed
if ($appointment['status'] === 'completed') {
    $_SESSION['error'] = "Completed appointments cannot be cancelled.";
    header("Location: SelfStorageAppointmentIndex.php");
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

header("Location: SelfStorageAppointmentIndex.php");
exit();
?>