<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminRecipientIndex.php");
    exit();
}

$recipient_id = intval($_GET['id']);

/* =========================
   STEP 1: GET ACCOUNT ID + IMAGE
========================= */
$stmt = $conn->prepare("SELECT account_id, profile_image FROM recipients_users WHERE recipient_id=?");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminRecipientIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];
$image = $data['profile_image'];

/* =========================
   STEP 2: DELETE RECIPIENT RECORD
========================= */
$stmt = $conn->prepare("DELETE FROM recipients_users WHERE recipient_id=?");
$stmt->bind_param("i", $recipient_id);
if (!$stmt->execute()) {
    die("Recipient delete error: " . $stmt->error);
}

/* =========================
   STEP 3: DELETE ACCOUNT RECORD
========================= */
$stmt = $conn->prepare("DELETE FROM accounts WHERE account_id=?");
$stmt->bind_param("i", $account_id);
if (!$stmt->execute()) {
    die("Account delete error: " . $stmt->error);
}

/* =========================
   STEP 4: DELETE PROFILE IMAGE FILE
========================= */
if (!empty($image) && file_exists("../../uploads/" . $image)) {
    unlink("../../../uploads/" . $image);
}

header("Location: AdminRecipientIndex.php?success=recipient_deleted");
exit();
?>
