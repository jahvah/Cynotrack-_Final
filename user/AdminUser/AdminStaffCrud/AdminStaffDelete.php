<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminStaffIndex.php");
    exit();
}

$staff_id = intval($_GET['id']);

//get account id and image
$stmt = $conn->prepare("SELECT account_id, profile_image FROM staff WHERE staff_id=?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminStaffIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];
$image = $data['profile_image'];

//delete staff
$stmt = $conn->prepare("DELETE FROM staff WHERE staff_id=?");
$stmt->bind_param("i", $staff_id);
if (!$stmt->execute()) {
    die("Staff delete error: " . $stmt->error);
}

//delete account
$stmt = $conn->prepare("DELETE FROM accounts WHERE account_id=?");
$stmt->bind_param("i", $account_id);
if (!$stmt->execute()) {
    die("Account delete error: " . $stmt->error);
}

//delete image file
if (!empty($image) && file_exists("../../uploads/" . $image)) {
    unlink("../../../uploads/" . $image);
}

header("Location: AdminStaffIndex.php?success=staff_deleted");
exit();
?>
