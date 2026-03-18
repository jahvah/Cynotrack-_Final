<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$donor_id = intval($_GET['id']);

// get account id from donors_users
$stmt = $conn->prepare("SELECT account_id FROM donors_users WHERE donor_id=?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];

// make account inactive instead of deleting
$stmt = $conn->prepare("UPDATE accounts SET status='inactive' WHERE account_id=?");
$stmt->bind_param("i", $account_id);
if (!$stmt->execute()) {
    die("Account update error: " . $stmt->error);
}

header("Location: AdminDonorIndex.php?success=donor_inactivated");
exit();
?>