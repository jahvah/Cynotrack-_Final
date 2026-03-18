<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Validate ID
$specimen_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($specimen_id <= 0) {
    $_SESSION['error'] = "Invalid specimen ID.";
    header("Location: ../StaffSpecimenIndex.php");
    exit();
}

// Get specimen (donor only)
$stmt = $conn->prepare("SELECT quantity, status FROM specimens 
                        WHERE specimen_id = ? 
                        AND specimen_owner_type = 'donor'");
$stmt->bind_param("i", $specimen_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Specimen not found.";
    header("Location: ../StaffSpecimenIndex.php");
    exit();
}

$specimen = $result->fetch_assoc();
$quantity = $specimen['quantity'];
$current_status = $specimen['status'];

if ($current_status === 'disposed') {
    $_SESSION['error'] = "Specimen is already disposed.";
    header("Location: ../StaffSpecimenIndex.php");
    exit();
}

$conn->begin_transaction();

try {
    // 1️⃣ Mark specimen as disposed
    $update_stmt = $conn->prepare("UPDATE specimens SET status = 'disposed' WHERE specimen_id = ?");
    $update_stmt->bind_param("i", $specimen_id);
    $update_stmt->execute();

    // 2️⃣ Insert inventory log
    if ($quantity > 0) {
        $log_stmt = $conn->prepare("INSERT INTO inventory_logs (specimen_id, action, quantity) VALUES (?, 'disposed', ?)");
        $log_stmt->bind_param("ii", $specimen_id, $quantity);
        $log_stmt->execute();
        $log_stmt->close();
    }

    $update_stmt->close();
    $conn->commit();

    $_SESSION['success'] = "Donor specimen marked as disposed successfully.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Failed to mark specimen as disposed.";
}

header("Location: ../StaffSpecimenIndex.php");
exit();
?>