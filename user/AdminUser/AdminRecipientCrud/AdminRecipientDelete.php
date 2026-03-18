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
   STEP 1: GET ACCOUNT ID
========================= */
$stmt = $conn->prepare("SELECT account_id FROM recipients_users WHERE recipient_id=?");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminRecipientIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];

/* =========================
   STEP 2: MAKE ACCOUNT INACTIVE
========================= */
$stmt = $conn->prepare("UPDATE accounts SET status='inactive' WHERE account_id=?");
$stmt->bind_param("i", $account_id);
if (!$stmt->execute()) {
    die("Account update error: " . $stmt->error);
}

header("Location: AdminRecipientIndex.php?success=recipient_inactivated");
exit();
?>