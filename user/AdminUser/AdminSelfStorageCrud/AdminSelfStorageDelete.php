<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$storage_user_id = intval($_GET['id']);

//get account id and image
$stmt = $conn->prepare("SELECT account_id, profile_image FROM self_storage_users WHERE storage_user_id=?");
$stmt->bind_param("i", $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];
$image = $data['profile_image'];

//delete self-storage user
$stmt = $conn->prepare("DELETE FROM self_storage_users WHERE storage_user_id=?");
$stmt->bind_param("i", $storage_user_id);
if (!$stmt->execute()) {
    die("Self-Storage user delete error: " . $stmt->error);
}

//delete account
$stmt = $conn->prepare("DELETE FROM accounts WHERE account_id=?");
$stmt->bind_param("i", $account_id);
if (!$stmt->execute()) {
    die("Account delete error: " . $stmt->error);
}

//delete image file
if (!empty($image) && file_exists("../../../uploads/" . $image)) {
    unlink("../../../uploads/" . $image);
}

// Delete medical document file
if (!empty($medical_doc) && file_exists("../../../medical_docs/" . $medical_doc)) {
    unlink("../../../medical_docs/" . $medical_doc);
}

header("Location: AdminSelfStorageIndex.php?success=storage_user_deleted");
exit();
?>
