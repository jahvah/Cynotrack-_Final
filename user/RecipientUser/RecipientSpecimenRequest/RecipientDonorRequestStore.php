<?php
session_start();
include("../../../includes/config.php");

// Ensure recipient is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $account_id = $_SESSION['account_id'];

    // 1. Get actual recipient_id from recipients_users
    $stmt_recipient = $conn->prepare("SELECT recipient_id FROM recipients_users WHERE account_id = ?");
    $stmt_recipient->bind_param("i", $account_id);
    $stmt_recipient->execute();
    $result    = $stmt_recipient->get_result();
    $recipient = $result->fetch_assoc();

    if (!$recipient) {
        $_SESSION['error'] = "Your recipient profile is missing. Please contact support.";
        header("Location: ../RecipientDashboard.php");
        exit;
    }

    $recipient_id = $recipient['recipient_id'];
    $donor_id     = intval($_POST['donor_id'] ?? 0);
    $quantity     = intval($_POST['quantity'] ?? 0);

    if ($donor_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = "Invalid input. Please select a valid quantity.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    // 2. Check for any existing pending requests by this recipient
    $stmt_check = $conn->prepare("
        SELECT COUNT(*) AS pending_count
        FROM specimen_requests
        WHERE recipient_id = ? AND status = 'pending'
    ");
    $stmt_check->bind_param("i", $recipient_id);
    $stmt_check->execute();
    $row_check = $stmt_check->get_result()->fetch_assoc();

    if ($row_check['pending_count'] > 0) {
        $_SESSION['error'] = "You already have a pending request. Please wait until it is processed before submitting a new one.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    // 3. Fetch a stored specimen from this donor
    $stmt_specimen = $conn->prepare("
        SELECT specimen_id, quantity, price
        FROM specimens
        WHERE specimen_owner_type = 'donor'
          AND specimen_owner_id = ?
          AND status = 'stored'
        ORDER BY specimen_id ASC
        LIMIT 1
    ");
    $stmt_specimen->bind_param("i", $donor_id);
    $stmt_specimen->execute();
    $specimen = $stmt_specimen->get_result()->fetch_assoc();

    if (!$specimen) {
        $_SESSION['error'] = "No available specimens from this donor at the moment.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    if ($quantity > $specimen['quantity']) {
        $_SESSION['error'] = "Requested quantity ({$quantity}) exceeds available stock ({$specimen['quantity']}).";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    $specimen_id = $specimen['specimen_id'];
    $unit_price  = $specimen['price'];
    $total_price = $unit_price * $quantity;

    // 4. Handle receipt upload
    $receipt_image = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../../../uploads/receipts/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext  = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

        if (!in_array($file_ext, $allowed)) {
            $_SESSION['error'] = "Invalid receipt file type. Accepted: JPG, PNG, GIF, PDF.";
            header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
            exit;
        }

        $file_name   = "receipt_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_path)) {
            $receipt_image = "uploads/receipts/" . $file_name;
        } else {
            $_SESSION['error'] = "Failed to upload receipt. Please try again.";
            header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
            exit;
        }
    } else {
        $_SESSION['error'] = "A payment receipt is required to complete your request.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    // 5. Insert specimen request
    $stmt_insert = $conn->prepare("
        INSERT INTO specimen_requests
            (recipient_id, specimen_id, requested_quantity, payment_status, receipt_image, unit_price, total_price, status)
        VALUES (?, ?, ?, 'unpaid', ?, ?, ?, 'pending')
    ");
    $stmt_insert->bind_param("iiisdd", $recipient_id, $specimen_id, $quantity, $receipt_image, $unit_price, $total_price);

    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "Your specimen request has been submitted successfully. Our team will review it shortly.";
    } else {
        $_SESSION['error'] = "Failed to submit request. Please try again. (Error: " . $stmt_insert->error . ")";
    }

    header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
    exit;

} else {
    header("Location: ../RecipientDashboard.php");
    exit;
}
?>
