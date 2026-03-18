<?php
session_start();
include('../../../includes/config.php');

// Admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Check if donor ID is provided
if (!isset($_GET['id'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$donor_id = intval($_GET['id']);

// Get account id, profile image, and medical document
$stmt = $conn->prepare("SELECT account_id, profile_image, medical_document FROM donors_users WHERE donor_id=?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];
$image = $data['profile_image'];
$medical_doc = $data['medical_document'];

// Delete donor record
$stmt = $conn->prepare("DELETE FROM donors_users WHERE donor_id=?");
$stmt->bind_param("i", $donor_id);
if (!$stmt->execute()) {
    die("Donor delete error: " . $stmt->error);
}

// Delete account record
$stmt = $conn->prepare("DELETE FROM accounts WHERE account_id=?");
$stmt->bind_param("i", $account_id);
if (!$stmt->execute()) {
    die("Account delete error: " . $stmt->error);
}

// Delete profile image file
if (!empty($image) && file_exists("../../../uploads/" . $image)) {
    unlink("../../../uploads/" . $image);
}

// Delete medical document file
if (!empty($medical_doc) && file_exists("../../../medical_docs/" . $medical_doc)) {
    unlink("../../../medical_docs/" . $medical_doc);
}

header("Location: AdminDonorIndex.php?success=donor_deleted");
exit();
?>